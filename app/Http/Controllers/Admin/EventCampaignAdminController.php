<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\Request;
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
        $campaignsQuery = EventCampaign::withCount('registrations')->latest();

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
            'type'                 => 'required|string|in:event,donation,scholarship,competition,workshop',
            'badge_text'           => 'nullable|string|max:50',
            'short_description'    => 'nullable|string|max:500',
            'description'          => 'nullable|string',
            'banner_image'         => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:2048',
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
        $validated['theme_color'] = $validated['theme_color'] ?: '#0284c7';

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

        $regQuery = $campaign->registrations()->with('user')->latest();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $regQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('registration_number', 'like', "%{$s}%")
                  ->orWhere('institution_or_org', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $regQuery->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $regQuery->where('payment_status', $request->payment_status);
        }

        $perPage = (int) $request->input('per_page', $campaign->table_settings['per_page'] ?? 25);
        if (!in_array($perPage, [10, 25, 50, 100, 250], true)) {
            $perPage = 25;
        }

        $registrations = $regQuery->paginate($perPage)->withQueryString();
        $totalRegistrations = $campaign->registrations()->count();
        $totalCollected = $campaign->registrations()->where('payment_status', 'verified')->sum('amount_paid');

        return view('admin.event_campaigns.show', compact('campaign', 'registrations', 'totalRegistrations', 'totalCollected', 'perPage'));
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
            'type'                 => 'required|string|in:event,donation,scholarship,competition,workshop',
            'badge_text'           => 'nullable|string|max:50',
            'short_description'    => 'nullable|string|max:500',
            'description'          => 'nullable|string',
            'banner_image'         => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:2048',
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
            if ($campaign->banner_image && Storage::disk('public')->exists($campaign->banner_image)) {
                Storage::disk('public')->delete($campaign->banner_image);
            }
            $validated['banner_image'] = $request->file('banner_image')->store('campaigns', 'public');
        }

        $validated['has_fee_or_donation'] = !empty($request->has_fee_or_donation);
        $validated['is_donation_flexible'] = !empty($request->is_donation_flexible);
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;
        $validated['slug'] = strtolower(trim($validated['slug']));
        $validated['theme_color'] = $validated['theme_color'] ?: '#0284c7';

        if ($request->has('custom_fields_json')) {
            $fields = json_decode($request->custom_fields_json, true);
            $validated['custom_fields'] = is_array($fields) ? $fields : [];
        }

        if ($request->has('form_settings_json')) {
            $fSettings = json_decode($request->form_settings_json, true);
            $validated['form_settings'] = is_array($fSettings) ? $fSettings : [];
        }

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
     * Update individual participant status or payment status.
     */
    public function updateRegistration(Request $request, EventRegistration $registration)
    {
        $validated = $request->validate([
            'status'         => 'nullable|string|in:confirmed,pending,rejected,attended',
            'payment_status' => 'nullable|string|in:pending,verified,waived,refunded',
            'admin_notes'    => 'nullable|string|max:1000',
        ]);

        $registration->update(array_filter($validated, fn($v) => !is_null($v)));

        return back()->with('success', 'Participant updated successfully.');
    }

    /**
     * Export participants list to CSV.
     */
    public function exportCsv(EventCampaign $campaign)
    {
        $registrations = $campaign->registrations()->latest()->get();
        $filename = "participants_{$campaign->slug}_" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($registrations) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, ['Reg Number', 'Name', 'Phone', 'Email', 'District', 'Thana', 'Institution', 'Amount Paid', 'Trx ID', 'Payment Status', 'Status', 'Registered At']);

            foreach ($registrations as $r) {
                fputcsv($handle, [
                    $r->registration_number,
                    $r->name,
                    $r->phone,
                    $r->email,
                    $r->district,
                    $r->thana,
                    $r->institution_or_org,
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
}
