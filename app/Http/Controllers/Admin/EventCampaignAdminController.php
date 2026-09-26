<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
     * Store new participant registration manually created by Admin.
     */
    public function storeRegistration(Request $request, EventCampaign $campaign)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'phone'                => 'required|string|max:30',
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

        // Clean & Format Phone
        $rawPhone = trim($validated['phone']);
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($cleanDigits, '880')) {
            $cleanDigits = substr($cleanDigits, 3);
        }
        if (str_starts_with($cleanDigits, '0')) {
            $cleanDigits = substr($cleanDigits, 1);
        }
        $formattedPhone = '+880' . $cleanDigits;
        $localPhone = '0' . $cleanDigits;

        // Auto Create or Find User
        $user = User::where('phone', $formattedPhone)
            ->orWhere('phone', $localPhone)
            ->orWhere('phone', $rawPhone)
            ->first();

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
                'phone'             => $formattedPhone,
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

        // Photo Upload Handling
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('campaigns/participants', 'public');
            $formData['author_photo'] = $photoPath;
            $formData['student_photo'] = $photoPath;
        }

        // Generate Registration Number
        $regNumber = EventRegistration::generateRegNumber($campaign->slug);

        $regStatus = $validated['status'] ?? 'confirmed';
        $payStatus = $validated['payment_status'] ?? ($campaign->has_fee_or_donation ? 'pending' : 'free');

        $registration = EventRegistration::create([
            'event_campaign_id'    => $campaign->id,
            'user_id'              => $user->id,
            'registration_number'  => $regNumber,
            'name'                 => $validated['name'],
            'phone'                => $localPhone,
            'email'                => $validated['email'] ?? ($user->email ?: null),
            'district'             => $validated['district'] ?? null,
            'thana'                => $validated['thana'] ?? null,
            'address'              => $validated['address'] ?? null,
            'institution_or_org'   => $validated['institution_or_org'] ?? null,
            'designation_or_class' => $validated['designation_or_class'] ?? null,
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

        // Photo Replacement Handling
        if ($request->hasFile('photo')) {
            $oldPhoto = $formData['student_photo'] ?? ($formData['author_photo'] ?? null);
            if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }
            $newPhoto = $request->file('photo')->store('campaigns/participants', 'public');
            $formData['author_photo'] = $newPhoto;
            $formData['student_photo'] = $newPhoto;
        }

        $updateData = array_filter($validated, fn($k) => in_array($k, [
            'name', 'phone', 'email', 'district', 'thana', 'address',
            'institution_or_org', 'designation_or_class', 'amount_paid',
            'payment_method', 'payment_status', 'transaction_id', 'status', 'admin_notes'
        ]), ARRAY_FILTER_USE_KEY);

        $updateData['form_data'] = $formData;

        $registration->update($updateData);

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
     * One-click Toggle Approval for Event/Writer Delegate Registrations (অনুমোদন বাটন).
     */
    public function toggleApproval(Request $request, EventRegistration $registration)
    {
        $isApproved = ($registration->status === 'confirmed' || $registration->status === 'selected' || $registration->status === 'approved');
        $newStatus = $isApproved ? 'pending' : 'confirmed';

        $registration->update([
            'status' => $newStatus,
        ]);

        if ($newStatus === 'confirmed') {
            // Optional SMS on approval
            try {
                $downloadUrl = url('/event-registration/print/' . $registration->registration_number);
                $smsText = "অভিনন্দন! '{$registration->campaign->title}'-এ আপনার ডেলিগেট নিবন্ধন অনুমোদিত হয়েছে। কার্ড ডাউনলোড লিংক: {$downloadUrl} — আইডিয়া প্রকাশন";
                \App\Services\SmsService::send($registration->phone, $smsText);
            } catch (\Throwable $e) {
                Log::warning("Approval SMS error: " . $e->getMessage());
            }

            $msg = "Participant #{$registration->registration_number} ({$registration->name}) has been APPROVED! Delegate pass is now downloadable.";
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'status'   => 'confirmed',
                    'approved' => true,
                    'message'  => $msg,
                ]);
            }

            return back()->with('success', $msg);
        }

        $msg = "Participant #{$registration->registration_number} ({$registration->name}) marked as PENDING.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'status'   => 'pending',
                'approved' => false,
                'message'  => $msg,
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
            return back()->with('error', 'No participants selected for bulk action.');
        }

        $query = $campaign->registrations()->whereIn('id', $ids);
        $count = $query->count();

        switch ($action) {
            case 'approve':
                $query->update(['status' => 'confirmed']);
                $msg = "{$count} participant(s) approved successfully.";
                break;

            case 'select_scholarship':
                $regs = $query->get();
                foreach ($regs as $r) {
                    $fd = $r->form_data ?? [];
                    $fd['is_scholarship_awarded'] = true;
                    $fd['scholarship_awarded_at'] = now()->toDateTimeString();
                    $r->update(['status' => 'selected', 'form_data' => $fd]);
                }
                $msg = "{$count} applicant(s) marked as Selected for Scholarship.";
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

            case 'delete':
                $query->delete();
                $msg = "{$count} participant(s) removed successfully.";
                break;

            default:
                return back()->with('error', 'Invalid bulk action specified.');
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

            $cardDesign['show_logo_border']  = $request->boolean('show_logo_border', false);
            $cardDesign['logo_border_width'] = intval($request->input('logo_border_width', $cardDesign['logo_border_width'] ?? 0));
            $cardDesign['show_event_logo']   = $request->boolean('show_event_logo', true);
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
            $cardDesign['show_header']       = $request->boolean('show_header', true);
            $cardDesign['show_logo']         = $request->boolean('show_logo', true);
            $cardDesign['logo_size']         = intval($request->input('logo_size', $cardDesign['logo_size'] ?? 58));
            $cardDesign['anniversary_text']  = $request->input('anniversary_text', '২০ অক্টোবর ১৩তম প্রতিষ্ঠাবার্ষিকী উপলক্ষে');
            $cardDesign['title_text']        = $request->input('title_text', 'রংপুর সাহিত্য উৎসব');
            $cardDesign['subtitle_text']     = $request->input('subtitle_text', 'ও ৩য় লিটিলম্যাগ মেলা');
            
            // 4. Badge & Card No
            $cardDesign['show_badge']        = $request->boolean('show_badge', true);
            $cardDesign['badge_text']        = $request->input('card_badge_text', 'আমন্ত্রণ কার্ড');
            
            // 5. Author Photo, Shape & Studio Filters (ফটো এডিটর স্যুট)
            $cardDesign['show_photo']          = $request->boolean('show_photo', true);
            $cardDesign['photo_size']          = intval($request->input('photo_size', 82));
            $cardDesign['photo_border_radius'] = $request->input('photo_border_radius', $cardDesign['photo_border_radius'] ?? '50%');
            $cardDesign['photo_border_width']  = intval($request->input('photo_border_width', $cardDesign['photo_border_width'] ?? 3));
            $cardDesign['photo_border_color']  = $request->input('photo_border_color', $cardDesign['photo_border_color'] ?? '#ffffff');
            $cardDesign['photo_shadow']        = $request->input('photo_shadow', $cardDesign['photo_shadow'] ?? 'soft');
            $cardDesign['photo_brightness']    = intval($request->input('photo_brightness', $cardDesign['photo_brightness'] ?? 100));
            $cardDesign['photo_contrast']      = intval($request->input('photo_contrast', $cardDesign['photo_contrast'] ?? 100));
            $cardDesign['photo_grayscale']     = intval($request->input('photo_grayscale', $cardDesign['photo_grayscale'] ?? 0));
            $cardDesign['photo_sepia']         = intval($request->input('photo_sepia', $cardDesign['photo_sepia'] ?? 0));
            
            // 6. Name Plate & Font Size & Line Spacing
            $cardDesign['show_name_plate']   = $request->boolean('show_name_plate', true);
            $cardDesign['name_font_size']    = intval($request->input('name_font_size', 16));
            $cardDesign['name_line_height']  = floatval($request->input('name_line_height', $cardDesign['name_line_height'] ?? 1.25));
            $cardDesign['name_spacing']      = intval($request->input('name_spacing', $cardDesign['name_spacing'] ?? 2));
            $cardDesign['plate_padding']     = intval($request->input('plate_padding', $cardDesign['plate_padding'] ?? 14));
            
            // 7. Quotation & Artwork
            $cardDesign['show_quote']        = $request->boolean('show_quote', true);
            $cardDesign['quote_text']        = $request->input('quote_text', "সাহিত্য উৎসব ও লিটিলম্যাগমেলায়\nআপনার উপস্থিতি ও অংশগ্রহণ\nআমাদের সম্মানিত করবে ।");
            $cardDesign['show_artwork']      = $request->boolean('show_artwork', true);
            
            // 8. Organizers 3 Columns
            $cardDesign['show_organizers']   = $request->boolean('show_organizers', true);
            $cardDesign['org_1_name']        = $request->input('org_1_name', 'সাকিল মাসুদ');
            $cardDesign['org_1_role']        = $request->input('org_1_role', "সদস্যসচিব, প্রতিষ্ঠাবার্ষিকী আয়োজক কমিটি ২০২৬\nও সাধারণ সম্পাদক, ফিরেদেখা");
            $cardDesign['org_1_phone']       = $request->input('org_1_phone', '০১৭২৬৯৭৬৯৮২');
            
            $cardDesign['org_2_name']        = $request->input('org_2_name', 'বাবুল সরকার');
            $cardDesign['org_2_role']        = $request->input('org_2_role', "আহ্বায়ক, প্রতিষ্ঠাবার্ষিকী আয়োজক কমিটি ২০২৬\nও সাহিত্য সম্পাদক, ফিরেদেখা");
            $cardDesign['org_2_phone']       = $request->input('org_2_phone', '01763170342');
            
            $cardDesign['org_3_name']        = $request->input('org_3_name', 'তাপস মাহমুদ');
            $cardDesign['org_3_role']        = $request->input('org_3_role', "সভাপতি,\nফিরেদেখা");
            $cardDesign['org_3_phone']       = $request->input('org_3_phone', '01820-547307');

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

        return back()->with('success', "Viva evaluation for #{$registration->registration_number} saved. Total Score: {$totalScore}/50.");
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

        $pdf->setPaper('a4', 'portrait');
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

