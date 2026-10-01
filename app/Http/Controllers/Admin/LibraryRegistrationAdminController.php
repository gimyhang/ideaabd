<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LibraryRegistrationAdminController extends Controller
{
    /**
     * Dedicated Dashboard & Registry for Library Book Grants (পাঠাগার ব্যবস্থাপনা ও বই অনুদান).
     */
    public function index(Request $request)
    {
        // Ensure pathagar campaign exists
        $campaign = EventCampaign::where('slug', 'pathagar')
            ->orWhere('type', 'library')
            ->first();

        if (!$campaign) {
            $campaign = EventCampaign::create([
                'title'               => 'বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন ২০২৬',
                'slug'                => 'pathagar',
                'type'                => 'library',
                'badge_text'          => 'পাঠাগার বই অনুদান ২০২৬',
                'short_description'   => 'বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচিতে অংশ নিয়ে পাঠাগার ও শিক্ষা প্রতিষ্ঠানের জন্য বই অনুদান প্রাপ্তির নিবন্ধন ফরম।',
                'description'         => 'আইডিয়া প্রকাশন ও বুকস অব আইডিয়া-এর বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচির আওতায় দেশের বিভিন্ন প্রান্তের সাধারণ পাঠাগার, ক্লাব লাইব্রেরি ও শিক্ষা প্রতিষ্ঠানসমূহে বিনামূল্যে বই প্রদান করা হবে। ফরমটি যথাযথভাবে পূরণ করে নিবন্ধন সম্পন্ন করুন।',
                'theme_color'         => '#047857',
                'has_fee_or_donation' => false,
                'fee_amount'          => 0.00,
                'is_active'           => true,
                'success_message'     => 'আপনার পাঠাগারের নিবন্ধন সফলভাবে সম্পন্ন হয়েছে! আমাদের প্রতিনিধি আপনার সাথে দ্রুত যোগাযোগ করবে এবং যাচাই শেষে বই অনুদানের তথ্য জানিয়ে দেওয়া হবে।',
                'custom_fields'       => [],
                'form_settings'       => ['is_library_form' => true, 'requires_approval' => true],
            ]);
        }

        // Query all library registrations across any library campaign
        $query = EventRegistration::with(['campaign', 'user'])
            ->where(function ($q) {
                $q->whereHas('campaign', function ($cq) {
                    $cq->where('type', 'library')->orWhere('slug', 'pathagar');
                })
                ->orWhereNotNull('form_data->library_name')
                ->orWhereNotNull('form_data->reader_count');
            })
            ->latest();

        // Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('institution_or_org', 'like', "%{$s}%")
                  ->orWhere('registration_number', 'like', "%{$s}%")
                  ->orWhere('district', 'like', "%{$s}%")
                  ->orWhere('thana', 'like', "%{$s}%")
                  ->orWhere('form_data->reg_no', 'like', "%{$s}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Division Filter
        if ($request->filled('division')) {
            $query->where('form_data->division', $request->division);
        }

        // District Filter
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        // Acknowledgment Status Filter
        if ($request->filled('ack_status')) {
            if ($request->ack_status === 'acknowledged') {
                $query->where(function ($q) {
                    $q->where('form_data->acknowledgment_status', 'acknowledged')
                      ->orWhereNotNull('form_data->received_date')
                      ->orWhereNotNull('form_data->received_books_count');
                });
            } elseif ($request->ack_status === 'dispatched') {
                $query->where(function ($q) {
                    $q->whereNotNull('form_data->books_allocated')
                      ->whereNull('form_data->received_date');
                });
            } elseif ($request->ack_status === 'pending') {
                $query->whereNull('form_data->books_allocated');
            }
        }

        $perPage = intval($request->input('per_page', 25));
        if (!in_array($perPage, [10, 25, 50, 100, 250], true)) {
            $perPage = 25;
        }

        $libraries = $query->paginate($perPage)->withQueryString();

        // High-level KPI Stats Calculation
        $allLibrariesQuery = EventRegistration::where(function ($q) {
            $q->whereHas('campaign', function ($cq) {
                $cq->where('type', 'library')->orWhere('slug', 'pathagar');
            })
            ->orWhereNotNull('form_data->library_name')
            ->orWhereNotNull('form_data->reader_count');
        });

        $totalLibraries = (clone $allLibrariesQuery)->count();
        $approvedLibraries = (clone $allLibrariesQuery)->whereIn('status', ['confirmed', 'selected', 'approved'])->count();
        $pendingReview = (clone $allLibrariesQuery)->where('status', 'pending')->count();
        $rejectedCount = (clone $allLibrariesQuery)->where('status', 'rejected')->count();

        // Calculate total books allocated & acknowledged, readers, and existing books
        $allRecords = (clone $allLibrariesQuery)->select('id', 'district', 'thana', 'status', 'form_data')->get();
        $totalBooksAllocated = 0;
        $totalBooksReceived = 0;
        $acknowledgedCount = 0;
        $totalReadersCount = 0;
        $totalExistingBooks = 0;
        $divisionStats = [
            'ঢাকা' => 0, 'রংপুর' => 0, 'রাজশাহী' => 0, 'চট্টগ্রাম' => 0,
            'খুলনা' => 0, 'বরিশাল' => 0, 'সিলেট' => 0, 'ময়মনসিংহ' => 0
        ];
        $districtSet = [];

        foreach ($allRecords as $row) {
            $fd = $row->form_data ?? [];
            $alloc = intval($fd['books_allocated'] ?? 0);
            $recv = intval($fd['received_books_count'] ?? 0);
            $readers = intval($fd['reader_count'] ?? 0);
            $curBooks = intval($fd['current_book_count'] ?? 0);

            $totalBooksAllocated += $alloc;
            $totalBooksReceived += $recv;
            $totalReadersCount += $readers;
            $totalExistingBooks += $curBooks;

            if (!empty($fd['received_date']) || !empty($fd['is_acknowledged']) || ($fd['acknowledgment_status'] ?? '') === 'acknowledged') {
                $acknowledgedCount++;
            }

            if (!empty($row->district)) {
                $districtSet[trim($row->district)] = true;
            }

            $div = $fd['division'] ?? '';
            if ($div && isset($divisionStats[$div])) {
                $divisionStats[$div]++;
            }
        }

        $uniqueDistricts = count($districtSet);

        // Form & Print Customizer Settings
        $formSettings = array_merge([
            'logo_url'            => asset('images/logo.png'),
            'logo_size'           => 24,
            'brand_name'          => 'আইডিয়া পাঠাগার',
            'sub_title'           => 'বই অনুদান আবেদন ফরম',
            'session_text'        => '',
            'brand_tag'           => "প্রধান কার্যালয়: ঢাকা, বাংলাদেশ\nwww.ideaabd.com",
            'banner_title'        => 'বিনামূল্যে বই বিতরণ কর্মসূচি ও পাঠাগার নিবন্ধন আবেদন ফরম',
            'grant_session'       => '২০২৬ অনুদান কর্মসূচি',
            'officer_name'        => 'সাকিল মাসুদ',
            'officer_designation' => 'তত্বাবধায়ক ও প্রতিষ্ঠাতা',
            'officer_org'         => 'আইডিয়া পাঠাগার ও প্রকাশন',
            'declaration_text'    => 'আইডিয়া পাঠাগার নিজ উদ্যোগে বই বিতরণ করে। বই প্রদানের ক্ষেত্রে যে কোনো সিদ্ধান্ত গ্রহণের ক্ষমতা সংরক্ষণ করে।',
            'theme_color'         => '#047857',
            'custom_css'          => '',
            'custom_js'           => '',
        ], $campaign->form_settings ?? []);

        return view('admin.libraries.index', compact(
            'libraries',
            'campaign',
            'totalLibraries',
            'approvedLibraries',
            'pendingReview',
            'rejectedCount',
            'totalBooksAllocated',
            'totalBooksReceived',
            'acknowledgedCount',
            'totalReadersCount',
            'totalExistingBooks',
            'uniqueDistricts',
            'divisionStats',
            'formSettings',
            'perPage'
        ));
    }

    /**
     * Update Form & Print Slip Customizer Settings (Logo, Texts, Signatures, CSS, JS, PHP).
     */
    public function updateSettings(Request $request)
    {
        $campaign = EventCampaign::where('slug', 'pathagar')
            ->orWhere('type', 'library')
            ->firstOrFail();

        $validated = $request->validate([
            'logo_url'            => 'nullable|string|max:500',
            'logo_size'           => 'nullable|integer|min:10|max:120',
            'brand_name'          => 'nullable|string|max:200',
            'sub_title'           => 'nullable|string|max:200',
            'session_text'        => 'nullable|string|max:200',
            'brand_tag'           => 'nullable|string|max:500',
            'banner_title'        => 'nullable|string|max:300',
            'grant_session'       => 'nullable|string|max:200',
            'officer_name'        => 'nullable|string|max:200',
            'officer_designation' => 'nullable|string|max:200',
            'officer_org'         => 'nullable|string|max:200',
            'declaration_text'    => 'nullable|string|max:1000',
            'theme_color'         => 'nullable|string|max:30',
            'custom_css'          => 'nullable|string',
            'custom_js'           => 'nullable|string',
            'logo_file'           => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
        ]);

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $path = $file->store('campaigns/logos', 'public');
            $validated['logo_url'] = asset('storage/' . $path);
        }

        unset($validated['logo_file']);

        $currentSettings = $campaign->form_settings ?? [];
        $updatedSettings = array_merge($currentSettings, array_filter($validated, fn($v) => !is_null($v)));

        $campaign->update([
            'form_settings' => $updatedSettings,
            'theme_color'   => $validated['theme_color'] ?? $campaign->theme_color,
        ]);

        $msg = 'পাঠাগার ফরম ও প্রিন্ট সেটিংস সফলভাবে সেভ করা হয়েছে।';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => $msg,
                'settings' => $updatedSettings,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Allocate books and record dispatch/delivery details with itemized book list for a library.
     */
    public function updateDispatch(Request $request, EventRegistration $registration)
    {
        $validated = $request->validate([
            'books_allocated'      => 'nullable|integer|min:1',
            'dispatched_date'      => 'required|date',
            'dispatch_tracking_no' => 'nullable|string|max:100',
            'dispatch_notes'       => 'nullable|string|max:1000',
            'delivery_method'      => 'nullable|string|max:150',
            'send_sms_alert'       => 'nullable|boolean',
            'book_items'           => 'nullable|array',
            'book_items.*.title'   => 'nullable|string|max:255',
            'book_items.*.copies'  => 'nullable|integer|min:1',
            'book_items.*.category'=> 'nullable|string|max:150',
            'book_items.*.author'  => 'nullable|string|max:150',
        ]);

        $cleanBookItems = [];
        $calculatedCopies = 0;
        if (!empty($validated['book_items']) && is_array($validated['book_items'])) {
            foreach ($validated['book_items'] as $item) {
                $title = trim($item['title'] ?? '');
                $copies = intval($item['copies'] ?? 1);
                if (!empty($title)) {
                    $cleanBookItems[] = [
                        'title'    => $title,
                        'copies'   => $copies > 0 ? $copies : 1,
                        'category' => trim($item['category'] ?? ''),
                        'author'   => trim($item['author'] ?? ''),
                    ];
                    $calculatedCopies += ($copies > 0 ? $copies : 1);
                }
            }
        }

        $totalAllocated = $calculatedCopies > 0 ? $calculatedCopies : intval($validated['books_allocated'] ?? 1);

        $formData = $registration->form_data ?? [];
        $formData['books_allocated']      = $totalAllocated;
        $formData['allocated_books_list'] = $cleanBookItems;
        $formData['dispatched_date']      = $validated['dispatched_date'];
        $formData['dispatch_tracking_no'] = $validated['dispatch_tracking_no'] ?? null;
        $formData['dispatch_notes']       = $validated['dispatch_notes'] ?? null;
        if (!empty($validated['delivery_method'])) {
            $formData['delivery_method']  = $validated['delivery_method'];
        }
        $formData['dispatched_at']        = now()->toDateTimeString();
        
        if (empty($formData['acknowledgment_status'])) {
            $formData['acknowledgment_status'] = 'dispatched';
        }

        $registration->update([
            'form_data' => $formData,
            'status'    => ($registration->status === 'pending') ? 'confirmed' : $registration->status,
        ]);

        // Send SMS to Library Representative
        if ($request->boolean('send_sms_alert')) {
            try {
                $ackUrl = url('/pathagar/acknowledgment/' . $registration->registration_number);
                $smsMsg = "আইডিয়া প্রকাশন — '{$registration->institution_or_org}'-এর জন্য {$totalAllocated} টি বই বরাদ্দ করা হয়েছে (তারিখ: {$validated['dispatched_date']})। বই পাওয়ার পর প্রাপ্তিস্বীকার লিংক: {$ackUrl} — আইডিয়া প্রকাশন";
                SmsService::send($registration->phone, $smsMsg);
            } catch (\Throwable $e) {
                Log::warning("Dispatch SMS Error: " . $e->getMessage());
            }
        }

        $msg = "{$totalAllocated} books allocated and dispatch records updated successfully for '{$registration->institution_or_org}'.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'              => true,
                'books_allocated'      => $formData['books_allocated'],
                'allocated_books_list' => $cleanBookItems,
                'dispatched_date'      => $formData['dispatched_date'],
                'message'              => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Record or verify Receipt Acknowledgment (প্রাপ্তিস্বীকার) by Admin.
     */
    public function updateAcknowledgment(Request $request, EventRegistration $registration)
    {
        $validated = $request->validate([
            'received_books_count' => 'required|integer|min:1',
            'received_date'        => 'required|date',
            'acknowledgment_notes' => 'nullable|string|max:1000',
            'status'               => 'nullable|string|in:confirmed,selected,pending',
        ]);

        $formData = $registration->form_data ?? [];
        $formData['received_books_count']  = intval($validated['received_books_count']);
        $formData['received_date']         = $validated['received_date'];
        $formData['acknowledgment_notes']  = $validated['acknowledgment_notes'] ?? null;
        $formData['acknowledgment_status'] = 'acknowledged';
        $formData['is_acknowledged']       = true;
        $formData['acknowledged_at']       = now()->toDateTimeString();
        $formData['acknowledged_by']       = 'admin (' . (auth()->user()->name ?? 'Admin') . ')';

        $registration->update([
            'form_data' => $formData,
            'status'    => $validated['status'] ?? 'confirmed',
        ]);

        $msg = "Receipt acknowledgment of {$validated['received_books_count']} books confirmed for '{$registration->institution_or_org}'.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'              => true,
                'received_books_count' => $formData['received_books_count'],
                'received_date'        => $formData['received_date'],
                'message'              => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * 1-Click Toggle Approval for Library registration.
     */
    public function toggleApproval(Request $request, EventRegistration $registration)
    {
        $isApproved = in_array($registration->status, ['confirmed', 'selected', 'approved'], true);
        $newStatus = $isApproved ? 'pending' : 'confirmed';

        $registration->update(['status' => $newStatus]);

        if ($newStatus === 'confirmed') {
            try {
                $slipUrl = url('/pathagar/slip/' . $registration->registration_number);
                $smsText = "অভিনন্দন! বাৎসরিক বিনামূল্যে বই বিতরণ কর্মসূচিতে '{$registration->institution_or_org}'-এর নিবন্ধন অনুমোদিত হয়েছে। স্লিপ: {$slipUrl} — আইডিয়া প্রকাশন";
                SmsService::send($registration->phone, $smsText);
            } catch (\Throwable $e) {
                Log::warning("Library approval SMS error: " . $e->getMessage());
            }
        }

        $msg = $newStatus === 'confirmed'
            ? "'{$registration->institution_or_org}' পাঠাগারটি অনুমোদিত হয়েছে।"
            : "'{$registration->institution_or_org}' পেন্ডিং তালিকায় রাখা হয়েছে।";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'status'   => $newStatus,
                'approved' => ($newStatus === 'confirmed'),
                'message'  => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Printable Official Library Book Grant Application Form & PDF Preview.
     */
    public function printSlip(EventRegistration $registration)
    {
        $registration->load('campaign', 'user');
        return view('frontend.events.library_form_print', [
            'registration' => $registration,
            'campaign'     => $registration->campaign,
            'isPdf'        => false,
        ]);
    }

    /**
     * Download Official Library Book Grant Form as PDF.
     */
    public function downloadPdf(EventRegistration $registration)
    {
        $registration->load('campaign', 'user');
        $campaign = $registration->campaign;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('frontend.events.library_form_print', [
            'registration' => $registration,
            'campaign'     => $campaign,
            'isPdf'        => true,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'sans-serif',
        ]);

        $libName = $registration->institution_or_org ?: 'Library';
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $libName);
        $filename = "Library_Grant_Form_{$registration->registration_number}_{$safeName}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export all Library Grant data to CSV.
     */
    public function exportCsv()
    {
        $libraries = EventRegistration::where(function ($q) {
            $q->whereHas('campaign', function ($cq) {
                $cq->where('type', 'library')->orWhere('slug', 'pathagar');
            })
            ->orWhereNotNull('form_data->library_name')
            ->orWhereNotNull('form_data->reader_count');
        })->latest()->get();

        $filename = "library_book_grants_" . date('Y-m-d') . ".csv";
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($libraries) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, [
                'Reg No', 'Library Name', 'Type', 'Govt Reg No', 'Est Year', 'Representative Name', 'Designation', 'Phone', 'Email',
                'President Name', 'President Phone', 'Secretary Name', 'Secretary Phone',
                'Division', 'District', 'Thana', 'Address', 'Readers Count', 'Current Books',
                'Books Allocated', 'Dispatched Date', 'Tracking No',
                'Received Books', 'Received Date', 'Acknowledgment Status', 'Approval Status', 'Registered Date'
            ]);

            foreach ($libraries as $lib) {
                $fd = $lib->form_data ?? [];
                fputcsv($handle, [
                    $lib->registration_number,
                    $lib->institution_or_org ?: ($fd['library_name'] ?? ''),
                    $fd['library_type'] ?? $lib->designation_or_class,
                    $fd['reg_no'] ?? '',
                    $fd['established_year'] ?? '',
                    $lib->name,
                    $lib->designation_or_class ?: ($fd['designation_or_class'] ?? ''),
                    $lib->phone,
                    $lib->email,
                    $fd['president_name'] ?? '',
                    $fd['president_phone'] ?? '',
                    $fd['secretary_name'] ?? '',
                    $fd['secretary_phone'] ?? '',
                    $fd['division'] ?? '',
                    $lib->district,
                    $lib->thana,
                    $lib->address,
                    $fd['reader_count'] ?? '',
                    $fd['current_book_count'] ?? '',
                    $fd['books_allocated'] ?? '0',
                    $fd['dispatched_date'] ?? '',
                    $fd['dispatch_tracking_no'] ?? '',
                    $fd['received_books_count'] ?? '0',
                    $fd['received_date'] ?? '',
                    $fd['acknowledgment_status'] ?? ($lib->isAcknowledged() ? 'Acknowledged' : 'Pending'),
                    $lib->status,
                    $lib->created_at ? $lib->created_at->format('Y-m-d H:i') : '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
