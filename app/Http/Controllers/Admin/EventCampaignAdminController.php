<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use App\Support\BangladeshGeo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventCampaignAdminController extends Controller
{
    /**
     * Display list of all Event Campaigns and Registrations.
     */
    public function index(Request $request)
    {
        $campaignsQuery = EventCampaign::withCount([
            'registrations',
            'registrations as confirmed_registrations_count' => function ($q) {
                $q->whereIn('status', ['confirmed', 'selected', 'approved']);
            },
            'registrations as pending_registrations_count' => function ($q) {
                $q->where('status', 'pending');
            }
        ])->latest();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $campaignsQuery->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('slug', 'like', "%{$s}%")
                  ->orWhere('badge_text', 'like', "%{$s}%");
            });
        }

        if ($request->filled('type')) {
            $campaignsQuery->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $campaignsQuery->where('is_active', true)
                    ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
            } elseif ($request->status === 'inactive') {
                $campaignsQuery->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $campaignsQuery->whereNotNull('ends_at')->where('ends_at', '<', now());
            }
        }

        $campaigns = $campaignsQuery->paginate(15)->withQueryString();

        // Stats summary
        $totalCampaigns = EventCampaign::count();
        $activeCampaigns = EventCampaign::where('is_active', true)->count();
        $totalRegistrations = EventRegistration::count();
        $totalCollected = EventRegistration::where('payment_status', 'verified')->sum('amount_paid');

        return view('admin.event_campaigns.index', compact(
            'campaigns',
            'totalCampaigns',
            'activeCampaigns',
            'totalRegistrations',
            'totalCollected'
        ));
    }

    /**
     * Dedicated direct entry for "পাঠাগার নিবন্ধন (বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচি)".
     */
    public function pathagar(Request $request)
    {
        return redirect()->route('admin.libraries.index');
    }

    /**
     * Show form to create new Event Campaign.
     */
    public function create()
    {
        return view('admin.event_campaigns.create');
    }

    /**
     * Store a newly created Event Campaign.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                => 'required|string|max:255',
            'slug'                 => 'required|string|max:100|alpha_dash|unique:event_campaigns,slug',
            'type'                 => 'required|string|in:event,donation,scholarship,competition,workshop,library,book_grant',
            'badge_text'           => 'nullable|string|max:50',
            'short_description'    => 'nullable|string|max:500',
            'description'          => 'nullable|string',
            'banner_image'         => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:6144',
            'theme_color'          => 'nullable|string|max:30',
            'has_fee_or_donation'  => 'nullable|boolean',
            'fee_amount'           => 'nullable|numeric|min:0',
            'is_donation_flexible' => 'nullable|boolean',
            'min_donation'         => 'nullable|numeric|min:0',
            'payment_methods'      => 'nullable|string',
            'payment_instructions' => 'nullable|string|max:2000',
            'starts_at'            => 'nullable|date',
            'ends_at'              => 'nullable|date|after_or_equal:starts_at',
            'max_participants'     => 'nullable|integer|min:1',
            'is_active'            => 'nullable|boolean',
            'success_message'      => 'nullable|string|max:500',
            'redirect_url'         => 'nullable|url|max:255',
            'contact_phone'        => 'nullable|string|max:30',
            'contact_email'        => 'nullable|email|max:100',
            'custom_fields_json'   => 'nullable|string',
            'form_settings_json'   => 'nullable|string',
        ]);

        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('campaigns', 'public');
        }

        $validated['has_fee_or_donation'] = !empty($request->has_fee_or_donation);
        $validated['is_donation_flexible'] = !empty($request->is_donation_flexible);
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;
        $validated['slug'] = strtolower(trim($validated['slug']));
        $validated['theme_color'] = ($validated['theme_color'] ?? null) ?: '#0284c7';

        if (!empty($request->custom_fields_json)) {
            $fields = json_decode($request->custom_fields_json, true);
            $validated['custom_fields'] = is_array($fields) ? $fields : [];
        } else {
            $validated['custom_fields'] = [];
        }

        if (!empty($request->form_settings_json)) {
            $fSettings = json_decode($request->form_settings_json, true);
            $validated['form_settings'] = is_array($fSettings) ? $fSettings : [];
        }

        $campaign = EventCampaign::create($validated);

        return redirect()->route('admin.event-campaigns.show', $campaign->id)
            ->with('success', "Campaign '{$campaign->title}' created successfully.");
    }

    /**
     * Show single campaign details and list of registered participants.
     */
    public function show(Request $request, EventCampaign $campaign)
    {
        $campaign->loadCount('registrations');

        $regQuery = $campaign->registrations()->with('user');

        // Search
        if ($request->filled('search')) {
            $s = trim($request->search);
            $regQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('registration_number', 'like', "%{$s}%")
                  ->orWhere('institution_or_org', 'like', "%{$s}%")
                  ->orWhere('district', 'like', "%{$s}%")
                  ->orWhere('thana', 'like', "%{$s}%")
                  ->orWhere('form_data->magazine_name', 'like', "%{$s}%")
                  ->orWhere('form_data->author_category', 'like', "%{$s}%")
                  ->orWhere('form_data->author_categories', 'like', "%{$s}%")
                  ->orWhere('form_data->perm_division', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $regQuery->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $regQuery->where('payment_status', $request->payment_status);
        }

        // Division & District conflict safeguard
        if ($request->filled('division') && $request->filled('district')) {
            $rawDiv = trim($request->division);
            $canonicalDiv = BangladeshGeo::normalizeDivision($rawDiv) ?: $rawDiv;
            $divDistricts = BangladeshGeo::DIVISIONS[$canonicalDiv] ?? [];
            $canonicalDst = BangladeshGeo::normalizeDistrict(trim($request->district));
            if (!empty($divDistricts) && !empty($canonicalDst) && !in_array($canonicalDst, $divDistricts, true)) {
                // If selected district does not belong to the selected division, unset district so all division results show
                $request->offsetUnset('district');
                $request->query->remove('district');
            }
        }

        // Division filter (handles both Bengali & English variants)
        if ($request->filled('division')) {
            $rawDiv = trim($request->division);
            $canonicalDiv = BangladeshGeo::normalizeDivision($rawDiv) ?: $rawDiv;
            $divVariants = BangladeshGeo::getVariants($canonicalDiv);

            // Also include all districts belonging to this division
            $divDistricts = BangladeshGeo::DIVISIONS[$canonicalDiv] ?? [];
            $allDistVariants = [];
            foreach ($divDistricts as $dName) {
                $allDistVariants = array_merge($allDistVariants, BangladeshGeo::getVariants($dName));
            }
            $allDistVariants = array_values(array_unique(array_filter($allDistVariants)));

            $regQuery->where(function ($q) use ($divVariants, $allDistVariants) {
                $q->where(function ($sq) use ($divVariants) {
                    foreach ($divVariants as $v) {
                        $sq->orWhere('form_data->perm_division', $v)
                           ->orWhere('form_data->division', $v)
                           ->orWhere('form_data->perm_division', 'like', "%{$v}%")
                           ->orWhere('form_data->division', 'like', "%{$v}%");
                    }
                });
                if (!empty($allDistVariants)) {
                    $q->orWhereIn('district', $allDistVariants)
                      ->orWhereIn('form_data->district', $allDistVariants)
                      ->orWhereIn('form_data->perm_district', $allDistVariants)
                      ->orWhereIn('form_data->permanent_district', $allDistVariants);
                }
            });
        }

        // District filter (handles both Bengali & English variants)
        if ($request->filled('district')) {
            $rawDst = trim($request->district);
            $canonicalDst = BangladeshGeo::normalizeDistrict($rawDst) ?: $rawDst;
            $dstVariants = BangladeshGeo::getVariants($canonicalDst);

            $regQuery->where(function ($q) use ($dstVariants) {
                $q->whereIn('district', $dstVariants)
                  ->orWhereIn('form_data->district', $dstVariants)
                  ->orWhereIn('form_data->perm_district', $dstVariants)
                  ->orWhereIn('form_data->permanent_district', $dstVariants)
                  ->orWhere(function ($sq) use ($dstVariants) {
                      foreach ($dstVariants as $v) {
                          $sq->orWhere('district', 'like', "%{$v}%")
                             ->orWhere('form_data->district', 'like', "%{$v}%")
                             ->orWhere('form_data->perm_district', 'like', "%{$v}%")
                             ->orWhere('form_data->permanent_district', 'like', "%{$v}%");
                      }
                  });
            });
        }

        // Category / Author Category & Little Magazine filter
        if ($request->filled('category')) {
            $cat = trim($request->category);
            if ($cat === 'littlemag' || $cat === 'লিটিলম্যাগ' || $cat === 'লিটিলম্যাগাজিন' || $cat === 'লিটিলম্যাগ সম্পাদক' || $cat === 'ছোটকাগজ') {
                $regQuery->where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereNotNull('form_data->magazine_name')
                           ->where('form_data->magazine_name', '!=', '');
                    })
                    ->orWhere('form_data->author_categories', 'like', '%লিটিলম্যাগ%')
                    ->orWhere('form_data->author_category', 'like', '%লিটিলম্যাগ%')
                    ->orWhere('designation_or_class', 'like', '%লিটিলম্যাগ%')
                    ->orWhere('designation_or_class', 'like', '%সম্পাদক%');
                });
            } else {
                $regQuery->where(function ($q) use ($cat) {
                    $q->where('form_data->author_category', 'like', "%{$cat}%")
                      ->orWhere('form_data->author_categories', 'like', "%{$cat}%")
                      ->orWhere('designation_or_class', 'like', "%{$cat}%")
                      ->orWhere('form_data->genres', 'like', "%{$cat}%");
                });
            }
        } elseif ($request->boolean('is_littlemag')) {
            $regQuery->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->whereNotNull('form_data->magazine_name')
                       ->where('form_data->magazine_name', '!=', '');
                })
                ->orWhere('form_data->author_categories', 'like', '%লিটিলম্যাগ%')
                ->orWhere('form_data->author_category', 'like', '%লিটিলম্যাগ%')
                ->orWhere('designation_or_class', 'like', '%লিটিলম্যাগ%')
                ->orWhere('designation_or_class', 'like', '%সম্পাদক%');
            });
        }

        // Alphabetical or Custom Sorting
        $sort = $request->input('sort', 'latest');
        if ($sort === 'alpha' || $sort === 'name') {
            $regQuery->orderBy('name', 'asc');
        } elseif ($sort === 'alpha_desc') {
            $regQuery->orderBy('name', 'desc');
        } elseif ($sort === 'oldest') {
            $regQuery->oldest();
        } elseif ($sort === 'reg_no') {
            $regQuery->orderBy('registration_number', 'asc');
        } else {
            $regQuery->latest();
        }

        // Smart Auto-Invalidating Conflict Analysis & Campaign Metrics Cache
        $regCount = $campaign->registrations_count ?? $campaign->registrations()->count();
        $maxUpdated = $campaign->registrations()->max('updated_at') ?? '0';
        $analysisCacheKey = "camp_{$campaign->id}_analysis_v_{$regCount}_{$maxUpdated}";

        $analysis = Cache::remember($analysisCacheKey, 300, function () use ($campaign) {
            $normPhoneFn = function ($phone) {
                if (!$phone) return '';
                $digits = preg_replace('/[^0-9]/', '', (string)$phone);
                if (str_starts_with($digits, '880') && strlen($digits) === 13) {
                    $digits = substr($digits, 2);
                }
                return $digits;
            };

            // Query only lightweight columns needed for analysis to minimize memory and DB I/O
            $allCampaignRegistrations = $campaign->registrations()
                ->select([
                    'id', 'event_campaign_id', 'user_id', 'registration_number',
                    'name', 'phone', 'email', 'district', 'designation_or_class',
                    'status', 'payment_status', 'amount_paid', 'form_data', 'created_at'
                ])
                ->with(['user:id,phone'])
                ->get();

            $phoneGroups = [];
            $emailGroups = [];

            foreach ($allCampaignRegistrations as $r) {
                $pNorm = $normPhoneFn($r->phone);
                if (!empty($pNorm)) {
                    $phoneGroups[$pNorm][] = [
                        'id'     => $r->id,
                        'reg_no' => $r->registration_number,
                        'name'   => $r->name,
                        'phone'  => $r->phone,
                    ];
                }

                if (!empty($r->email)) {
                    $eNorm = strtolower(trim($r->email));
                    $emailGroups[$eNorm][] = [
                        'id'     => $r->id,
                        'reg_no' => $r->registration_number,
                        'name'   => $r->name,
                        'email'  => $r->email,
                    ];
                }
            }

            $conflictMap = [];
            $conflictRegIds = [];
            $duplicatePhoneMap = [];

            foreach ($phoneGroups as $pNorm => $group) {
                if (count($group) > 1) {
                    $duplicatePhoneMap[$pNorm] = count($group);
                }
            }

            $geoConflictRegIds = [];
            $phoneConflictRegIds = [];

            foreach ($allCampaignRegistrations as $r) {
                $pNorm = $normPhoneFn($r->phone);
                $hasDupPhone = !empty($pNorm) && isset($phoneGroups[$pNorm]) && count($phoneGroups[$pNorm]) > 1;
                $otherPhoneRegs = $hasDupPhone 
                    ? array_values(array_filter($phoneGroups[$pNorm], fn($item) => $item['id'] !== $r->id))
                    : [];

                $userPhoneNorm = $r->user ? $normPhoneFn($r->user->phone) : '';
                $hasUserMismatch = ($r->user && !empty($userPhoneNorm) && !empty($pNorm) && $userPhoneNorm !== $pNorm);

                $eNorm = !empty($r->email) ? strtolower(trim($r->email)) : '';
                $hasDupEmail = !empty($eNorm) && isset($emailGroups[$eNorm]) && count($emailGroups[$eNorm]) > 1;
                $otherEmailRegs = $hasDupEmail 
                    ? array_values(array_filter($emailGroups[$eNorm], fn($item) => $item['id'] !== $r->id))
                    : [];

                // Division & District Geo Conflict Checking
                $rawDiv = $r->form_data['perm_division'] ?? ($r->form_data['division'] ?? null);
                $rawDist = $r->district ?: ($r->form_data['perm_district'] ?? ($r->form_data['permanent_district'] ?? ($r->form_data['district'] ?? null)));
                $normDiv = BangladeshGeo::normalizeDivision($rawDiv);
                $normDist = BangladeshGeo::normalizeDistrict($rawDist);
                $expectedDivForDist = BangladeshGeo::getDivisionForDistrict($normDist);

                $hasGeoConflict = false;
                $geoConflictDetails = null;

                if (!empty($normDiv) && !empty($expectedDivForDist) && $normDiv !== $expectedDivForDist) {
                    $hasGeoConflict = true;
                    $geoConflictDetails = "বিভাগ অমিল: জেলা '{$normDist}' মূলত {$expectedDivForDist} বিভাগের অন্তর্ভুক্ত, কিন্তু বিভাগে '{$normDiv}' দেওয়া রয়েছে।";
                } elseif (!empty($rawDist) && ($normDist === 'অনির্ধারিত জেলা' || !in_array($normDist, array_merge(...array_values(BangladeshGeo::DIVISIONS)), true))) {
                    $hasGeoConflict = true;
                    $geoConflictDetails = "অজ্ঞাত জেলা: '{$rawDist}' বাংলাদেশের ৬৪ জেলার তালিকায় পাওয়া যায়নি।";
                }

                if ($hasDupPhone || $hasUserMismatch || $hasDupEmail || $hasGeoConflict) {
                    $conflictRegIds[] = $r->id;
                    if ($hasGeoConflict) {
                        $geoConflictRegIds[] = $r->id;
                    }
                    if ($hasDupPhone || $hasUserMismatch || $hasDupEmail) {
                        $phoneConflictRegIds[] = $r->id;
                    }

                    $conflictMap[$r->id] = [
                        'has_conflict'          => true,
                        'duplicate_phone'       => $hasDupPhone,
                        'other_regs_with_phone' => $otherPhoneRegs,
                        'user_mismatch'         => $hasUserMismatch,
                        'user_phone'            => $r->user?->phone,
                        'duplicate_email'       => $hasDupEmail,
                        'other_regs_with_email' => $otherEmailRegs,
                        'geo_conflict'          => $hasGeoConflict,
                        'geo_conflict_details'  => $geoConflictDetails,
                        'norm_div'              => $normDiv,
                        'norm_dist'             => $normDist,
                        'expected_div'          => $expectedDivForDist,
                    ];
                }
            }

            $totalConflictCount = count(array_unique($conflictRegIds));
            $phoneConflictCount = count(array_unique($phoneConflictRegIds));
            $geoConflictCount   = count(array_unique($geoConflictRegIds));

            $totalRegistrations = $allCampaignRegistrations->count();
            $totalCollected = $allCampaignRegistrations->where('payment_status', 'verified')->sum('amount_paid');
            $pendingCount = $allCampaignRegistrations->where('status', 'pending')->count();

            $geoDatabase = [];
            $divisionStats = [];
            $districtStats = [];
            $littleMagList = collect();
            $categoryStats = [];

            foreach ($allCampaignRegistrations as $reg) {
                $dist = $reg->resolved_district;
                $div = $reg->resolved_division;

                if (!isset($geoDatabase[$div])) {
                    $geoDatabase[$div] = [];
                    $divisionStats[$div] = 0;
                }
                if (!isset($geoDatabase[$div][$dist])) {
                    $geoDatabase[$div][$dist] = collect();
                    $districtStats[$dist] = 0;
                }

                $geoDatabase[$div][$dist]->push($reg);
                $divisionStats[$div]++;
                $districtStats[$dist]++;

                if ($reg->isLittleMagEditor()) {
                    $littleMagList->push($reg);
                }

                // Extract all author categories & roles
                $cats = [];
                if (!empty($reg->form_data['author_categories']) && is_array($reg->form_data['author_categories'])) {
                    $cats = array_merge($cats, $reg->form_data['author_categories']);
                } elseif (!empty($reg->form_data['author_category'])) {
                    $parts = array_map('trim', explode(',', $reg->form_data['author_category']));
                    $cats = array_merge($cats, $parts);
                }
                if (!empty($reg->designation_or_class) && !in_array($reg->designation_or_class, $cats, true)) {
                    $cats[] = trim($reg->designation_or_class);
                }
                if ($reg->isLittleMagEditor()) {
                    $cats[] = 'লিটিলম্যাগ সম্পাদক';
                }
                $cats = array_unique(array_filter($cats));
                foreach ($cats as $cName) {
                    $categoryStats[$cName] = ($categoryStats[$cName] ?? 0) + 1;
                }
            }
            arsort($categoryStats);

            // Sort Divisions alphabetically (Bengali Unicode collation)
            uksort($geoDatabase, function ($a, $b) {
                if ($a === 'অনির্ধারিত বিভাগ') return 1;
                if ($b === 'অনির্ধারিত বিভাগ') return -1;
                return strcmp($a, $b);
            });

            // Within each Division, sort Districts alphabetically and participants alphabetically
            foreach ($geoDatabase as $divName => $dists) {
                uksort($geoDatabase[$divName], function ($a, $b) {
                    if ($a === 'অনির্ধারিত জেলা') return 1;
                    if ($b === 'অনির্ধারিত জেলা') return -1;
                    return strcmp($a, $b);
                });

                foreach ($geoDatabase[$divName] as $dName => $pList) {
                    $geoDatabase[$divName][$dName] = $pList->sort(function ($a, $b) {
                        return strcmp($a->name, $b->name);
                    })->values();
                }
            }

            // Sort Little Magazine List alphabetically (by Magazine Name, then Editor Name)
            $littleMagList = $littleMagList->sort(function ($a, $b) {
                $magA = $a->magazine_name ?: $a->name;
                $magB = $b->magazine_name ?: $b->name;
                return strcmp($magA, $magB);
            })->values();

            $totalLittleMagCount = $littleMagList->count();
            $totalGeoDivisions = count(array_filter(array_keys($geoDatabase), fn($d) => $d !== 'অনির্ধারিত বিভাগ'));
            $totalGeoDistricts = count(array_filter(array_keys($districtStats), fn($d) => $d !== 'অনির্ধারিত জেলা'));

            return [
                'totalRegistrations'   => $totalRegistrations,
                'totalCollected'       => $totalCollected,
                'pendingCount'         => $pendingCount,
                'duplicatePhoneMap'    => $duplicatePhoneMap,
                'phoneConflictCount'   => $phoneConflictCount,
                'geoConflictCount'     => $geoConflictCount,
                'totalConflictCount'   => $totalConflictCount,
                'conflictRegIds'       => array_values(array_unique($conflictRegIds)),
                'geoConflictRegIds'    => array_values(array_unique($geoConflictRegIds)),
                'phoneConflictRegIds'  => array_values(array_unique($phoneConflictRegIds)),
                'conflictMap'          => $conflictMap,
                'geoDatabase'          => $geoDatabase,
                'divisionStats'        => $divisionStats,
                'districtStats'        => $districtStats,
                'categoryStats'        => $categoryStats,
                'littleMagList'        => $littleMagList,
                'totalLittleMagCount'  => $totalLittleMagCount,
                'totalGeoDivisions'    => $totalGeoDivisions,
                'totalGeoDistricts'    => $totalGeoDistricts,
            ];
        });

        // Unpack cached analysis instantly without re-querying
        $totalRegistrations  = $analysis['totalRegistrations'];
        $totalCollected      = $analysis['totalCollected'];
        $pendingCount        = $analysis['pendingCount'];
        $duplicatePhoneMap   = $analysis['duplicatePhoneMap'];
        $phoneConflictCount  = $analysis['phoneConflictCount'];
        $geoConflictCount    = $analysis['geoConflictCount'];
        $totalConflictCount  = $analysis['totalConflictCount'];
        $conflictMap         = $analysis['conflictMap'];
        $geoDatabase         = $analysis['geoDatabase'];
        $divisionStats       = $analysis['divisionStats'];
        $districtStats       = $analysis['districtStats'];
        $categoryStats       = $analysis['categoryStats'];
        $littleMagList       = $analysis['littleMagList'];
        $totalLittleMagCount = $analysis['totalLittleMagCount'];
        $totalGeoDivisions   = $analysis['totalGeoDivisions'];
        $totalGeoDistricts   = $analysis['totalGeoDistricts'];

        // Fast Conflict Filtering
        if ($request->boolean('has_conflict')) {
            $conflictType = $request->input('conflict_type');
            if ($conflictType === 'geo') {
                $targetIds = $analysis['geoConflictRegIds'];
            } elseif ($conflictType === 'phone') {
                $targetIds = $analysis['phoneConflictRegIds'];
            } else {
                $targetIds = $analysis['conflictRegIds'];
            }
            $regQuery->whereIn('id', !empty($targetIds) ? $targetIds : [-1]);
        }

        $perPage = (int) $request->input('per_page', $campaign->table_settings['per_page'] ?? 25);
        if (!in_array($perPage, [10, 25, 50, 100, 250], true)) {
            $perPage = 25;
        }

        $registrations = $regQuery->paginate($perPage)->withQueryString();

        return view('admin.event_campaigns.show', compact(
            'campaign',
            'registrations',
            'totalRegistrations',
            'totalCollected',
            'pendingCount',
            'perPage',
            'geoDatabase',
            'divisionStats',
            'districtStats',
            'categoryStats',
            'littleMagList',
            'totalLittleMagCount',
            'totalGeoDivisions',
            'totalGeoDistricts',
            'duplicatePhoneMap',
            'phoneConflictCount',
            'geoConflictCount',
            'totalConflictCount',
            'conflictMap',
            'sort'
        ));
    }

    /**
     * Show form to edit Event Campaign.
     */
    public function edit(EventCampaign $campaign)
    {
        return view('admin.event_campaigns.edit', compact('campaign'));
    }

    /**
     * Update Event Campaign.
     */
    public function update(Request $request, EventCampaign $campaign)
    {
        $validated = $request->validate([
            'title'                => 'required|string|max:255',
            'slug'                 => 'required|string|max:100|alpha_dash|unique:event_campaigns,slug,' . $campaign->id,
            'type'                 => 'required|string|in:event,donation,scholarship,competition,workshop,library,book_grant',
            'badge_text'           => 'nullable|string|max:50',
            'short_description'    => 'nullable|string|max:500',
            'description'          => 'nullable|string',
            'banner_image'         => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:6144',
            'theme_color'          => 'nullable|string|max:30',
            'has_fee_or_donation'  => 'nullable|boolean',
            'fee_amount'           => 'nullable|numeric|min:0',
            'is_donation_flexible' => 'nullable|boolean',
            'min_donation'         => 'nullable|numeric|min:0',
            'payment_methods'      => 'nullable|string',
            'payment_instructions' => 'nullable|string|max:2000',
            'starts_at'            => 'nullable|date',
            'ends_at'              => 'nullable|date|after_or_equal:starts_at',
            'max_participants'     => 'nullable|integer|min:1',
            'is_active'            => 'nullable|boolean',
            'success_message'      => 'nullable|string|max:500',
            'redirect_url'         => 'nullable|url|max:255',
            'contact_phone'        => 'nullable|string|max:30',
            'contact_email'        => 'nullable|email|max:100',
            'custom_fields_json'   => 'nullable|string',
            'form_settings_json'   => 'nullable|string',
            'remove_banner'        => 'nullable|boolean',
            'sch_title'            => 'nullable|string|max:80',
            'sch_heading'          => 'nullable|string|max:60',
            'sch_session'          => 'nullable|string|max:60',
            'sch_logo_side'        => 'nullable|in:left,center,hidden',
            'number_prefix'        => ['nullable', 'regex:/^[A-Za-z0-9]{1,12}$/'],
            'form_logo_image'      => 'nullable|file|mimes:jpeg,png,jpg,webp,svg,gif,bmp|max:8192',
        ], [
            'banner_image.max' => 'Banner is larger than 6 MB.',
            'banner_image.uploaded' => 'Banner upload failed (file too large for the server). Pick a smaller image.',
            'form_logo_image.max' => 'Logo is larger than 8 MB.',
            'form_logo_image.uploaded' => 'Logo upload failed (file too large for the server). Pick a smaller image.',
        ]);

        if ($request->hasFile('banner_image')) {
            // Store the new file first; the old one is removed only once the new one is safely saved.
            $validated['banner_image'] = $request->file('banner_image')->store('campaigns', 'public');
            if ($campaign->banner_image && Storage::disk('public')->exists($campaign->banner_image)) {
                Storage::disk('public')->delete($campaign->banner_image);
            }
        } elseif ($request->boolean('remove_banner')) {
            if ($campaign->banner_image && Storage::disk('public')->exists($campaign->banner_image)) {
                Storage::disk('public')->delete($campaign->banner_image);
            }
            $validated['banner_image'] = null;
        }
        unset($validated['remove_banner'], $validated['form_logo_image'], $validated['sch_title'], $validated['sch_heading'], $validated['sch_session'], $validated['sch_logo_side'], $validated['number_prefix']);

        $validated['has_fee_or_donation'] = !empty($request->has_fee_or_donation);
        $validated['is_donation_flexible'] = !empty($request->is_donation_flexible);
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;
        $validated['slug'] = strtolower(trim($validated['slug']));
        $validated['theme_color'] = ($validated['theme_color'] ?? null) ?: '#0284c7';

        if ($request->has('custom_fields_json')) {
            $fields = json_decode($request->custom_fields_json, true);
            $validated['custom_fields'] = is_array($fields) ? $fields : [];
        }

        $fSettings = $campaign->form_settings ?: [];
        if ($request->has('form_settings_json')) {
            $decoded = json_decode($request->form_settings_json, true);
            if (is_array($decoded)) {
                // The logo file path is owned by the server. The page's copy can be stale (e.g. the logo was
                // changed from the campaign page meanwhile) and would point at a deleted file.
                unset($decoded['logo_image']);
                $fSettings = array_merge($fSettings, $decoded);
            }
        }

        // Form logo handling from edit page
        if ($request->boolean('remove_form_logo')) {
            if (!empty($fSettings['logo_image']) && Storage::disk('public')->exists($fSettings['logo_image'])) {
                Storage::disk('public')->delete($fSettings['logo_image']);
            }
            unset($fSettings['logo_image']);
        } elseif ($request->hasFile('form_logo_image')) {
            $newLogo = $request->file('form_logo_image')->store('campaigns/logos', 'public');
            if (!empty($fSettings['logo_image']) && Storage::disk('public')->exists($fSettings['logo_image'])) {
                Storage::disk('public')->delete($fSettings['logo_image']);
            }
            $fSettings['logo_image'] = $newLogo;
        }

        // scholarship header & number prefix (kept only when filled; empty falls back to the defaults)
        foreach (['sch_title', 'sch_heading', 'sch_session', 'sch_logo_side', 'number_prefix'] as $schKey) {
            if ($request->has($schKey)) {
                $v = trim((string) $request->input($schKey));
                if ($v === '') { unset($fSettings[$schKey]); } else { $fSettings[$schKey] = $v; }
            }
        }

        if ($request->filled('form_logo_size')) {
            $fSettings['logo_size'] = max(30, min(200, intval($request->form_logo_size)));
        }
        if ($request->has('form_venue')) {
            $fSettings['form_venue'] = trim($request->input('form_venue'));
        }
        if ($request->has('form_date')) {
            $fSettings['form_date'] = trim($request->input('form_date'));
        }
        if ($request->has('form_org')) {
            $fSettings['form_org'] = trim($request->input('form_org'));
        }
        if ($request->has('form_copy_tag')) {
            $fSettings['form_copy_tag'] = trim($request->input('form_copy_tag'));
        }

        $validated['form_settings'] = $fSettings;

        $campaign->update($validated);

        return redirect()->route('admin.event-campaigns.show', $campaign->id)
            ->with('success', 'Campaign updated successfully.');
    }

    /**
     * Quick Title & Badge update via AJAX or Form submit.
     */
    public function updateTitle(Request $request, EventCampaign $campaign)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'badge_text' => 'nullable|string|max:50',
            'slug'       => 'nullable|string|max:100|alpha_dash|unique:event_campaigns,slug,' . $campaign->id,
        ]);

        $campaign->title = trim($validated['title']);
        if ($request->has('badge_text')) {
            $campaign->badge_text = $validated['badge_text'];
        }
        if (!empty($validated['slug'])) {
            $campaign->slug = strtolower(trim($validated['slug']));
        }
        $campaign->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'title'      => $campaign->title,
                'badge_text' => $campaign->badge_text,
                'slug'       => $campaign->slug,
                'public_url' => $campaign->public_url,
                'message'    => 'Title updated successfully.',
            ]);
        }

        return back()->with('success', 'Title updated successfully.');
    }

    /**
     * Save table customization settings (visible columns, per-page, density).
     */
    public function updateTableSettings(Request $request, EventCampaign $campaign)
    {
        $settings = [
            'columns'  => is_array($request->columns) ? $request->columns : [],
            'per_page' => in_array((int)$request->per_page, [10, 25, 50, 100, 250], true) ? (int)$request->per_page : 25,
            'density'  => in_array($request->density, ['compact', 'standard', 'spacious'], true) ? $request->density : 'standard',
        ];

        $campaign->table_settings = $settings;
        $campaign->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'table_settings' => $campaign->table_settings,
                'message'        => 'Table settings updated.',
            ]);
        }

        return back()->with('success', 'Table settings updated.');
    }

    /**
     * Toggle Active/Inactive status via AJAX or POST.
     */
    public function toggleStatus(Request $request, EventCampaign $campaign)
    {
        $campaign->is_active = !$campaign->is_active;
        $campaign->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'is_active' => (bool) $campaign->is_active,
                'message'   => $campaign->is_active ? 'Campaign activated.' : 'Campaign deactivated.',
            ]);
        }

        return back()->with('success', $campaign->is_active ? 'Campaign activated.' : 'Campaign deactivated.');
    }

    /**
     * Duplicate/Clone an existing Campaign with all its settings.
     */
    public function clone(EventCampaign $campaign)
    {
        $newSlug = $campaign->slug . '-copy';
        $counter = 1;
        while (EventCampaign::where('slug', $newSlug)->exists()) {
            $newSlug = $campaign->slug . '-copy-' . (++$counter);
        }

        $clone = $campaign->replicate();
        $clone->title = $campaign->title . ' (Copy)';
        $clone->slug = $newSlug;
        $clone->is_active = false; // cloned starts as inactive
        $clone->created_at = now();
        $clone->updated_at = now();
        $clone->save();

        return redirect()->route('admin.event-campaigns.edit', $clone->id)
            ->with('success', "Campaign cloned successfully as '{$clone->title}'. Configure settings and activate.");
    }

    /**
     * Delete an Event Campaign.
     * Note: Users who registered remain intact in users table as registered customers!
     */
    public function destroy(EventCampaign $campaign)
    {
        $title = $campaign->title;
        $campaign->delete();

        return redirect()->route('admin.event-campaigns.index')
            ->with('success', "Campaign '{$title}' deleted. Registered customers remain safe in Users list.");
    }

    /**
     * Store new participant registration manually created by Admin.
     */
    public function storeRegistration(Request $request, EventCampaign $campaign)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'phone'                => 'required|string|max:30',
            'country_code'         => 'nullable|string|max:10',
            'email'                => 'nullable|email|max:255',
            'division'             => 'nullable|string|max:100',
            'district'             => 'nullable|string|max:100',
            'thana'                => 'nullable|string|max:100',
            'post_office'          => 'nullable|string|max:100',
            'address'              => 'nullable|string|max:500',
            'institution_or_org'   => 'nullable|string|max:255',
            'designation_or_class' => 'nullable|string|max:255',
            'author_category'      => 'nullable|string|max:255',
            'author_categories'    => 'nullable|array',
            'art_medium'           => 'nullable|string|max:255',
            'organization_name'    => 'nullable|string|max:255',
            'published_books_count'=> 'nullable|string|max:255',
            'notable_books'        => 'nullable|string|max:500',
            'magazine_name'        => 'nullable|string|max:255',
            'magazine_issue_count' => 'nullable|string|max:100',
            'amount_paid'          => 'nullable|numeric|min:0',
            'payment_method'       => 'nullable|string|max:50',
            'payment_status'       => 'nullable|string|in:pending,verified,waived,refunded,free',
            'transaction_id'       => 'nullable|string|max:100',
            'status'               => 'nullable|string|in:confirmed,pending,rejected,attended,selected',
            'admin_notes'          => 'nullable|string|max:2000',
            'photo'                => 'nullable|file|mimes:jpeg,png,jpg,webp,avif|max:10240',
            'cropped_photo_data'   => 'nullable|string',
            'optimized_photo_data' => 'nullable|string',
            'form_data'            => 'nullable|array',
            'genres'               => 'nullable|array',
            'pen_name'             => 'nullable|string|max:255',
            'published_book_count' => 'nullable|integer|min:0',
        ]);

        // Clean & Format Phone using universal PhoneNormalizer
        $countryCode = $request->input('country_code', '+880');
        $pInfo = \App\Support\PhoneNormalizer::normalize($validated['phone'], $countryCode);
        $localPhone = $pInfo['local_phone'];
        $formattedPhone = $pInfo['formatted_phone'];
        $cleanDigits = $pInfo['core'];
        $queryPhones = $pInfo['query_phones'];

        // Auto Create or Find User
        $user = User::where(function ($q) use ($queryPhones, $pInfo) {
            $q->whereIn('phone', $queryPhones);
            if ($pInfo['is_bd'] && strlen($pInfo['core']) === 10) {
                $q->orWhere('phone', 'LIKE', '%' . $pInfo['core']);
            }
        })->first();

        if (!$user && !empty($validated['email'])) {
            $user = User::where('email', strtolower(trim($validated['email'])))->first();
        }

        if (!$user) {
            $email = !empty($validated['email']) ? strtolower(trim($validated['email'])) : 'participant_' . $cleanDigits . '@ideaabd.com';
            if (User::where('email', $email)->exists()) {
                $email = 'participant_' . $cleanDigits . '_' . time() . '@ideaabd.com';
            }
            $user = User::create([
                'name'              => $validated['name'],
                'phone'             => $localPhone,
                'email'             => $email,
                'password'          => \Illuminate\Support\Facades\Hash::make(Str::random(12)),
                'role'              => 'customer',
                'status'            => 'approved',
                'reg_type'          => 'customer',
                'phone_verified_at' => now(),
                'reg_data'          => [
                    'registered_via_admin_campaign' => $campaign->slug,
                    'registered_at'                 => now()->toDateTimeString(),
                ],
            ]);
        }

        // Form data payload
        $formData = $validated['form_data'] ?? [];
        if (!empty($validated['pen_name'])) {
            $formData['pen_name'] = $validated['pen_name'];
        }
        if (!empty($validated['genres'])) {
            $formData['genres'] = $validated['genres'];
        }
        if (isset($validated['published_book_count'])) {
            $formData['published_book_count'] = $validated['published_book_count'];
        }

        // Direct keys from registration form
        $directKeys = [
            'author_category', 'author_categories', 'art_medium', 'organization_name', 
            'published_books_count', 'notable_books', 'magazine_name', 'magazine_issue_count',
            'division', 'post_office', 'resident_type', 'country_name'
        ];
        foreach ($directKeys as $dk) {
            if ($request->filled($dk)) {
                $formData[$dk] = $request->input($dk);
            }
        }

        if ($request->filled('author_category')) {
            $catSingle = trim($request->input('author_category'));
            $formData['author_category'] = $catSingle;
            $formData['author_categories'] = [$catSingle];
        }

        if ($request->filled('division')) {
            $formData['perm_division'] = $request->input('division');
        } elseif (!empty($validated['district'])) {
            $inferredDiv = BangladeshGeo::getDivisionForDistrict($validated['district']);
            if ($inferredDiv) {
                $formData['perm_division'] = $inferredDiv;
            }
        }
        if ($request->filled('post_office')) {
            $formData['perm_post_office'] = $request->input('post_office');
        }

        // Photo Upload & Cropped / Optimized Base64 Handling
        if ($request->filled('cropped_photo_data') || $request->filled('optimized_photo_data')) {
            $rawBase64 = $request->input('cropped_photo_data') ?: $request->input('optimized_photo_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $rawBase64, $type)) {
                $rawBase64 = substr($rawBase64, strpos($rawBase64, ',') + 1);
                $ext = strtolower($type[1]);
                $imageContent = base64_decode($rawBase64);
                if ($imageContent) {
                    $fileName = 'campaigns/participants/' . Str::random(24) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                    \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageContent);
                    $formData['author_photo'] = $fileName;
                    $formData['student_photo'] = $fileName;
                }
            }
        } elseif ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('campaigns/participants', 'public');
            $formData['author_photo'] = $photoPath;
            $formData['student_photo'] = $photoPath;
        }

        // Generate Registration Number
        $regNumber = EventRegistration::generateRegNumber($campaign->slug);

        $regStatus = $validated['status'] ?? 'confirmed';
        $payStatus = $validated['payment_status'] ?? ($campaign->has_fee_or_donation ? 'pending' : 'free');

        // Composite full address if needed
        $addressVal = $validated['address'] ?? null;
        if (empty($addressVal) && ($request->filled('division') || $request->filled('district') || $request->filled('thana'))) {
            $parts = array_filter([
                $request->input('post_office') ? 'পোস্ট: ' . $request->input('post_office') : null,
                $request->input('thana'),
                $request->input('district'),
                $request->input('division')
            ]);
            $addressVal = implode(', ', $parts);
        }

        $designationVal = $validated['designation_or_class'] ?? null;
        if (empty($designationVal) && $request->filled('author_category')) {
            $designationVal = $request->input('author_category');
        }

        $institutionVal = $validated['institution_or_org'] ?? null;
        if (empty($institutionVal) && $request->filled('organization_name')) {
            $institutionVal = $request->input('organization_name');
        }

        $registration = EventRegistration::create([
            'event_campaign_id'    => $campaign->id,
            'user_id'              => $user->id,
            'registration_number'  => $regNumber,
            'name'                 => $validated['name'],
            'phone'                => $localPhone,
            'email'                => $validated['email'] ?? ($user->email ?: null),
            'district'             => $validated['district'] ?? null,
            'thana'                => $validated['thana'] ?? null,
            'address'              => $addressVal,
            'institution_or_org'   => $institutionVal,
            'designation_or_class' => $designationVal,
            'amount_paid'          => floatval($validated['amount_paid'] ?? 0),
            'payment_method'       => $validated['payment_method'] ?? 'cash_or_admin',
            'payment_status'       => $payStatus,
            'transaction_id'       => $validated['transaction_id'] ?? null,
            'status'               => $regStatus,
            'admin_notes'          => $validated['admin_notes'] ?? 'Added manually by admin',
            'form_data'            => $formData,
            'ip_address'           => $request->ip(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'registration' => $registration,
                'message'      => "Participant #{$regNumber} ({$registration->name}) successfully registered!",
            ]);
        }

        return back()->with('success', "Participant #{$regNumber} ({$registration->name}) successfully registered by Admin.");
    }

    /**
     * Update individual participant details, status, profile, or payment.
     */
    public function updateRegistration(Request $request, EventRegistration $registration)
    {
        $validated = $request->validate([
            'name'                 => 'nullable|string|max:255',
            'phone'                => 'nullable|string|max:30',
            'email'                => 'nullable|email|max:255',
            'district'             => 'nullable|string|max:100',
            'thana'                => 'nullable|string|max:100',
            'address'              => 'nullable|string|max:500',
            'institution_or_org'   => 'nullable|string|max:255',
            'designation_or_class' => 'nullable|string|max:255',
            'amount_paid'          => 'nullable|numeric|min:0',
            'payment_method'       => 'nullable|string|max:50',
            'payment_status'       => 'nullable|string|in:pending,verified,waived,refunded,free',
            'transaction_id'       => 'nullable|string|max:100',
            'status'               => 'nullable|string|in:confirmed,pending,rejected,attended,selected',
            'admin_notes'          => 'nullable|string|max:2000',
            'photo'                => 'nullable|file|mimes:jpeg,png,jpg,webp,avif|max:10240',
            'form_data'            => 'nullable|array',
            'genres'               => 'nullable|array',
            'pen_name'             => 'nullable|string|max:255',
            'published_book_count' => 'nullable|integer|min:0',
        ]);

        $formData = $registration->form_data ?? [];
        $updateData = [];

        if (isset($validated['pen_name'])) {
            $formData['pen_name'] = $validated['pen_name'];
        }
        if (isset($validated['genres'])) {
            $formData['genres'] = $validated['genres'];
        }
        if (isset($validated['published_book_count'])) {
            $formData['published_book_count'] = $validated['published_book_count'];
        }
        if (!empty($validated['form_data']) && is_array($validated['form_data'])) {
            $formData = array_merge($formData, $validated['form_data']);
        }

        // Additional direct form inputs support (e.g. from Admin Edit Modal)
        $directKeys = [
            'resident_type', 'country_name', 'custom_country', 'state_or_city', 'zip_code', 'foreign_address',
            'perm_division', 'permanent_district', 'perm_upazila', 'perm_post_office', 'perm_village',
            'author_category', 'author_categories', 'art_medium', 'organization_name', 'published_books_count', 'notable_books', 'magazine_name', 'magazine_issue_count',
            'father_name', 'mother_name', 'guardian_name', 'guardian_phone', 'gender', 'religion', 'birth_date', 'marital_status',
            'national_id', 'father_occupation', 'mother_occupation', 'father_status', 'mother_status', 'father_income', 'mother_income', 'father_phone', 'mother_phone', 'guardian_relation', 'guardian_income',
            'family_count', 'earning_count', 'orphan_status', 'disability', 'disability_type', 'disability_details', 'current_year',
            'pay_method', 'pay_account_owner', 'pay_account_name', 'pay_account_no', 'pay_bank_name', 'pay_bank_branch',
            'prev_joyee', 'books_read_count', 'reading_habit', 'books_read_names', 'scholarship_reason',
            'library_name', 'library_type', 'established_year', 'reg_no', 'president_name', 'president_phone', 'secretary_name', 'secretary_phone',
            'reader_count', 'current_book_count', 'preferred_genres'
        ];
        foreach ($directKeys as $dk) {
            if ($request->has($dk)) {
                $formData[$dk] = $request->input($dk);
            }
        }

        if ($request->filled('perm_division')) {
            $formData['perm_division'] = $request->input('perm_division');
        } elseif ($request->filled('division')) {
            $formData['perm_division'] = $request->input('division');
        } elseif (!empty($validated['district']) && empty($formData['perm_division'])) {
            $inferredDiv = BangladeshGeo::getDivisionForDistrict($validated['district']);
            if ($inferredDiv) {
                $formData['perm_division'] = $inferredDiv;
            }
        }

        if ($request->has('author_category') && is_string($request->input('author_category'))) {
            $catSingle = trim($request->input('author_category'));
            if ($catSingle !== '') {
                $formData['author_category'] = $catSingle;
                $formData['author_categories'] = [$catSingle];

                // If moving away from editor to writer (লেখক, কবি ইত্যাদি)
                $isEditor = (mb_strpos($catSingle, 'সম্পাদক') !== false || mb_strpos($catSingle, 'লিটিলম্যাগ') !== false);
                if (!$isEditor) {
                    if ($request->boolean('clear_magazine') || !$request->filled('magazine_name')) {
                        unset($formData['magazine_name']);
                        unset($formData['magazine_issue_count']);
                    }
                    if ($registration->designation_or_class === 'সম্পাদক' || mb_strpos((string)$registration->designation_or_class, 'সম্পাদক') !== false) {
                        $updateData['designation_or_class'] = $catSingle;
                    }
                }
            }
        } elseif ($request->has('author_categories') && is_array($request->input('author_categories'))) {
            $catList = array_values(array_filter($request->input('author_categories')));
            $formData['author_categories'] = $catList;
            $formData['author_category'] = implode(', ', $catList);
        }

        // Resident Type & Country Normalization
        $resType = $request->input('resident_type', $formData['resident_type'] ?? 'domestic');
        if ($resType === 'international' || $resType === 'foreign') {
            $formData['resident_type'] = 'international';
            $cName = $request->input('country_name', $formData['country_name'] ?? '');
            if ($cName === 'OTHER' && $request->filled('custom_country')) {
                $cName = trim($request->input('custom_country'));
            }
            $formData['country_name'] = $cName ?: 'Foreign/International';
        } else {
            $formData['resident_type'] = 'domestic';
            $formData['country_name'] = 'বাংলাদেশ (Bangladesh)';
            unset($formData['custom_country'], $formData['state_or_city'], $formData['zip_code'], $formData['foreign_address']);
        }

        // Photo Replacement / Cropper Handling
        if ($request->filled('cropped_photo_data')) {
            $base64 = $request->input('cropped_photo_data');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64)) {
                $data = substr($base64, strpos($base64, ',') + 1);
                $data = base64_decode($data);
                if ($data !== false) {
                    $oldPhoto = $formData['student_photo'] ?? ($formData['author_photo'] ?? null);
                    if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
                        Storage::disk('public')->delete($oldPhoto);
                    }
                    $filename = 'campaigns/participants/' . Str::random(32) . '.jpg';
                    Storage::disk('public')->put($filename, $data);
                    $formData['author_photo'] = $filename;
                    $formData['student_photo'] = $filename;
                }
            }
        } elseif ($request->hasFile('photo')) {
            $oldPhoto = $formData['student_photo'] ?? ($formData['author_photo'] ?? null);
            if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }
            $newPhoto = $request->file('photo')->store('campaigns/participants', 'public');
            $formData['author_photo'] = $newPhoto;
            $formData['student_photo'] = $newPhoto;
        }

        $updateData = array_merge($updateData, array_filter($validated, fn($k) => in_array($k, [
            'name', 'phone', 'email', 'district', 'thana', 'address',
            'institution_or_org', 'designation_or_class', 'amount_paid',
            'payment_method', 'payment_status', 'transaction_id', 'status', 'admin_notes'
        ]), ARRAY_FILTER_USE_KEY));

        if (!empty($updateData['phone'])) {
            $updateData['phone'] = \App\Support\PhoneNormalizer::toLocal($updateData['phone']);
        }

        $oldStatus = $registration->status;
        $updateData['form_data'] = $formData;

        $registration->update($updateData);

        if ($request->boolean('sync_user_phone') && $registration->user && !empty($updateData['phone'])) {
            $registration->user->update(['phone' => $updateData['phone']]);
        }

        // SMS notification on status transitions
        if (!in_array($oldStatus, ['confirmed', 'selected', 'approved'], true) && in_array($registration->status, ['confirmed', 'selected'], true)) {
            try {
                $downloadUrl = url('/card/' . $registration->registration_number);
                $smsText = "রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলায় আপনাকে স্বাগত। Download your card: {$downloadUrl} — আইডিয়া প্রকাশন";
                \App\Services\SmsService::send($registration->phone, $smsText);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Update Registration Approval SMS error: " . $e->getMessage());
            }
        } elseif ($oldStatus !== 'rejected' && $registration->status === 'rejected') {
            try {
                $smsText = "রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলা ২০২৬-এ আপনার নিবন্ধনটি বাতিল করা হয়েছে। অনুসন্ধানে: 01726976982 — আইডিয়া প্রকাশন";
                \App\Services\SmsService::send($registration->phone, $smsText);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Update Registration Rejection SMS error: " . $e->getMessage());
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'registration' => $registration,
                'message'      => "Participant #{$registration->registration_number} updated successfully.",
            ]);
        }

        return back()->with('success', "Participant #{$registration->registration_number} updated successfully.");
    }

    /**
     * Delete individual participant registration.
     */
    public function destroyRegistration(Request $request, EventRegistration $registration)
    {
        $regNumber = $registration->registration_number;
        $name = $registration->name;

        // Delete uploaded photo if exists
        $formData = $registration->form_data ?? [];
        $photo = $formData['student_photo'] ?? ($formData['author_photo'] ?? null);
        if ($photo && Storage::disk('public')->exists($photo)) {
            Storage::disk('public')->delete($photo);
        }

        $registration->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Participant #{$regNumber} ({$name}) has been deleted successfully.",
            ]);
        }

        return back()->with('success', "Participant #{$regNumber} ({$name}) has been deleted successfully.");
    }

    /**
     * Synchronize or resolve phone conflict between registration and linked user account.
     */
    public function syncUserPhone(Request $request, EventRegistration $registration)
    {
        $direction = $request->input('direction', 'to_reg'); // 'to_reg' or 'to_user'
        $user = $registration->user;

        if (!$user && !empty($registration->phone)) {
            $foundUser = \App\Models\User::where('phone', $registration->phone)->first();
            if ($foundUser) {
                $registration->update(['user_id' => $foundUser->id]);
                return response()->json([
                    'success' => true,
                    'message' => "নিবন্ধনটি ইউজার #{$foundUser->id} ({$foundUser->name})-এর সাথে সফলভাবে লিংক করা হয়েছে।",
                ]);
            }
        }

        if ($user) {
            if ($direction === 'to_user') {
                $user->update(['phone' => $registration->phone]);
                $msg = "ইউজার #{$user->id}-এর ফোন নম্বর পরিবর্তন করে {$registration->phone} করা হয়েছে।";
            } else {
                $registration->update(['phone' => $user->phone]);
                $msg = "নিবন্ধনের ফোন নম্বর পরিবর্তন করে ইউজারের ফোন নম্বর ({$user->phone}) অনুযায়ী সিঙ্ক করা হয়েছে।";
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'কোনো ইউজার অ্যাকাউন্ট পাওয়া যায়নি।',
        ], 404);
    }

    /**
     * Resolve Division/District Geo conflict by syncing the canonical division for the participant's district.
     */
    public function resolveGeoConflict(Request $request, EventRegistration $registration)
    {
        $rawDist = trim($registration->district ?? $registration->form_data['district'] ?? $registration->form_data['perm_district'] ?? '');
        $normDist = \App\Support\BangladeshGeo::normalizeDistrict($rawDist);
        $expectedDiv = $normDist ? \App\Support\BangladeshGeo::getDivisionForDistrict($normDist) : null;

        if (empty($expectedDiv)) {
            $expectedDiv = $request->input('target_division');
        }

        if (empty($expectedDiv)) {
            return response()->json([
                'success' => false,
                'message' => "জেলা '{$rawDist}'-এর জন্য কোনো নির্ধারিত বিভাগ পাওয়া যায়নি।",
            ], 422);
        }

        $formData = (array) ($registration->form_data ?? []);
        $formData['perm_division'] = $expectedDiv;
        if (isset($formData['division'])) {
            $formData['division'] = $expectedDiv;
        }

        $updateData = ['form_data' => $formData];
        if (\Illuminate\Support\Facades\Schema::hasColumn('entries', 'division')) {
            $updateData['division'] = $expectedDiv;
        }

        $registration->update($updateData);

        $msg = "নিবন্ধন #{$registration->registration_number} ({$registration->name})-এর বিভাগ সংশোধন করে '{$expectedDiv}' করা হয়েছে।";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'           => true,
                'message'           => $msg,
                'resolved_division' => $expectedDiv,
                'resolved_district' => $normDist ?: $rawDist,
                'registration_id'   => $registration->id,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Bulk resolve all Division/District geo conflicts for an entire event campaign.
     */
    public function bulkResolveGeoConflicts(Request $request, EventCampaign $campaign)
    {
        $registrations = $campaign->registrations;
        $resolvedCount = 0;

        foreach ($registrations as $reg) {
            $rawDiv = trim($reg->form_data['perm_division'] ?? $reg->form_data['division'] ?? '');
            $rawDist = trim($reg->district ?? $reg->form_data['district'] ?? $reg->form_data['perm_district'] ?? '');

            $normDiv = \App\Support\BangladeshGeo::normalizeDivision($rawDiv);
            $normDist = \App\Support\BangladeshGeo::normalizeDistrict($rawDist);
            $expectedDiv = $normDist ? \App\Support\BangladeshGeo::getDivisionForDistrict($normDist) : null;

            if (!empty($normDiv) && !empty($expectedDiv) && $normDiv !== $expectedDiv) {
                $formData = (array) ($reg->form_data ?? []);
                $formData['perm_division'] = $expectedDiv;
                if (isset($formData['division'])) {
                    $formData['division'] = $expectedDiv;
                }
                $updateData = ['form_data' => $formData];
                if (\Illuminate\Support\Facades\Schema::hasColumn('entries', 'division')) {
                    $updateData['division'] = $expectedDiv;
                }
                $reg->update($updateData);
                $resolvedCount++;
            }
        }

        $msg = "মোট {$resolvedCount}টি নিবন্ধনের বিভাগ-জেলা অমিল স্বয়ংক্রিয়ভাবে সংশোধন করা হয়েছে।";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'message'        => $msg,
                'resolved_count' => $resolvedCount,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * One-click Toggle Selection / Award of Scholarship (বৃত্তিপ্রাপ্ত নির্বাচিত বাটন).
     */
    public function toggleScholarship(Request $request, EventRegistration $registration)
    {
        $isAwarded = ($registration->status === 'selected' || !empty($registration->form_data['is_scholarship_awarded']));
        $newAwarded = !$isAwarded;
        
        $formData = $registration->form_data ?? [];
        $formData['is_scholarship_awarded'] = $newAwarded;
        $formData['scholarship_awarded_at'] = $newAwarded ? now()->toDateTimeString() : null;

        $registration->update([
            'status'    => $newAwarded ? 'selected' : 'confirmed',
            'form_data' => $formData,
        ]);

        $msg = $newAwarded 
            ? "Applicant #{$registration->registration_number} ({$registration->name}) is SELECTED for Scholarship!"
            : "Applicant #{$registration->registration_number} ({$registration->name}) scholarship selection removed.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'is_awarded' => $newAwarded,
                'status'     => $registration->status,
                'message'    => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * One-click Status Update (Approve, Reject, Pending) for Event/Writer Delegate Registrations.
     */
    public function toggleApproval(Request $request, EventRegistration $registration)
    {
        $requestedStatus = $request->input('status');
        if ($requestedStatus && in_array($requestedStatus, ['confirmed', 'rejected', 'pending', 'selected', 'attended'])) {
            $newStatus = $requestedStatus;
        } else {
            $isApproved = ($registration->status === 'confirmed' || $registration->status === 'selected' || $registration->status === 'approved');
            $newStatus = $isApproved ? 'pending' : 'confirmed';
        }

        $registration->update([
            'status' => $newStatus,
        ]);

        $campaign = $registration->campaign;
        $pendingCount = $campaign ? $campaign->registrations()->where('status', 'pending')->count() : 0;
        $confirmedCount = $campaign ? $campaign->registrations()->whereIn('status', ['confirmed', 'selected', 'approved'])->count() : 0;

        if ($newStatus === 'confirmed' || $newStatus === 'selected') {
            // SMS on approval
            try {
                $downloadUrl = url('/card/' . $registration->registration_number);
                $smsText = "রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলায় আপনাকে স্বাগত। Download your card: {$downloadUrl} — আইডিয়া প্রকাশন";
                \App\Services\SmsService::send($registration->phone, $smsText);
            } catch (\Throwable $e) {
                Log::warning("Approval SMS error: " . $e->getMessage());
            }

            $msg = "নিবন্ধন #{$registration->registration_number} ({$registration->name}) সফলভাবে এপ্রুভ (Approved) করা হয়েছে এবং স্বাগত ও কার্ড ডাউনলোড লিংক পাঠানো হয়েছে!";
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'         => true,
                    'status'          => $newStatus,
                    'status_label'    => $newStatus === 'selected' ? 'Selected' : 'Confirmed',
                    'approved'        => true,
                    'message'         => $msg,
                    'registration_id' => $registration->id,
                    'pending_count'   => $pendingCount,
                    'confirmed_count' => $confirmedCount,
                ]);
            }

            return back()->with('success', $msg);
        }

        if ($newStatus === 'rejected') {
            // SMS on rejection / cancellation
            try {
                $smsText = "রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলা ২০২৬-এ আপনার নিবন্ধনটি বাতিল করা হয়েছে। অনুসন্ধানে: 01726976982 — আইডিয়া প্রকাশন";
                \App\Services\SmsService::send($registration->phone, $smsText);
            } catch (\Throwable $e) {
                Log::warning("Rejection SMS error: " . $e->getMessage());
            }

            $msg = "নিবন্ধন #{$registration->registration_number} ({$registration->name}) সফলভাবে বাতিল (Rejected) করা হয়েছে এবং নোটিশ পাঠানো হয়েছে!";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'         => true,
                    'status'          => 'rejected',
                    'status_label'    => 'Rejected',
                    'approved'        => false,
                    'message'         => $msg,
                    'registration_id' => $registration->id,
                    'pending_count'   => $pendingCount,
                    'confirmed_count' => $confirmedCount,
                ]);
            }

            return back()->with('warning', $msg);
        }

        $msg = "নিবন্ধন #{$registration->registration_number} ({$registration->name}) স্ট্যাটাস পেন্ডিং (Pending) করা হয়েছে।";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'         => true,
                'status'          => 'pending',
                'status_label'    => 'Pending',
                'approved'        => false,
                'message'         => $msg,
                'registration_id' => $registration->id,
                'pending_count'   => $pendingCount,
                'confirmed_count' => $confirmedCount,
            ]);
        }

        return back()->with('info', $msg);
    }

    /**
     * Handle bulk actions on selected registrations.
     */
    public function bulkAction(Request $request, EventCampaign $campaign)
    {
        $action = $request->input('action');
        $rawIds = $request->input('selected_ids');
        $ids = is_array($rawIds) ? $rawIds : json_decode($rawIds, true);

        if (empty($ids) || !is_array($ids)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'কোনো অংশগ্রহণকারী নির্বাচন করা হয়নি।'], 422);
            }
            return back()->with('error', 'No participants selected for bulk action.');
        }

        $query = $campaign->registrations()->whereIn('id', $ids);
        $count = $query->count();

        switch ($action) {
            case 'approve':
                $regs = $query->with('campaign')->get();
                foreach ($regs as $r) {
                    $r->update(['status' => 'confirmed']);
                    try {
                        $downloadUrl = url('/card/' . $r->registration_number);
                        $smsText = "রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলায় আপনাকে স্বাগত। Download your card: {$downloadUrl} — আইডিয়া প্রকাশন";
                        \App\Services\SmsService::send($r->phone, $smsText);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Bulk Approval SMS error: " . $e->getMessage());
                    }
                }
                $msg = "{$count} participant(s) approved and notified via SMS with card download link.";
                break;

            case 'reject':
                $regs = $query->with('campaign')->get();
                foreach ($regs as $r) {
                    $r->update(['status' => 'rejected']);
                    try {
                        $smsText = "রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলা ২০২৬-এ আপনার নিবন্ধনটি বাতিল করা হয়েছে। অনুসন্ধানে: 01726976982 — আইডিয়া প্রকাশন";
                        \App\Services\SmsService::send($r->phone, $smsText);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Bulk Rejection SMS error: " . $e->getMessage());
                    }
                }
                $msg = "{$count} participant(s) rejected and notified via SMS.";
                break;

            case 'select_scholarship':
                $regs = $query->with('campaign')->get();
                foreach ($regs as $r) {
                    $fd = $r->form_data ?? [];
                    $fd['is_scholarship_awarded'] = true;
                    $fd['scholarship_awarded_at'] = now()->toDateTimeString();
                    $r->update(['status' => 'selected', 'form_data' => $fd]);
                    try {
                        $downloadUrl = $r->scholarshipUrl('print');
                        $smsText = "অভিনন্দন! '{$r->campaign->title}'-এ আপনি বৃত্তির জন্য প্রাথমিকভাবে নির্বাচিত হয়েছেন। কার্ড ডাউনলোড লিংক: {$downloadUrl} — আইডিয়া প্রকাশন";
                        \App\Services\SmsService::send($r->phone, $smsText);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Scholarship Selection SMS Error: " . $e->getMessage());
                    }
                }
                $msg = "{$count} applicant(s) marked as Selected for Scholarship & notified via SMS.";
                break;

            case 'mark_pending':
                $query->update(['status' => 'pending']);
                $msg = "{$count} participant(s) marked as Pending.";
                break;

            case 'mark_attended':
                $query->update(['status' => 'attended']);
                $msg = "{$count} participant(s) marked as Attended.";
                break;

            case 'verify_payment':
                $query->update(['payment_status' => 'verified']);
                $msg = "{$count} participant(s) payment marked as Verified.";
                break;

            case 'resolve_geo':
                $resolved = 0;
                $regs = $query->get();
                foreach ($regs as $reg) {
                    $rawDist = trim($reg->district ?? $reg->form_data['district'] ?? $reg->form_data['perm_district'] ?? '');
                    $normDist = \App\Support\BangladeshGeo::normalizeDistrict($rawDist);
                    $expectedDiv = $normDist ? \App\Support\BangladeshGeo::getDivisionForDistrict($normDist) : null;
                    if ($expectedDiv) {
                        $fd = (array) ($reg->form_data ?? []);
                        $fd['perm_division'] = $expectedDiv;
                        if (isset($fd['division'])) {
                            $fd['division'] = $expectedDiv;
                        }
                        $updateData = ['form_data' => $fd];
                        if (\Illuminate\Support\Facades\Schema::hasColumn('entries', 'division')) {
                            $updateData['division'] = $expectedDiv;
                        }
                        $reg->update($updateData);
                        $resolved++;
                    }
                }
                $msg = "নির্বাচিত {$resolved}টি আবেদনের বিভাগ-জেলা অমিল সংশোধন করা হয়েছে।";
                break;

            case 'delete':
                $query->delete();
                $msg = "{$count} participant(s) removed successfully.";
                break;

            default:
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Invalid bulk action specified.'], 400);
                }
                return back()->with('error', 'Invalid bulk action specified.');
        }

        $pendingCount = $campaign->registrations()->where('status', 'pending')->count();
        $confirmedCount = $campaign->registrations()->whereIn('status', ['confirmed', 'selected', 'approved'])->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'         => true,
                'count'           => $count,
                'action'          => $action,
                'message'         => $msg,
                'selected_ids'    => $ids,
                'pending_count'   => $pendingCount,
                'confirmed_count' => $confirmedCount,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Update Custom Delegate Card Background Design & Theme.
     */
    public function updateCardDesign(Request $request, EventCampaign $campaign)
    {
        try {
            $fSettings = $campaign->form_settings ?? [];
            $cardDesign = $fSettings['card_design'] ?? [];

            // 1. Background Image Handling
            if ($request->boolean('remove_bg_image')) {
                if (!empty($cardDesign['bg_image']) && Storage::disk('public')->exists($cardDesign['bg_image'])) {
                    Storage::disk('public')->delete($cardDesign['bg_image']);
                }
                $cardDesign['bg_image'] = null;
            } elseif ($request->hasFile('card_bg_image')) {
                $request->validate([
                    'card_bg_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg,gif,bmp|max:12288',
                ]);
                if (!empty($cardDesign['bg_image']) && Storage::disk('public')->exists($cardDesign['bg_image'])) {
                    Storage::disk('public')->delete($cardDesign['bg_image']);
                }
                $path = $request->file('card_bg_image')->store('campaigns/cards', 'public');
                $cardDesign['bg_image'] = $path;
            }

            // 1.1 Logo Image Handling (Upload / Remove)
            if ($request->boolean('remove_logo_image')) {
                if (!empty($cardDesign['logo_image']) && Storage::disk('public')->exists($cardDesign['logo_image'])) {
                    Storage::disk('public')->delete($cardDesign['logo_image']);
                }
                $cardDesign['logo_image'] = null;
            } elseif ($request->hasFile('card_logo_image')) {
                $request->validate([
                    'card_logo_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg,gif,bmp|max:8192',
                ]);
                if (!empty($cardDesign['logo_image']) && Storage::disk('public')->exists($cardDesign['logo_image'])) {
                    Storage::disk('public')->delete($cardDesign['logo_image']);
                }
                $logoPath = $request->file('card_logo_image')->store('campaigns/logos', 'public');
                $cardDesign['logo_image'] = $logoPath;
            }

            // 1.2 Event Logo Image Handling (হেডারের দ্বিতীয় ইভেন্ট লোগো)
            if ($request->boolean('remove_event_logo_image')) {
                if (!empty($cardDesign['event_logo_image']) && Storage::disk('public')->exists($cardDesign['event_logo_image'])) {
                    Storage::disk('public')->delete($cardDesign['event_logo_image']);
                }
                $cardDesign['event_logo_image'] = null;
            } elseif ($request->hasFile('card_event_logo_image')) {
                $request->validate([
                    'card_event_logo_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg,gif,bmp|max:8192',
                ]);
                if (!empty($cardDesign['event_logo_image']) && Storage::disk('public')->exists($cardDesign['event_logo_image'])) {
                    Storage::disk('public')->delete($cardDesign['event_logo_image']);
                }
                $eventLogoPath = $request->file('card_event_logo_image')->store('campaigns/logos', 'public');
                $cardDesign['event_logo_image'] = $eventLogoPath;
            }

            $cardDesign['show_logo_border']  = $request->has('show_logo_border');
            $cardDesign['logo_border_width'] = intval($request->input('logo_border_width', $cardDesign['logo_border_width'] ?? 0));
            $cardDesign['show_event_logo']   = $request->has('show_event_logo');
            $cardDesign['event_logo_size']   = intval($request->input('event_logo_size', $cardDesign['event_logo_size'] ?? 64));
            $cardDesign['event_logo_offset_x'] = intval($request->input('event_logo_offset_x', $cardDesign['event_logo_offset_x'] ?? 0));
            $cardDesign['event_logo_offset_y'] = intval($request->input('event_logo_offset_y', $cardDesign['event_logo_offset_y'] ?? 0));

            // 2. Colors & Typography
            $cardDesign['bg_color']          = $request->input('card_bg_color', $cardDesign['bg_color'] ?? '#c98c21');
            $cardDesign['theme_color']       = $request->input('card_theme_color', $cardDesign['theme_color'] ?? '#7f1d1d');
            $cardDesign['text_color']        = $request->input('card_text_color', $cardDesign['text_color'] ?? '#ffffff');
            $cardDesign['plate_bg_color']    = $request->input('plate_bg_color', $cardDesign['plate_bg_color'] ?? '#ecd8b4');
            $cardDesign['card_theme']        = $request->input('card_theme', $cardDesign['card_theme'] ?? 'ochre_gold');
            $cardDesign['font_family']       = $request->input('font_family', $cardDesign['font_family'] ?? 'Hind Siliguri');

            // 2.1 Fine-grained Text Colors
            $cardDesign['anniv_color']       = $request->input('anniv_color', $cardDesign['anniv_color'] ?? '#ffffff');
            $cardDesign['title_color']       = $request->input('title_color', $cardDesign['title_color'] ?? '#ffffff');
            $cardDesign['subtitle_color']    = $request->input('subtitle_color', $cardDesign['subtitle_color'] ?? '#ffffff');
            $cardDesign['name_color']        = $request->input('name_color', $cardDesign['name_color'] ?? '#0f172a');
            $cardDesign['meta_color']        = $request->input('meta_color', $cardDesign['meta_color'] ?? '#334155');
            $cardDesign['quote_color']       = $request->input('quote_color', $cardDesign['quote_color'] ?? '#ffffff');
            $cardDesign['org_color']         = $request->input('org_color', $cardDesign['org_color'] ?? '#ffffff');

            // 2.2 Background Effects & Filters
            $cardDesign['bg_overlay_opacity']= intval($request->input('bg_overlay_opacity', $cardDesign['bg_overlay_opacity'] ?? 0));
            $cardDesign['bg_overlay_color']  = $request->input('bg_overlay_color', $cardDesign['bg_overlay_color'] ?? '#000000');
            $cardDesign['bg_blur']           = intval($request->input('bg_blur', $cardDesign['bg_blur'] ?? 0));
            
            // 3. Header Texts & Logo Sizing
            $cardDesign['show_header']       = false; // উৎসবের ৩-স্তরের মূল শিরোনাম ডিলিট করা হয়েছে
            $cardDesign['show_logo']         = $request->has('show_logo');
            $cardDesign['logo_size']         = intval($request->input('logo_size', $cardDesign['logo_size'] ?? 58));
            $cardDesign['logo_offset_x']     = intval($request->input('logo_offset_x', $cardDesign['logo_offset_x'] ?? 0));
            $cardDesign['logo_offset_y']     = intval($request->input('logo_offset_y', $cardDesign['logo_offset_y'] ?? 0));
            $cardDesign['anniversary_text']  = $request->input('anniversary_text', '');
            $cardDesign['title_text']        = $request->input('title_text', '');
            $cardDesign['subtitle_text']     = $request->input('subtitle_text', '');
            
            // 4. Badge & Card No
            $cardDesign['show_badge']        = false; // আমন্ত্রণ ব্যাজ ও কার্ড নম্বর ডিলিট করা হয়েছে
            $cardDesign['badge_text']        = $request->input('card_badge_text', '');
            
            // 5. Author Photo, Shape & Studio Filters (ফটো এডিটর স্যুট)
            $cardDesign['show_photo']          = $request->has('show_photo');
            $cardDesign['photo_size']          = intval($request->input('photo_size', 82));
            $cardDesign['photo_offset_x']      = intval($request->input('photo_offset_x', $cardDesign['photo_offset_x'] ?? 0));
            $cardDesign['photo_offset_y']      = intval($request->input('photo_offset_y', $cardDesign['photo_offset_y'] ?? 0));
            $cardDesign['photo_scale']         = intval($request->input('photo_scale', $cardDesign['photo_scale'] ?? 100));
            $cardDesign['photo_border_radius'] = $request->input('photo_border_radius', $cardDesign['photo_border_radius'] ?? '50%');
            $cardDesign['photo_border_width']  = intval($request->input('photo_border_width', $cardDesign['photo_border_width'] ?? 3));
            $cardDesign['photo_border_color']  = $request->input('photo_border_color', $cardDesign['photo_border_color'] ?? '#ffffff');
            $cardDesign['photo_shadow']        = $request->input('photo_shadow', $cardDesign['photo_shadow'] ?? 'soft');
            $cardDesign['photo_brightness']    = intval($request->input('photo_brightness', $cardDesign['photo_brightness'] ?? 100));
            $cardDesign['photo_contrast']      = intval($request->input('photo_contrast', $cardDesign['photo_contrast'] ?? 100));
            $cardDesign['photo_grayscale']     = intval($request->input('photo_grayscale', $cardDesign['photo_grayscale'] ?? 0));
            $cardDesign['photo_sepia']         = intval($request->input('photo_sepia', $cardDesign['photo_sepia'] ?? 0));
            $cardDesign['photo_name_gap']      = intval($request->input('photo_name_gap', $cardDesign['photo_name_gap'] ?? 10));
            
            // 6. Name Plate, Typography & Background Shade (নাম, পদবি ও জেলা সাইজ এবং সাদা শেড আলাদা করা)
            $cardDesign['show_name_plate']       = $request->has('show_name_plate');
            $cardDesign['name_font_size']        = intval($request->input('name_font_size', 16));
            $cardDesign['designation_font_size'] = intval($request->input('designation_font_size', $cardDesign['designation_font_size'] ?? 10));
            $cardDesign['location_font_size']    = intval($request->input('location_font_size', $cardDesign['location_font_size'] ?? 9));
            $cardDesign['name_line_height']      = floatval($request->input('name_line_height', $cardDesign['name_line_height'] ?? 1.25));
            $cardDesign['name_spacing']          = intval($request->input('name_spacing', $cardDesign['name_spacing'] ?? 2));
            $cardDesign['plate_padding']         = intval($request->input('plate_padding', $cardDesign['plate_padding'] ?? 14));

            // Nameplate Background Shade & Separation Controls
            $cardDesign['show_plate_bg']         = $request->has('show_plate_bg');
            $cardDesign['plate_bg_opacity']      = intval($request->input('plate_bg_opacity', $cardDesign['plate_bg_opacity'] ?? 100));
            $cardDesign['plate_border_width']    = intval($request->input('plate_border_width', $cardDesign['plate_border_width'] ?? 1));
            $cardDesign['plate_border_color']    = $request->input('plate_border_color', $cardDesign['plate_border_color'] ?? '#d8be92');
            $cardDesign['plate_border_radius']   = intval($request->input('plate_border_radius', $cardDesign['plate_border_radius'] ?? 12));
            $cardDesign['designation_color']     = $request->input('designation_color', $cardDesign['designation_color'] ?? ($cardDesign['meta_color'] ?? '#334155'));
            $cardDesign['location_color']        = $request->input('location_color', $cardDesign['location_color'] ?? ($cardDesign['meta_color'] ?? '#475569'));
            
            // 7. Quotation & Artwork
            $cardDesign['show_quote']        = $request->has('show_quote');
            $cardDesign['quote_text']        = $request->input('quote_text', "সাহিত্য উৎসব ও লিটিলম্যাগমেলায়\nআপনার উপস্থিতি ও অংশগ্রহণ\nআমাদের সম্মানিত করবে ।");
            $cardDesign['show_artwork']      = $request->has('show_artwork');
            
            // 8. Verification QR Code & Attendance Settings (স্বাক্ষর বাদ হয়ে অটোমেটিক ভেরিফাই কিউ আর কোড)
            $cardDesign['show_qr_code']      = $request->has('show_qr_code') || $request->has('show_organizers');
            $cardDesign['show_organizers']   = $cardDesign['show_qr_code'];
            $cardDesign['qr_code_size']      = intval($request->input('qr_code_size', $cardDesign['qr_code_size'] ?? 78));
            $cardDesign['qr_caption_text']   = $request->input('qr_caption_text', $cardDesign['qr_caption_text'] ?? 'উপস্থিতি যাচাই ও নিশ্চিতকরণ QR কোড');
            $cardDesign['qr_subtext']        = $request->input('qr_subtext', $cardDesign['qr_subtext'] ?? 'ক্লিক বা স্ক্যান করলেই অনুষ্ঠানে উপস্থিতি নিশ্চিত হবে');
            
            $cardDesign['org_1_name']        = $request->input('org_1_name', $cardDesign['org_1_name'] ?? 'সাকিল মাসুদ');
            $cardDesign['org_1_role']        = $request->input('org_1_role', $cardDesign['org_1_role'] ?? "সদস্যসচিব, প্রতিষ্ঠাবার্ষিকী আয়োজক কমিটি ২০২৬\nও সাধারণ সম্পাদক, ফিরেদেখা");
            $cardDesign['org_1_phone']       = $request->input('org_1_phone', $cardDesign['org_1_phone'] ?? '০১৭২৬৯৭৬৯৮২');
            
            $cardDesign['org_2_name']        = $request->input('org_2_name', $cardDesign['org_2_name'] ?? 'বাবুল সরকার');
            $cardDesign['org_2_role']        = $request->input('org_2_role', $cardDesign['org_2_role'] ?? "আহ্বায়ক, প্রতিষ্ঠাবার্ষিকী আয়োজক কমিটি ২০২৬\nও সাহিত্য সম্পাদক, ফিরেদেখা");
            $cardDesign['org_2_phone']       = $request->input('org_2_phone', $cardDesign['org_2_phone'] ?? '01763170342');
            
            $cardDesign['org_3_name']        = $request->input('org_3_name', $cardDesign['org_3_name'] ?? 'তাপস মাহমুদ');
            $cardDesign['org_3_role']        = $request->input('org_3_role', $cardDesign['org_3_role'] ?? "সভাপতি,\nফিরেদেখা");
            $cardDesign['org_3_phone']       = $request->input('org_3_phone', $cardDesign['org_3_phone'] ?? '01820-547307');

            // 9. Custom Floating Objects (Stickers, Stamps, Badges, Watermarks)
            if ($request->has('custom_objects_json')) {
                $rawJson = $request->input('custom_objects_json');
                $decoded = json_decode($rawJson, true);
                if (is_array($decoded)) {
                    $cardDesign['custom_objects'] = $decoded;
                }
            }

            $fSettings['card_design'] = $cardDesign;
            $campaign->update(['form_settings' => $fSettings]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'        => true,
                    'card_design'    => $cardDesign,
                    'bg_image_url'   => !empty($cardDesign['bg_image']) ? asset('storage/' . $cardDesign['bg_image']) : null,
                    'logo_image_url'       => !empty($cardDesign['logo_image']) ? asset('storage/' . $cardDesign['logo_image']) : null,
                    'event_logo_image_url' => !empty($cardDesign['event_logo_image']) ? asset('storage/' . $cardDesign['event_logo_image']) : null,
                    'message'              => 'কার্ড ডিজাইন ও স্টুডিও সেটিংস সফলভাবে সংরক্ষিত হয়েছে।',
                ]);
            }

            return back()->with('success', 'Delegate card background design and customizer settings saved successfully.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $msg = implode(' ', \Illuminate\Support\Arr::flatten($ve->errors()));
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors($ve->errors())->withInput();
        } catch (\Throwable $e) {
            \Log::error('Card Customizer Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'সংরক্ষণ ব্যর্থ: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'সংরক্ষণ ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Upload an object/sticker/badge asset for the card customizer studio.
     */
    public function uploadCardObject(Request $request, EventCampaign $campaign)
    {
        try {
            $request->validate([
                'object_file' => 'required|file|mimes:jpeg,png,jpg,webp,svg,gif,bmp|max:12288',
            ]);

            $path = $request->file('object_file')->store('campaigns/objects', 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'path'    => $path,
                'url'     => $url,
                'name'    => $request->file('object_file')->getClientOriginalName(),
                'message' => 'অবজেক্ট সফলভাবে আপলোড হয়েছে।',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => implode(' ', \Illuminate\Support\Arr::flatten($ve->errors())),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'অবজেক্ট আপলোড ব্যর্থ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update 50-mark Viva Assessment Evaluation for scholarship applicant.
     */
    public function updateVivaEvaluation(Request $request, EventRegistration $registration)
    {
        $vivaAttendance = floatval($request->input('viva_attendance', 0));
        $vivaDocs = floatval($request->input('viva_documents', 0));
        $vivaAttire = floatval($request->input('viva_attire', 0));
        $vivaFuture = floatval($request->input('viva_future_plan', 0));
        $vivaReading = floatval($request->input('viva_reading_habit', 0));
        $vivaVolunteer = floatval($request->input('viva_volunteer_exp', 0));
        $vivaIq = floatval($request->input('viva_iq', 0));

        $totalScore = $vivaAttendance + $vivaDocs + $vivaAttire + $vivaFuture + $vivaReading + $vivaVolunteer + $vivaIq;

        $formData = $registration->form_data ?? [];
        $formData['viva_attendance']    = $vivaAttendance;
        $formData['viva_documents']     = $vivaDocs;
        $formData['viva_attire']        = $vivaAttire;
        $formData['viva_future_plan']   = $vivaFuture;
        $formData['viva_reading_habit'] = $vivaReading;
        $formData['viva_volunteer_exp'] = $vivaVolunteer;
        $formData['viva_iq']            = $vivaIq;
        $formData['viva_total']         = $totalScore;

        $isAwarded = $request->boolean('is_awarded', false);
        if ($isAwarded) {
            $formData['is_scholarship_awarded'] = true;
            $formData['scholarship_awarded_at'] = now()->toDateTimeString();
        }

        $registration->update([
            'status'    => $isAwarded ? 'selected' : ($registration->status === 'selected' ? 'selected' : 'attended'),
            'form_data' => $formData,
        ]);

        $msg = "Viva evaluation for #{$registration->registration_number} saved. Total Score: {$totalScore}/50.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'total_score' => $totalScore,
                'is_awarded'  => $isAwarded,
                'status'      => $registration->status,
                'message'     => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Print applicant scholarship/event form or pass.
     */
    public function printRegistration(EventRegistration $registration)
    {
        $registration->load('campaign', 'user');

        $isScholarship = ($registration->campaign->type === 'scholarship' || $registration->campaign->slug === 'jshikkhabritti' || !empty($registration->campaign->form_settings['is_scholarship_form']));
        $viewName = $isScholarship ? 'frontend.events.scholarship_form_print' : 'frontend.events.ticket_print';

        return view($viewName, [
            'registration' => $registration,
            'isPdf'        => false,
        ]);
    }

    /**
     * Download applicant scholarship/event form or pass as PDF.
     */
    public function pdfRegistration(EventRegistration $registration)
    {
        $registration->load('campaign', 'user');

        $isScholarship = ($registration->campaign->type === 'scholarship' || $registration->campaign->slug === 'jshikkhabritti' || !empty($registration->campaign->form_settings['is_scholarship_form']));
        
        if (!$isScholarship) {
            return redirect()->route('event.registration.print', [
                'registrationNumber' => $registration->registration_number,
                'download'           => 1,
            ]);
        }

        $viewName = 'frontend.events.scholarship_form_print';
        $pdf = Pdf::loadView($viewName, [
            'registration' => $registration,
            'isPdf'        => true,
        ]);

        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'sans-serif',
        ]);

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $registration->name);
        $filename = "Scholarship_Form_{$registration->registration_number}_{$safeName}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export participants list to Excel (.xls) or CSV with Division & District Geo hierarchy.
     */
    public function exportCsv(Request $request, EventCampaign $campaign)
    {
        $type = $request->input('type');
        $format = strtolower($request->input('format', ''));
        $targetDivision = $request->input('division');

        // 1. Little Magazine Directory Export
        if ($type === 'littlemag') {
            return $this->exportLittleMag($request, $campaign, $format);
        }

        // 2. Division & District Wise Geo Database Export
        if ($type === 'geo' || $request->filled('division')) {
            return $this->exportGeoDatabase($request, $campaign, $targetDivision, $format ?: 'excel');
        }

        // 3. Scholarship applications: every answer needed to review and pay (default for scholarship campaigns)
        if ($type === 'scholarship' || ($campaign->type === 'scholarship' && !$type)) {
            return $this->exportScholarship($campaign);
        }

        // 4. Standard / General Participants Export
        $filename = "participants_{$campaign->slug}_" . date('Y-m-d') . ".csv";
        $regQuery = $campaign->registrations();

        if ($request->input('sort') === 'alpha' || $request->input('sort') === 'name') {
            $regQuery->orderBy('name', 'asc');
        } else {
            $regQuery->latest();
        }

        if ($request->filled('district')) {
            $rawDst = trim($request->district);
            $canonicalDst = BangladeshGeo::normalizeDistrict($rawDst) ?: $rawDst;
            $dstVariants = BangladeshGeo::getVariants($canonicalDst);

            $regQuery->where(function ($q) use ($dstVariants) {
                $q->whereIn('district', $dstVariants)
                  ->orWhere(function ($sq) use ($dstVariants) {
                      foreach ($dstVariants as $v) {
                          $sq->orWhere('district', 'like', "%{$v}%")
                             ->orWhere('form_data->district', $v)
                             ->orWhere('form_data->perm_district', $v)
                             ->orWhere('form_data->district', 'like', "%{$v}%")
                             ->orWhere('form_data->perm_district', 'like', "%{$v}%");
                      }
                  });
            });
        }

        $registrations = $regQuery->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($registrations) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, ['Reg Number', 'Name', 'Phone', 'Email', 'Division', 'District', 'Thana', 'Institution/Genre', 'Magazine Name', 'Amount Paid', 'Trx ID', 'Payment Status', 'Status', 'Registered At']);

            foreach ($registrations as $r) {
                fputcsv($handle, [
                    $r->registration_number,
                    $r->name,
                    $r->phone,
                    $r->email,
                    $r->resolved_division,
                    $r->resolved_district,
                    $r->resolved_thana ?: 'N/A',
                    $r->institution_or_org ?: ($r->form_data['author_category'] ?? ''),
                    $r->magazine_name ?: '',
                    $r->amount_paid,
                    $r->transaction_id,
                    $r->payment_status,
                    $r->status,
                    $r->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Scholarship applications as CSV: identity, family, academic, payment account and the statement.
     */
    protected function exportScholarship(EventCampaign $campaign)
    {
        $filename = "scholarship_applications_{$campaign->slug}_" . date('Y-m-d') . ".csv";
        $registrations = $campaign->registrations()->orderBy('id')->get();

        // Cells that start with = + - @ would run as formulas in Excel; numbers keep their leading zeros as text.
        $safe = function ($v) {
            $v = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : (string) $v;
            return preg_match('/^[=+\-@\r]/', $v) ? "'" . $v : $v;
        };
        $text = fn ($v) => ((string) $v) === '' ? '' : "\t" . $v;

        $columns = [
            'Application No' => fn ($r, $d) => $r->registration_number,
            'Status' => fn ($r, $d) => $r->status,
            'Name' => fn ($r, $d) => $r->name,
            'Mobile' => fn ($r, $d) => $text($r->phone),
            'Email' => fn ($r, $d) => $d('email') ?: $r->email,
            'NID / Birth Reg' => fn ($r, $d) => $text($d('national_id')),
            'Gender' => fn ($r, $d) => $d('gender'),
            'Birth Date' => fn ($r, $d) => $d('birth_date'),
            'Father' => fn ($r, $d) => $d('father_name'),
            'Father Status' => fn ($r, $d) => $d('father_status'),
            'Father Occupation' => fn ($r, $d) => $d('father_occupation'),
            'Father Income' => fn ($r, $d) => $d('father_income'),
            'Father Mobile' => fn ($r, $d) => $text($d('father_phone')),
            'Mother' => fn ($r, $d) => $d('mother_name'),
            'Mother Status' => fn ($r, $d) => $d('mother_status'),
            'Mother Occupation' => fn ($r, $d) => $d('mother_occupation'),
            'Mother Income' => fn ($r, $d) => $d('mother_income'),
            'Mother Mobile' => fn ($r, $d) => $text($d('mother_phone')),
            'Guardian' => fn ($r, $d) => $d('guardian_name'),
            'Guardian Relation' => fn ($r, $d) => $d('guardian_relation'),
            'Guardian Income' => fn ($r, $d) => $d('guardian_income'),
            'Guardian Contact' => fn ($r, $d) => $text($d('guardian_phone')),
            'Annual Family Income' => fn ($r, $d) => $d('annual_income'),
            'Family Members' => fn ($r, $d) => $d('family_count'),
            'Earning Members' => fn ($r, $d) => $d('earning_count'),
            'Orphan Status' => fn ($r, $d) => $d('orphan_status'),
            'Disability' => fn ($r, $d) => $d('disability'),
            'Disability Category' => fn ($r, $d) => $d('disability_type'),
            'Disability Details' => fn ($r, $d) => $d('disability_details'),
            'Degree Applied' => fn ($r, $d) => $d('scholarship_degree'),
            'Current Year' => fn ($r, $d) => $d('current_year'),
            'Institute' => fn ($r, $d) => $d('college_name') ?: $r->institution_or_org,
            'Group' => fn ($r, $d) => $d('group'),
            'Subject' => fn ($r, $d) => $d('assigned_subject'),
            'Admission Roll' => fn ($r, $d) => $d('admission_roll'),
            'Merit Position' => fn ($r, $d) => $d('merit_position'),
            'Admission Fee' => fn ($r, $d) => $d('admission_fee'),
            'Monthly Fee' => fn ($r, $d) => $d('monthly_fee'),
            'Hostel Cost' => fn ($r, $d) => $d('hostel_cost'),
            'SSC (year / GPA)' => fn ($r, $d) => trim($d('ssc_year') . ' / ' . $d('ssc_gpa'), ' /'),
            'HSC (year / GPA)' => fn ($r, $d) => trim($d('hsc_year') . ' / ' . $d('hsc_gpa'), ' /'),
            'Honours (year / CGPA)' => fn ($r, $d) => trim($d('grad_year') . ' / ' . $d('grad_cgpa'), ' /'),
            'Masters (year / CGPA)' => fn ($r, $d) => trim($d('other_year') . ' / ' . $d('other_cgpa'), ' /'),
            'Received Joyee Before' => fn ($r, $d) => $d('prev_joyee'),
            'Previous Joyee Details' => fn ($r, $d) => collect($r->form_data['prev_scholarships'] ?? [])
                ->filter(fn ($x) => is_array($x) && array_filter($x))->map(fn ($x) => ($x['year'] ?? '') . ' x' . ($x['times'] ?? '') . ' = ' . ($x['amount'] ?? ''))->implode('; '),
            'Other Scholarship' => fn ($r, $d) => trim($d('other_scholarship') . ' ' . $d('other_scholarship_details')),
            'Books Read' => fn ($r, $d) => $d('books_read_count'),
            'Reading Habit' => fn ($r, $d) => $d('reading_habit'),
            'Book Names' => fn ($r, $d) => str_replace(["\r\n", "\n"], '; ', $d('books_read_names')),
            'Statement' => fn ($r, $d) => $d('scholarship_reason'),
            'Pay Method' => fn ($r, $d) => $d('pay_method'),
            'Account Owner' => fn ($r, $d) => $d('pay_account_owner'),
            'Account Holder' => fn ($r, $d) => $d('pay_account_name'),
            'Account / Wallet No' => fn ($r, $d) => $text($d('pay_account_no')),
            'Bank' => fn ($r, $d) => $d('pay_bank_name'),
            'Branch' => fn ($r, $d) => $d('pay_bank_branch'),
            'Permanent Address' => fn ($r, $d) => $d('permanent_address') ?: $r->address,
            'Present Address' => fn ($r, $d) => $d('present_address'),
            'Viva Marks' => fn ($r, $d) => $d('viva_total'),
            'Awarded' => fn ($r, $d) => ($r->status === 'selected' || !empty($r->form_data['is_scholarship_awarded'])) ? 'Yes' : '',
            'Submitted At' => fn ($r, $d) => $r->created_at->format('Y-m-d H:i:s'),
        ];

        $callback = function () use ($registrations, $columns, $safe) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, array_keys($columns));
            foreach ($registrations as $r) {
                $d = fn ($k) => trim((string) (is_array($r->form_data[$k] ?? null) ? json_encode($r->form_data[$k], JSON_UNESCAPED_UNICODE) : ($r->form_data[$k] ?? '')));
                fputcsv($handle, array_map(fn ($c) => $safe($c($r, $d)), $columns));
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export Little Magazine Editors Directory to CSV or Excel.
     */
    protected function exportLittleMag(Request $request, EventCampaign $campaign, string $format = 'csv')
    {
        $filename = "littlemag_editors_{$campaign->slug}_" . date('Y-m-d') . ".csv";
        $allRegs = $campaign->registrations()->latest()->get();
        $registrations = $allRegs->filter(fn($r) => $r->isLittleMagEditor())->sort(function ($a, $b) {
            $magA = $a->magazine_name ?: $a->name;
            $magB = $b->magazine_name ?: $b->name;
            return strcmp($magA, $magB);
        })->values();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($registrations) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, ['Reg Number', 'Magazine Name (পত্রিকা)', 'Editor Name (সম্পাদক)', 'Issue Count (সংখ্যা)', 'Division (বিভাগ)', 'District (জেলা)', 'Thana (থানা)', 'Phone (মোবাইল)', 'Email', 'Status', 'Registered At']);

            foreach ($registrations as $r) {
                fputcsv($handle, [
                    $r->registration_number,
                    $r->magazine_name ?: 'N/A',
                    $r->name,
                    $r->magazine_issue_count ?: 'N/A',
                    $r->resolved_division,
                    $r->resolved_district,
                    $r->resolved_thana ?: 'N/A',
                    $r->phone,
                    $r->email,
                    $r->status,
                    $r->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export participants arranged by Division, District, and Alphabetical Name order.
     */
    protected function exportGeoDatabase(Request $request, EventCampaign $campaign, ?string $targetDivision = null, string $format = 'excel')
    {
        $allCampaignRegistrations = $campaign->registrations()->with('user')->get();

        // 1. Filter by specific division if requested
        if (!empty($targetDivision)) {
            $rawDiv = trim($targetDivision);
            $canonicalDiv = BangladeshGeo::normalizeDivision($rawDiv) ?: $rawDiv;
            $divVariants = BangladeshGeo::getVariants($canonicalDiv);

            $divDistricts = BangladeshGeo::DIVISIONS[$canonicalDiv] ?? [];
            $allDistVariants = [];
            foreach ($divDistricts as $dName) {
                $allDistVariants = array_merge($allDistVariants, BangladeshGeo::getVariants($dName));
            }
            $allDistVariants = array_values(array_unique(array_filter($allDistVariants)));

            $allCampaignRegistrations = $allCampaignRegistrations->filter(function ($reg) use ($canonicalDiv, $divVariants, $allDistVariants) {
                $rDiv = $reg->resolved_division;
                if ($rDiv === $canonicalDiv || in_array($rDiv, $divVariants, true)) {
                    return true;
                }
                $rDist = $reg->resolved_district;
                if (in_array($rDist, $allDistVariants, true)) {
                    return true;
                }
                return false;
            });
        }

        // 2. Filter by specific district if requested
        if ($request->filled('district')) {
            $rawDst = trim($request->district);
            $canonicalDst = BangladeshGeo::normalizeDistrict($rawDst) ?: $rawDst;
            $dstVariants = BangladeshGeo::getVariants($canonicalDst);

            $allCampaignRegistrations = $allCampaignRegistrations->filter(function ($reg) use ($canonicalDst, $dstVariants) {
                $rDist = $reg->resolved_district;
                return $rDist === $canonicalDst || in_array($rDist, $dstVariants, true);
            });
        }

        // 3. Little Magazine filter if requested
        if ($request->boolean('is_littlemag')) {
            $allCampaignRegistrations = $allCampaignRegistrations->filter(fn($r) => $r->isLittleMagEditor());
        }

        // 4. Build Division -> District -> Participants structure
        $geoDatabase = [];
        foreach ($allCampaignRegistrations as $reg) {
            $div = $reg->resolved_division;
            $dist = $reg->resolved_district;

            if (!isset($geoDatabase[$div])) {
                $geoDatabase[$div] = [];
            }
            if (!isset($geoDatabase[$div][$dist])) {
                $geoDatabase[$div][$dist] = collect();
            }
            $geoDatabase[$div][$dist]->push($reg);
        }

        // If target division was requested but had no registrations, create empty placeholder
        if (!empty($targetDivision) && empty($geoDatabase)) {
            $canonicalDiv = BangladeshGeo::normalizeDivision($targetDivision) ?: $targetDivision;
            $geoDatabase[$canonicalDiv] = [];
        }

        // Sort Divisions alphabetically (Bengali Unicode collation)
        uksort($geoDatabase, function ($a, $b) {
            if ($a === 'অনির্ধারিত বিভাগ') return 1;
            if ($b === 'অনির্ধারিত বিভাগ') return -1;
            return strcmp($a, $b);
        });

        // Within each Division, sort Districts alphabetically, and Participants alphabetically by name
        foreach ($geoDatabase as $divName => $districts) {
            uksort($geoDatabase[$divName], function ($a, $b) {
                if ($a === 'অনির্ধারিত জেলা') return 1;
                if ($b === 'অনির্ধারিত জেলা') return -1;
                return strcmp($a, $b);
            });

            foreach ($geoDatabase[$divName] as $dName => $participants) {
                $geoDatabase[$divName][$dName] = $participants->sort(function ($a, $b) {
                    return strcmp(trim($a->name), trim($b->name));
                })->values();
            }
        }

        if ($format === 'csv') {
            return $this->exportGeoCsv($campaign, $geoDatabase, $targetDivision);
        }

        return $this->exportGeoExcel($campaign, $geoDatabase, $targetDivision);
    }

    /**
     * Generate structured Microsoft Excel (.xls XML Spreadsheet 2003) workbook with Division sheets, District headers, and alphabetical participants.
     */
    protected function exportGeoExcel(EventCampaign $campaign, array $geoDatabase, ?string $targetDivision = null)
    {
        $campaignSlug = Str::slug($campaign->slug ?: 'campaign');
        $divisionSlug = $targetDivision ? Str::slug($targetDivision) : 'all_divisions';
        $filename = "geo_participants_{$campaignSlug}_{$divisionSlug}_" . date('Y-m-d') . ".xls";

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

        $xml .= ' <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">' . "\n";
        $xml .= '  <Author>ideaabd</Author>' . "\n";
        $xml .= '  <Title>' . $this->xmlSafe($campaign->title) . '</Title>' . "\n";
        $xml .= '  <Created>' . date('Y-m-d\TH:i:s\Z') . '</Created>' . "\n";
        $xml .= '  <Company>ideaabd</Company>' . "\n";
        $xml .= ' </DocumentProperties>' . "\n";

        $xml .= ' <Styles>' . "\n";
        $xml .= '  <Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Segoe UI, Kalpurush, Arial" ss:Size="10" ss:Color="#0F172A"/></Style>' . "\n";
        $xml .= '  <Style ss:ID="TitleBanner"><Alignment ss:Horizontal="Left" ss:Vertical="Center"/><Font ss:FontName="Segoe UI, Kalpurush, Arial" ss:Size="13" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#0F766E" ss:Pattern="Solid"/></Style>' . "\n";
        $xml .= '  <Style ss:ID="SubtitleBanner"><Alignment ss:Horizontal="Left" ss:Vertical="Center"/><Font ss:FontName="Segoe UI, Kalpurush, Arial" ss:Size="9.5" ss:Color="#E2E8F0"/><Interior ss:Color="#115E59" ss:Pattern="Solid"/></Style>' . "\n";
        $xml .= '  <Style ss:ID="DistrictBanner"><Alignment ss:Horizontal="Left" ss:Vertical="Center"/><Font ss:FontName="Segoe UI, Kalpurush, Arial" ss:Size="11" ss:Bold="1" ss:Color="#14532D"/><Interior ss:Color="#DCFCE7" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#86EFAC"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#86EFAC"/></Borders></Style>' . "\n";
        $xml .= '  <Style ss:ID="TableHeader"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Segoe UI, Kalpurush, Arial" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#1E293B" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#475569"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#475569"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#475569"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#475569"/></Borders></Style>' . "\n";
        $xml .= '  <Style ss:ID="CellLeft"><Alignment ss:Horizontal="Left" ss:Vertical="Center"/><Font ss:FontName="Segoe UI, Kalpurush, Arial" ss:Size="9.5"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/></Borders></Style>' . "\n";
        $xml .= '  <Style ss:ID="CellCenter"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Segoe UI, Kalpurush, Arial" ss:Size="9.5"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/></Borders></Style>' . "\n";
        $xml .= '  <Style ss:ID="DistrictSummary"><Alignment ss:Horizontal="Right" ss:Vertical="Center"/><Font ss:FontName="Segoe UI, Kalpurush, Arial" ss:Size="10" ss:Bold="1" ss:Color="#065F46"/><Interior ss:Color="#F0FDF4" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#86EFAC"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#86EFAC"/></Borders></Style>' . "\n";
        $xml .= '  <Style ss:ID="DivisionSummary"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Segoe UI, Kalpurush, Arial" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#0F766E" ss:Pattern="Solid"/></Style>' . "\n";
        $xml .= ' </Styles>' . "\n";

        // Generate a Worksheet for each division
        foreach ($geoDatabase as $divName => $districts) {
            $divTotal = 0;
            foreach ($districts as $participants) {
                $divTotal += $participants->count();
            }
            $cleanName = preg_replace('/[\\\\\\/?*[\\]:]/', '', $divName . ($divName !== 'অনির্ধারিত বিভাগ' ? ' বিভাগ' : ''));
            $sheetTitle = mb_substr($cleanName, 0, 30);

            $xml .= ' <Worksheet ss:Name="' . $this->xmlSafe($sheetTitle) . '">' . "\n";
            $xml .= '  <Table>' . "\n";
            $xml .= '   <Column ss:Width="45"/>' . "\n";   // SL
            $xml .= '   <Column ss:Width="165"/>' . "\n";  // Name
            $xml .= '   <Column ss:Width="150"/>' . "\n";  // Category / Magazine
            $xml .= '   <Column ss:Width="95"/>' . "\n";   // District
            $xml .= '   <Column ss:Width="105"/>' . "\n";  // Thana
            $xml .= '   <Column ss:Width="180"/>' . "\n";  // Address
            $xml .= '   <Column ss:Width="105"/>' . "\n";  // Phone
            $xml .= '   <Column ss:Width="150"/>' . "\n";  // Email
            $xml .= '   <Column ss:Width="95"/>' . "\n";   // Reg No
            $xml .= '   <Column ss:Width="85"/>' . "\n";   // Payment
            $xml .= '   <Column ss:Width="80"/>' . "\n";   // Status
            $xml .= '   <Column ss:Width="120"/>' . "\n";  // Registered At

            // Division Title Row
            $xml .= '   <Row ss:Height="28">' . "\n";
            $xml .= '    <Cell ss:MergeAcross="11" ss:StyleID="TitleBanner"><Data ss:Type="String">' . $this->xmlSafe($divName . ($divName !== 'অনির্ধারিত বিভাগ' ? ' বিভাগ' : '') . ' — অংশগ্রহণকারী ডাটাবেজ (মোট: ' . $divTotal . ' জন)') . '</Data></Cell>' . "\n";
            $xml .= '   </Row>' . "\n";

            // Subtitle Row
            $xml .= '   <Row ss:Height="20">' . "\n";
            $xml .= '    <Cell ss:MergeAcross="11" ss:StyleID="SubtitleBanner"><Data ss:Type="String">' . $this->xmlSafe('ইভেন্ট: ' . $campaign->title . ' | জেলা অনুসারে এবং নামের বর্ণাক্রমে (A-Z / ক-হ) সাজানো | ডাউনলোডের তারিখ: ' . date('d M, Y h:i A')) . '</Data></Cell>' . "\n";
            $xml .= '   </Row>' . "\n";

            if (empty($districts)) {
                $xml .= '   <Row ss:Height="24">' . "\n";
                $xml .= '    <Cell ss:MergeAcross="11" ss:StyleID="CellCenter"><Data ss:Type="String">এই বিভাগে এখনও কোনো অংশগ্রহণকারী নিবন্ধন করেনি।</Data></Cell>' . "\n";
                $xml .= '   </Row>' . "\n";
            }

            foreach ($districts as $distName => $participants) {
                $distCount = $participants->count();

                // District Banner Row
                $xml .= '   <Row ss:Height="24">' . "\n";
                $xml .= '    <Cell ss:MergeAcross="11" ss:StyleID="DistrictBanner"><Data ss:Type="String">' . $this->xmlSafe('▶ জেলা: ' . $distName . ($distName !== 'অনির্ধারিত জেলা' ? ' জেলা' : '') . ' (মোট অংশগ্রহণকারী: ' . $distCount . ' জন)') . '</Data></Cell>' . "\n";
                $xml .= '   </Row>' . "\n";

                // Column Header Row
                $xml .= '   <Row ss:Height="22">' . "\n";
                $headers = ['ক্রঃ', 'নাম (বর্ণানুক্রমে)', 'ক্যাটাগরি / সাহিত্য শাখা / পত্রিকা', 'জেলা', 'উপজেলা / থানা', 'ঠিকানা / গ্রাম', 'মোবাইল নম্বর', 'ইমেইল', 'রেজিস্ট্রেশন নং', 'পেমেন্ট অবস্থা', 'স্ট্যাটাস', 'নিবন্ধন তারিখ'];
                foreach ($headers as $h) {
                    $xml .= '    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">' . $this->xmlSafe($h) . '</Data></Cell>' . "\n";
                }
                $xml .= '   </Row>' . "\n";

                // Data Rows (Sorted Alphabetically by Name)
                $sl = 1;
                foreach ($participants as $r) {
                    $cat = trim(($r->institution_or_org ?: ($r->form_data['author_category'] ?? '')) . ($r->magazine_name ? ' [পত্রিকা: ' . $r->magazine_name . ']' : ''));
                    $thana = $r->resolved_thana ?: ($r->thana ?: '—');
                    $addr = $r->address ?: ($r->form_data['perm_address'] ?? ($r->form_data['present_address'] ?? '—'));
                    $dateStr = $r->created_at ? $r->created_at->format('d/m/Y h:i A') : '—';

                    $xml .= '   <Row ss:Height="20">' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="Number">' . $sl++ . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellLeft"><Data ss:Type="String">' . $this->xmlSafe($r->name) . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellLeft"><Data ss:Type="String">' . $this->xmlSafe($cat ?: '—') . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $this->xmlSafe($r->resolved_district) . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $this->xmlSafe($thana) . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellLeft"><Data ss:Type="String">' . $this->xmlSafe($addr) . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $this->xmlSafe($r->phone) . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellLeft"><Data ss:Type="String">' . $this->xmlSafe($r->email ?: '—') . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $this->xmlSafe($r->registration_number) . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $this->xmlSafe(ucfirst($r->payment_status)) . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $this->xmlSafe(ucfirst($r->status)) . '</Data></Cell>' . "\n";
                    $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $this->xmlSafe($dateStr) . '</Data></Cell>' . "\n";
                    $xml .= '   </Row>' . "\n";
                }

                // District Summary Row
                $xml .= '   <Row ss:Height="20">' . "\n";
                $xml .= '    <Cell ss:MergeAcross="11" ss:StyleID="DistrictSummary"><Data ss:Type="String">' . $this->xmlSafe($distName . ' জেলার মোট: ' . $distCount . ' জন') . '</Data></Cell>' . "\n";
                $xml .= '   </Row>' . "\n";

                // Spacer Row
                $xml .= '   <Row ss:Height="10"><Cell ss:MergeAcross="11"><Data ss:Type="String"></Data></Cell></Row>' . "\n";
            }

            // Division Final Summary Row
            $xml .= '   <Row ss:Height="24">' . "\n";
            $xml .= '    <Cell ss:MergeAcross="11" ss:StyleID="DivisionSummary"><Data ss:Type="String">' . $this->xmlSafe('★ ' . $divName . ' বিভাগের সর্বমোট অংশগ্রহণকারী: ' . $divTotal . ' জন') . '</Data></Cell>' . "\n";
            $xml .= '   </Row>' . "\n";

            $xml .= '  </Table>' . "\n";
            $xml .= ' </Worksheet>' . "\n";
        }

        $xml .= '</Workbook>';

        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0, no-cache, must-revalidate',
            'Pragma'              => 'public',
        ];

        return response($xml, 200, $headers);
    }

    /**
     * Generate structured CSV export grouped by Division & District with alphabetical name sorting.
     */
    protected function exportGeoCsv(EventCampaign $campaign, array $geoDatabase, ?string $targetDivision = null)
    {
        $campaignSlug = Str::slug($campaign->slug ?: 'campaign');
        $divisionSlug = $targetDivision ? Str::slug($targetDivision) : 'all_divisions';
        $filename = "geo_participants_{$campaignSlug}_{$divisionSlug}_" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($campaign, $geoDatabase) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [$campaign->title . ' — বিভাগ ও জেলা ভিত্তিক অংশগ্রহণকারী ডাটাবেজ']);
            fputcsv($handle, ['তৈরির তারিখ: ' . date('d M, Y h:i A') . ' | জেলা অনুসারে এবং নামের বর্ণাক্রমে (A-Z / ক-হ) সাজানো']);
            fputcsv($handle, []);

            foreach ($geoDatabase as $divName => $districts) {
                $divTotal = 0;
                foreach ($districts as $pList) {
                    $divTotal += $pList->count();
                }

                fputcsv($handle, ["================== {$divName} বিভাগ (মোট: {$divTotal} জন) =================="]);
                fputcsv($handle, []);

                foreach ($districts as $distName => $participants) {
                    $distCount = $participants->count();
                    fputcsv($handle, ["--- জেলা: {$distName} (মোট: {$distCount} জন) ---"]);
                    fputcsv($handle, [
                        'ক্রঃ নং',
                        'নাম (বর্ণানুক্রমে)',
                        'ক্যাটাগরি / সাহিত্য শাখা / পত্রিকা',
                        'বিভাগ',
                        'জেলা',
                        'উপজেলা / থানা',
                        'ঠিকানা / গ্রাম',
                        'মোবাইল নম্বর',
                        'ইমেইল',
                        'রেজিস্ট্রেশন নং',
                        'পেমেন্ট অবস্থা',
                        'অনুমোদন অবস্থা',
                        'নিবন্ধনের তারিখ',
                    ]);

                    $sl = 1;
                    foreach ($participants as $r) {
                        $cat = trim(($r->institution_or_org ?: ($r->form_data['author_category'] ?? '')) . ($r->magazine_name ? ' [পত্রিকা: ' . $r->magazine_name . ']' : ''));
                        $thana = $r->resolved_thana ?: ($r->thana ?: '—');
                        $addr = $r->address ?: ($r->form_data['perm_address'] ?? ($r->form_data['present_address'] ?? '—'));

                        fputcsv($handle, [
                            $sl++,
                            $r->name,
                            $cat ?: '—',
                            $r->resolved_division,
                            $r->resolved_district,
                            $thana,
                            $addr,
                            $r->phone,
                            $r->email ?: '—',
                            $r->registration_number,
                            ucfirst($r->payment_status),
                            ucfirst($r->status),
                            $r->created_at ? $r->created_at->format('Y-m-d H:i:s') : '—',
                        ]);
                    }

                    fputcsv($handle, ["{$distName} জেলার মোট অংশগ্রহণকারী: {$distCount} জন"]);
                    fputcsv($handle, []);
                }

                fputcsv($handle, ["★ {$divName} বিভাগের সর্বমোট অংশগ্রহণকারী: {$divTotal} জন"]);
                fputcsv($handle, []);
                fputcsv($handle, []);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * XML string safe escaper for XML Spreadsheet.
     */
    protected function xmlSafe(?string $str): string
    {
        if ($str === null || $str === '') {
            return '';
        }
        return htmlspecialchars((string) $str, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * Comprehensive Form & Logo Customizer Studio update handler.
     */
    public function updateFormCustomizer(Request $request, EventCampaign $campaign)
    {
        $formSettings = $campaign->form_settings ?: [];

        // 1. Remove Logo Handling
        if ($request->boolean('remove_logo')) {
            if (!empty($formSettings['logo_image']) && Storage::disk('public')->exists($formSettings['logo_image'])) {
                Storage::disk('public')->delete($formSettings['logo_image']);
            }
            unset($formSettings['logo_image']);
        } elseif ($request->hasFile('logo_image')) {
            $request->validate([
                'logo_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg,gif,bmp|max:8192',
            ]);
            if (!empty($formSettings['logo_image']) && Storage::disk('public')->exists($formSettings['logo_image'])) {
                Storage::disk('public')->delete($formSettings['logo_image']);
            }
            $path = $request->file('logo_image')->store('campaigns/logos', 'public');
            $formSettings['logo_image'] = $path;
        }

        // 2. Logo Dimensions (Width & Height), Shape, Border, Position, Fit & Background
        if ($request->filled('logo_width')) {
            $formSettings['logo_width'] = max(30, min(350, intval($request->logo_width)));
        }
        if ($request->filled('logo_height')) {
            $formSettings['logo_height'] = max(30, min(250, intval($request->logo_height)));
        }
        if ($request->filled('logo_size')) {
            $formSettings['logo_size'] = max(30, min(350, intval($request->logo_size)));
            if (empty($formSettings['logo_width'])) $formSettings['logo_width'] = $formSettings['logo_size'];
            if (empty($formSettings['logo_height'])) $formSettings['logo_height'] = $formSettings['logo_size'];
        }
        if (!empty($formSettings['logo_width']) && empty($formSettings['logo_size'])) {
            $formSettings['logo_size'] = $formSettings['logo_width'];
        }
        if ($request->has('logo_fit')) {
            $formSettings['logo_fit'] = in_array($request->logo_fit, ['contain', 'cover', 'fill', 'scale-down'], true) ? $request->logo_fit : 'contain';
        }
        if ($request->has('logo_shape')) {
            $formSettings['logo_shape'] = in_array($request->logo_shape, ['default', 'circle', 'rounded', 'square'], true) ? $request->logo_shape : 'default';
        }
        if ($request->has('logo_border_width')) {
            $formSettings['logo_border_width'] = max(0, min(10, intval($request->logo_border_width)));
        }
        if ($request->filled('logo_border_color')) {
            $formSettings['logo_border_color'] = trim($request->logo_border_color);
        }
        if ($request->filled('emblem_icon')) {
            $formSettings['emblem_icon'] = trim($request->emblem_icon);
        }
        if ($request->has('logo_offset_x')) {
            $formSettings['logo_offset_x'] = max(-150, min(150, intval($request->logo_offset_x)));
        }
        if ($request->has('logo_offset_y')) {
            $formSettings['logo_offset_y'] = max(-150, min(150, intval($request->logo_offset_y)));
        }
        if ($request->has('logo_bg')) {
            $formSettings['logo_bg'] = in_array($request->logo_bg, ['transparent', 'white', '#ffffff', '#000000'], true) ? $request->logo_bg : 'transparent';
        }

        // 3. Form Header and Letterhead Text Fields
        if ($request->has('form_venue')) {
            $formSettings['form_venue'] = trim($request->input('form_venue'));
        }
        if ($request->has('form_date')) {
            $formSettings['form_date'] = trim($request->input('form_date'));
        }
        if ($request->has('form_org')) {
            $formSettings['form_org'] = trim($request->input('form_org'));
        }
        if ($request->has('form_copy_tag')) {
            $formSettings['form_copy_tag'] = trim($request->input('form_copy_tag'));
        }
        if ($request->has('form_subhead')) {
            $formSettings['form_subhead'] = trim($request->input('form_subhead'));
        }

        // 4. Color Palette & Typography
        if ($request->filled('theme_color')) {
            $formSettings['theme_color'] = trim($request->input('theme_color'));
            $campaign->theme_color = $formSettings['theme_color'];
        }
        if ($request->filled('form_border_color')) {
            $formSettings['form_border_color'] = trim($request->input('form_border_color'));
        }
        if ($request->filled('form_bg_label')) {
            $formSettings['form_bg_label'] = trim($request->input('form_bg_label'));
        }
        if ($request->filled('font_family')) {
            $formSettings['font_family'] = trim($request->input('font_family'));
        }

        // 5. Notice / Announcement Banner
        $formSettings['form_notice_active'] = $request->boolean('form_notice_active');
        if ($request->has('form_notice_text')) {
            $formSettings['form_notice_text'] = trim($request->input('form_notice_text'));
        }
        if ($request->filled('form_notice_type')) {
            $formSettings['form_notice_type'] = in_array($request->form_notice_type, ['info', 'warning', 'danger', 'success'], true) ? $request->form_notice_type : 'info';
        }

        // 6. Categories Management
        if ($request->has('categories')) {
            $cats = $request->input('categories');
            if (is_string($cats)) {
                $decoded = json_decode($cats, true);
                $cats = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode("\n", $cats)));
            }
            if (is_array($cats)) {
                $cleanCats = [];
                foreach ($cats as $cat) {
                    $cat = trim(is_array($cat) ? ($cat['name'] ?? '') : $cat);
                    if (!empty($cat) && !in_array($cat, $cleanCats, true)) {
                        $cleanCats[] = $cat;
                    }
                }
                $formSettings['categories'] = $cleanCats;
            }
        }

        // 7. Field Toggles
        if ($request->has('photo_required')) {
            $formSettings['photo_required'] = in_array($request->photo_required, ['required', 'optional', 'hidden'], true) ? $request->photo_required : 'required';
        }
        if ($request->has('enable_intl_address')) {
            $formSettings['enable_intl_address'] = $request->boolean('enable_intl_address');
        }
        if ($request->has('enable_notable_books')) {
            $formSettings['enable_notable_books'] = $request->boolean('enable_notable_books');
        }
        if ($request->has('enable_magazine')) {
            $formSettings['enable_magazine'] = $request->boolean('enable_magazine');
        }
        if ($request->has('enable_literary_info')) {
            $formSettings['enable_literary_info'] = $request->boolean('enable_literary_info');
        }
        if ($request->has('enable_signature')) {
            $formSettings['enable_signature'] = $request->boolean('enable_signature');
        }
        if ($request->has('requires_approval')) {
            $formSettings['requires_approval'] = $request->boolean('requires_approval');
        }

        // 8. Direct Campaign Attributes Update
        if ($request->filled('title')) {
            $campaign->title = trim($request->input('title'));
        }
        if ($request->has('badge_text')) {
            $campaign->badge_text = trim($request->input('badge_text'));
        }
        if ($request->has('short_description')) {
            $campaign->short_description = trim($request->input('short_description'));
        }
        if ($request->has('success_message')) {
            $campaign->success_message = trim($request->input('success_message'));
        }
        if ($request->has('is_active')) {
            $campaign->is_active = $request->boolean('is_active');
        }
        if ($request->has('has_fee_or_donation')) {
            $campaign->has_fee_or_donation = $request->boolean('has_fee_or_donation');
        }
        if ($request->has('fee_amount')) {
            $campaign->fee_amount = floatval($request->input('fee_amount', 0));
        }
        if ($request->has('payment_instructions')) {
            $campaign->payment_instructions = trim($request->input('payment_instructions'));
        }
        if ($request->filled('max_participants')) {
            $campaign->max_participants = intval($request->input('max_participants')) > 0 ? intval($request->input('max_participants')) : null;
        } elseif ($request->has('max_participants')) {
            $campaign->max_participants = null;
        }
        if ($request->filled('ends_at')) {
            $campaign->ends_at = $request->input('ends_at');
        } elseif ($request->has('ends_at')) {
            $campaign->ends_at = null;
        }

        $campaign->form_settings = $formSettings;
        $campaign->save();

        $logoUrl = !empty($formSettings['logo_image']) ? '/storage/' . ltrim(preg_replace('#^(public/|storage/)+#', '', $formSettings['logo_image']), '/') : null;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'       => true,
                'message'       => 'রেজিস্ট্রেশন ফরম ও লোগো কাস্টমাইজেশন সফলভাবে সংরক্ষিত হয়েছে।',
                'logo_url'      => $logoUrl,
                'logo_size'     => $formSettings['logo_size'] ?? 70,
                'title'         => $campaign->title,
                'badge_text'    => $campaign->badge_text,
                'form_venue'    => $formSettings['form_venue'] ?? '',
                'form_date'     => $formSettings['form_date'] ?? '',
                'form_org'      => $formSettings['form_org'] ?? '',
                'form_copy_tag' => $formSettings['form_copy_tag'] ?? '',
                'theme_color'   => $campaign->theme_color,
                'form_settings' => $campaign->form_settings,
            ]);
        }

        return back()->with('success', 'রেজিস্ট্রেশন ফরম ও লোগো কাস্টমাইজেশন সফলভাবে সংরক্ষিত হয়েছে।');
    }

    /**
     * Update campaign logo and size settings (alias for updateFormCustomizer)
     */
    public function updateLogoSettings(Request $request, EventCampaign $campaign)
    {
        return $this->updateFormCustomizer($request, $campaign);
    }

    /**
     * One-command clean printing for all registered participants.
     */
    public function printAll(Request $request, EventCampaign $campaign)
    {
        $campaign->loadCount('registrations');
        $query = $campaign->registrations();

        // Apply filters if passed via query
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('division')) {
            $rawDiv = trim($request->division);
            $canonicalDiv = \App\Support\BangladeshGeo::normalizeDivision($rawDiv) ?: $rawDiv;
            $divVariants = \App\Support\BangladeshGeo::getVariants($canonicalDiv);
            $query->where(function ($q) use ($divVariants) {
                foreach ($divVariants as $v) {
                    $q->orWhere('form_data->perm_division', 'like', "%{$v}%")
                      ->orWhere('form_data->division', 'like', "%{$v}%");
                }
            });
        }
        if ($request->filled('district')) {
            $rawDst = trim($request->district);
            $canonicalDst = \App\Support\BangladeshGeo::normalizeDistrict($rawDst) ?: $rawDst;
            $dstVariants = \App\Support\BangladeshGeo::getVariants($canonicalDst);
            $query->where(function ($q) use ($dstVariants) {
                $q->whereIn('district', $dstVariants)
                  ->orWhere(function ($sq) use ($dstVariants) {
                      foreach ($dstVariants as $v) {
                          $sq->orWhere('district', 'like', "%{$v}%")
                             ->orWhere('form_data->district', 'like', "%{$v}%");
                      }
                  });
            });
        }
        if ($request->boolean('is_littlemag') || $request->input('type') === 'littlemag') {
            $query->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->whereNotNull('form_data->magazine_name')
                       ->where('form_data->magazine_name', '!=', '');
                })
                ->orWhere('form_data->author_categories', 'like', '%লিটিলম্যাগ%')
                ->orWhere('form_data->author_category', 'like', '%লিটিলম্যাগ%')
                ->orWhere('designation_or_class', 'like', '%লিটিলম্যাগ%')
                ->orWhere('designation_or_class', 'like', '%সম্পাদক%');
            });
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('registration_number', 'like', "%{$s}%")
                  ->orWhere('district', 'like', "%{$s}%")
                  ->orWhere('thana', 'like', "%{$s}%")
                  ->orWhere('form_data->magazine_name', 'like', "%{$s}%");
            });
        }

        $sort = $request->input('sort', 'latest');
        if ($sort === 'alpha' || $sort === 'name') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'alpha_desc') {
            $query->orderBy('name', 'desc');
        } elseif ($sort === 'oldest') {
            $query->orderBy('id', 'asc');
        } elseif ($sort === 'reg_no') {
            $query->orderBy('registration_number', 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $allRegistrations = $query->get();
        $totalCount = $allRegistrations->count();
        $isLittleMagOnly = $request->boolean('is_littlemag') || $request->input('type') === 'littlemag';

        return view('admin.event_campaigns.print_all', compact(
            'campaign',
            'allRegistrations',
            'totalCount',
            'isLittleMagOnly'
        ));
    }

    /**
     * Send direct custom SMS to a participant.
     */
    public function sendCustomSms(Request $request, EventRegistration $registration)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $phone = $registration->phone;
        if (empty($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'অংশগ্রহণকারীর কোনো মোবাইল নম্বর পাওয়া যায়নি।',
            ], 422);
        }

        try {
            $res = \App\Services\SmsService::send($phone, $validated['message']);
            if (!empty($res['success'])) {
                return response()->json([
                    'success' => true,
                    'message' => "এসএমএস সফলভাবে {$phone} নম্বরে পাঠানো হয়েছে।",
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'এসএমএস গেটওয়ে রেসপন্স: ' . ($res['message'] ?? 'ব্যর্থ'),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'এসএমএস পাঠানোতে সমস্যা: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * One-click conversion from Little Magazine Editor to Writer (লেখক).
     */
    public function convertToWriter(Request $request, EventRegistration $registration)
    {
        $formData = $registration->form_data ?? [];
        $targetCategory = $request->input('category', 'লেখক');
        
        $formData['author_category'] = $targetCategory;
        $formData['author_categories'] = [$targetCategory];
        
        // Remove magazine flags
        unset($formData['magazine_name']);
        unset($formData['magazine_issue_count']);

        $updateData = [
            'form_data' => $formData,
        ];

        if ($registration->designation_or_class === 'সম্পাদক' || mb_strpos((string)$registration->designation_or_class, 'সম্পাদক') !== false) {
            $updateData['designation_or_class'] = $targetCategory;
        }

        $registration->update($updateData);

        $msg = "অংশগ্রহণকারী #{$registration->registration_number} ({$registration->name})-কে সফলভাবে '{$targetCategory}' ক্যাটাগরিতে স্থানান্তর করা হয়েছে।";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => $msg,
                'registration' => $registration,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Broadcast bulk dynamic update SMS to participants (Filtered, Selected, or All).
     */
    public function broadcastSms(Request $request, EventCampaign $campaign)
    {
        $validated = $request->validate([
            'message'               => 'required|string|max:1000',
            'target_group'          => 'required|string|in:all,confirmed,pending,selected,filtered,custom_ids',
            'selected_ids'          => 'nullable|string',
            'division'              => 'nullable|string',
            'district'              => 'nullable|string',
            'avoid_duplicate_phone' => 'nullable|boolean',
        ]);

        $query = $campaign->registrations();

        // Target group filter
        switch ($validated['target_group']) {
            case 'confirmed':
                $query->whereIn('status', ['confirmed', 'selected', 'approved']);
                break;
            case 'pending':
                $query->where('status', 'pending');
                break;
            case 'selected':
                $query->where('status', 'selected');
                break;
            case 'custom_ids':
                $ids = json_decode($validated['selected_ids'] ?? '[]', true) ?: [];
                if (!empty($ids) && is_array($ids)) {
                    $query->whereIn('id', $ids);
                } else {
                    return response()->json(['success' => false, 'message' => 'কোনো অংশগ্রহণকারী নির্বাচন করা হয়নি।'], 422);
                }
                break;
            case 'filtered':
                if (!empty($validated['division'])) {
                    $div = trim($validated['division']);
                    $query->where(function($q) use ($div) {
                        $q->where('form_data->perm_division', $div)
                          ->orWhere('form_data->division', $div)
                          ->orWhere('form_data->perm_division', 'like', "%{$div}%");
                    });
                }
                if (!empty($validated['district'])) {
                    $dst = trim($validated['district']);
                    $query->where(function($q) use ($dst) {
                        $q->where('district', $dst)
                          ->orWhere('form_data->district', $dst)
                          ->orWhere('form_data->perm_district', $dst);
                    });
                }
                break;
            case 'all':
            default:
                // All participants of this campaign
                break;
        }

        $registrations = $query->with('campaign')->get();

        if ($registrations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'নির্বাচিত ফিল্টারে কোনো অংশগ্রহণকারী পাওয়া যায়নি।',
            ], 422);
        }

        $rawMessage = $validated['message'];
        $venue = $campaign->form_settings['form_venue'] ?? 'রংপুর';
        $eventDate = $campaign->form_settings['form_date'] ?? ($campaign->starts_at ? $campaign->starts_at->format('d M Y') : '২০ অক্টোবর ২০২৬');
        $eventTitle = $campaign->title;

        $sentCount = 0;
        $failedCount = 0;
        $seenPhones = [];
        $avoidDup = $request->boolean('avoid_duplicate_phone', true);

        foreach ($registrations as $r) {
            $phone = trim($r->phone ?? '');
            if (empty($phone)) {
                $failedCount++;
                continue;
            }

            $normPhone = \App\Support\PhoneNormalizer::toE164($phone);
            if (empty($normPhone)) {
                $failedCount++;
                continue;
            }

            if ($avoidDup && isset($seenPhones[$normPhone])) {
                continue; // Skip duplicate phone number
            }
            $seenPhones[$normPhone] = true;

            // Replace dynamic placeholders
            $cardUrl = url('/card/' . $r->registration_number);
            $msg = str_replace([
                '{name}',
                '{reg_no}',
                '{card_url}',
                '{event_title}',
                '{venue}',
                '{date}',
            ], [
                $r->name,
                $r->registration_number,
                $cardUrl,
                $eventTitle,
                $venue,
                $eventDate,
            ], $rawMessage);

            try {
                $res = \App\Services\SmsService::send($normPhone, $msg);
                if (!empty($res['success'])) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Broadcast SMS error for {$r->id} ({$normPhone}): " . $e->getMessage());
                $failedCount++;
            }
        }

        return response()->json([
            'success'      => true,
            'message'      => "{$sentCount} জন অংশগ্রহণকারীকে সফলভাবে ইভেন্ট আপডেট বার্তা পাঠানো হয়েছে!" . ($failedCount > 0 ? " ({$failedCount}টি নম্বর ব্যর্থ/অনুপলব্ধ)" : ''),
            'sent_count'   => $sentCount,
            'failed_count' => $failedCount,
            'total_target' => $registrations->count(),
        ]);
    }
}
