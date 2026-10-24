<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\AdminDashboardSetting;
use App\Models\AdminPermission;
use App\Models\User;
use App\Services\AdminAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\AdminRole;
use Illuminate\Http\JsonResponse;

class AdminAccessController extends Controller
{
    public function __construct(private readonly AdminAccessService $accessService)
    {
    }

    /**
     * View and manage Enterprise Role & Permission IAM Hub.
     */
    public function rolesPermissions(Request $request): View
    {
        abort_unless(auth()->user() && auth()->user()->isAdmin(), 403, 'Only the main administrator can open this page.');

        $stats = $this->accessService->getIamSummaryStats();
        $roles = $this->accessService->getAllRoles();
        $permissions = $this->accessService->getPermissionsGrouped();
        $rolePermissions = $this->accessService->getRolePermissionsMap();
        $staffUsers = $this->accessService->getStaffUsers($request);
        $recentLogs = $this->accessService->recentLogs(30);

        return view('admin.roles-permissions', compact(
            'stats',
            'roles',
            'permissions',
            'rolePermissions',
            'staffUsers',
            'recentLogs'
        ));
    }

    /**
     * Update permissions assigned to roles across the full matrix (AJAX or form).
     */
    public function updatePermissions(Request $request)
    {
        abort_unless(auth()->user() && auth()->user()->isAdmin(), 403, 'Only the main administrator can change role permissions.');

        $data = $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'nullable|array',
        ]);

        $validRoles = collect($this->accessService->getAllRoles())->pluck('slug')->all();
        $validPerms = \App\Models\AdminPermission::pluck('id')->map(fn ($id) => (int) $id)->all();
        $current = $this->accessService->getRolePermissionsMap();

        // Only known roles and permission ids; duplicates removed. A role missing from the
        // request keeps what it has (a stale page must never wipe a role it did not know about).
        $clean = [];
        foreach ($validRoles as $slug) {
            if (array_key_exists($slug, $data['permissions'] ?? [])) {
                $ids = array_map('intval', (array) $data['permissions'][$slug]);
                $clean[$slug] = array_values(array_unique(array_intersect($ids, $validPerms)));
            } else {
                $clean[$slug] = array_map('intval', (array) ($current[$slug] ?? []));
            }
        }

        $this->accessService->syncMatrix($clean);

        $message = 'Role permissions saved.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
    /**
     * Create a new custom role.
     */
    public function storeRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'nullable|string|max:60|unique:admin_roles,slug',
            'department'  => 'required|string|max:100',
            'badge_color' => 'nullable|string|max:30',
            'icon'        => 'nullable|string|max:60',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
        ], [], [
            'name'        => 'রোলের নাম',
            'slug'        => 'রোল স্লাগ',
            'department'  => 'বিভাগ',
            'badge_color' => 'ব্যাজ কালার',
            'icon'        => 'আইকন',
            'description' => 'বিবরণ',
        ]);

        $role = $this->accessService->createRole($data);

        return back()->with('success', "নতুন কাস্টম রোল '{$role->name}' সফলভাবে তৈরি করা হয়েছে!");
    }

    /**
     * Update an existing custom role.
     */
    public function updateRole(Request $request, AdminRole $role): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'department'  => 'required|string|max:100',
            'badge_color' => 'nullable|string|max:30',
            'icon'        => 'nullable|string|max:60',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'nullable|boolean',
        ]);

        $this->accessService->updateRole($role->id, $data);

        return back()->with('success', "রোল '{$role->name}' সফলভাবে আপডেট করা হয়েছে!");
    }

    /**
     * Clone an existing role.
     */
    public function cloneRole(Request $request, AdminRole $role): RedirectResponse
    {
        $request->validate([
            'new_name' => 'required|string|max:100',
        ]);

        $cloned = $this->accessService->cloneRole($role->id, $request->string('new_name')->trim()->value());

        return back()->with('success', "রোলটি সফলভাবে ক্লোন করা হয়েছে: '{$cloned->name}'!");
    }

    /**
     * Delete a custom role.
     */
    public function deleteRole(AdminRole $role): RedirectResponse
    {
        try {
            $this->accessService->deleteRole($role->id);
            return back()->with('success', 'কাস্টম রোলটি সফলভাবে মুছে ফেলা হয়েছে!');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get JSON inspector data for user direct permissions modal.
     */
    public function userPermissionsInspector(User $user): JsonResponse
    {
        $data = $this->accessService->getUserPermissionInspector($user);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Save direct user permission overrides (Grant / Deny).
     */
    public function updateUserDirectPermissions(Request $request, User $user): RedirectResponse
    {
        $grants = array_filter(array_map('intval', (array) $request->input('grants', [])));
        $denies = array_filter(array_map('intval', (array) $request->input('denies', [])));

        $this->accessService->syncUserDirectPermissions($user->id, $grants, $denies);

        return back()->with('success', "কর্মী '{$user->name}' এর স্পেশাল পারমিশন ওভাররাইড সফলভাবে সংরক্ষিত হয়েছে!");
    }

    /**
     * Toggle staff account active/suspended state.
     */
    public function toggleStaffStatus(User $user): RedirectResponse
    {
        $updated = $this->accessService->toggleStaffStatus($user->id);

        return back()->with('success', $updated->is_active
            ? "কর্মী '{$user->name}' এর অ্যাকাউন্ট সক্রিয় (Active) করা হয়েছে।"
            : "কর্মী '{$user->name}' এর অ্যাকাউন্ট সাময়িকভাবে স্থগিত (Suspended) করা হয়েছে।");
    }

    /**
     * Terminate all active sessions for a staff member.
     */
    public function terminateStaffSessions(User $user): RedirectResponse
    {
        $this->accessService->terminateStaffSessions($user->id);

        return back()->with('success', "কর্মী '{$user->name}' এর সকল ব্রাউজার সেশন ও লগইন বাতিল (ফোর্স লগআউট) করা হয়েছে!");
    }

    /**
     * Toggle force password reset on next login.
     */
    public function forceStaffPasswordReset(User $user): RedirectResponse
    {
        $updated = $this->accessService->forcePasswordReset($user->id);

        return back()->with('success', $updated->force_password_reset
            ? "কর্মী '{$user->name}' এর পরবর্তী লগইনে পাসওয়ার্ড পরিবর্তন বাধ্যতামূলক করা হয়েছে।"
            : "কর্মী '{$user->name}' এর পাসওয়ার্ড রিসেট বাধ্যবাধকতা তুলে নেওয়া হয়েছে।");
    }

    /**
     * Update staff member assigned role and optional IP whitelist.
     */
    public function updateStaffRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'custom_role_id' => 'nullable|integer',
            'role'           => 'nullable|string|max:60',
            'ip_whitelist'   => 'nullable|string|max:255',
        ]);

        $this->accessService->updateStaffRoleAndSecurity($user->id, $data);

        return back()->with('success', "কর্মী '{$user->name}' এর রোল ও সিকিউরিটি কনফিগারেশন আপডেট করা হয়েছে!");
    }

    /**
     * Appoint / assign any user (registered applicant/buyer/staff) to ANY role.
     */
    public function assignUserRole(Request $request, User $user)
    {
        abort_unless(auth()->user() && auth()->user()->isAdmin(), 403, 'শুধুমাত্র মূল সুপার অ্যাডমিন যে কাউকেই যেকোনো পদে পদায়ন বা নিয়োগ দিতে পারেন।');

        $validated = $request->validate([
            'role'           => 'required|string|max:60',
            'custom_role_id' => 'nullable|integer',
            'reg_status'     => 'nullable|string|in:pending,approved,rejected',
            'is_active'      => 'nullable|boolean',
            'notes'          => 'nullable|string|max:500',
        ]);

        $updatedUser = $this->accessService->assignUserRole(
            $user->id,
            $validated['role'],
            !empty($validated['custom_role_id']) ? (int) $validated['custom_role_id'] : null,
            $validated['reg_status'] ?? 'approved',
            $request->has('is_active') ? $request->boolean('is_active') : true,
            $validated['notes'] ?? null
        );

        $msg = "ব্যবহারকারী '{$updatedUser->name}' কে সফলভাবে '{$updatedUser->getRoleDisplayName()}' পদে পদায়ন ও নিয়োগ অনুমোদন করা হয়েছে!";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'user'    => $updatedUser,
                'role'    => $updatedUser->role,
                'role_name' => $updatedUser->getRoleDisplayName(),
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Revoke role and demote user back to general buyer.
     */
    public function revokeUserRole(Request $request, User $user)
    {
        abort_unless(auth()->user() && auth()->user()->isAdmin(), 403, 'শুধুমাত্র মূল সুপার অ্যাডমিন পদায়ন বাতিল করতে পারেন।');

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $updatedUser = $this->accessService->revokeUserRole($user->id, $validated['reason'] ?? null);

        $msg = "ব্যবহারকারী '{$updatedUser->name}' এর পদায়ন বাতিল করে সাধারণ গ্রাহক (Buyer) করা হয়েছে!";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'user'    => $updatedUser,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * View audit activity logs.
     */
    public function activityLogs(Request $request): View
    {
        $logs = Schema::hasTable('admin_activity_logs')
            ? AdminActivityLog::with('user')
                ->when($request->filled('search'), function ($q) use ($request) {
                    $term = '%' . $request->string('search')->trim() . '%';
                    $q->where('description', 'like', $term)
                      ->orWhere('action_type', 'like', $term)
                      ->orWhere('ip_address', 'like', $term);
                })
                ->when($request->filled('action'), fn ($q) => $q->where('action_type', $request->string('action')))
                ->latest()
                ->paginate(25)
                ->withQueryString()
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);

        return view('admin.activity-logs', compact('logs'));
    }

    /**
     * View and update system dashboard settings.
     */
    public function systemSettings(): View
    {
        $settings = [];
        if (Schema::hasTable('admin_dashboard_settings')) {
            $settings = AdminDashboardSetting::all()->pluck('value', 'key')->toArray();
        }

        $noticeSetting = $settings['system_notice'] ?? ['text' => '', 'active' => false, 'type' => 'info'];
        $maintSetting = $settings['maintenance_mode'] ?? ['enabled' => false, 'reason' => ''];
        $rawEcom = $settings['ecommerce_settings'] ?? [];
        $ecomSetting = array_merge([
            'delivery_dhaka'          => 50,
            'delivery_sub'            => 100,
            'delivery_outside'        => 120,
            'gift_wrap_fee'           => 20,
            'free_delivery_threshold' => 1500,
            'helpline_phone'          => '01726976982',
            'helpline_email'          => 'ideapbd@gmail.com',
            'whatsapp_number'         => '01726976982',
            'bkash_number'            => '01558712810',
            'nagad_number'            => '01558712810',
            'rocket_number'           => '01558712810',
            'payment_instruction'     => 'বিকাশ বা নগদ থেকে উল্লেখিত নম্বরে সেন্ড মানি করে TrxID ও পেমেন্ট নম্বর দিন।',
            // Coupon Configuration
            'coupon_enabled'          => false,
            'coupon_code'             => 'IDEA2026',
            'coupon_type'             => 'percent',
            'coupon_discount'         => 10,
            'coupon_min_order'        => 500,
            'coupon_description'      => 'বিশেষ কুপন ছাড়',
            // Threshold Offer Configuration
            'threshold_offer_enabled' => false,
            'threshold_offer_amount'  => 1000,
            'threshold_offer_type'    => 'free_delivery',
            'threshold_offer_discount'=> 100,
            'threshold_offer_title'   => '৳১০০০+ অর্ডারে ফ্রি ডেলিভারি ও বিশেষ উপহার!',
        ], is_array($rawEcom) ? $rawEcom : []);
        $themeSetting = $settings['theme_settings'] ?? [
            'primary_color' => '#0066cc',
            'secondary_color' => '#0099ff',
            'default_mode' => 'light',
        ];
        $invoiceSetting = $settings['invoice_settings'] ?? [
            'sender_name'    => 'আইডিয়া প্রকাশন',
            'sender_address' => 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ',
            'sender_phone'   => '01558712870',
            'sender_email'   => 'ideapbd@gmail.com',
            'sender_website' => 'www.ideaabd.com',
            'invoice_title'  => 'ক্যাশ মেমো / ইনভয়েস',
            'invoice_terms'  => 'পণ্য গ্রহণের সময় অনুগ্রহ করে চেক করে নিন। কোনো ত্রুটি থাকলে ডেলিভারি ম্যানের সামনেই হেল্পলাইনে যোগাযোগ করুন।',
            'invoice_footer' => 'বই পড়ার আনন্দ ছড়িয়ে পড়ুক সবার মাঝে। ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!',
        ];

        // Payment Gateways
        $paymentGateways = $settings['payment_gateways'] ?? [
            'bkash' => [
                'enabled'      => true,
                'name'         => 'বিকাশ (bKash)',
                'number'       => $ecomSetting['bkash_number'] ?? '01558712810',
                'type'         => 'personal',
                'instructions' => 'বিকাশ অ্যাপ থেকে Send Money অপশনে গিয়ে উপরে উল্লেখিত নম্বরে সর্বমোট বিল পাঠান।',
            ],
            'nagad' => [
                'enabled'      => true,
                'name'         => 'নগদ (Nagad)',
                'number'       => $ecomSetting['nagad_number'] ?? '01558712810',
                'type'         => 'personal',
                'instructions' => 'নগদ অ্যাপ থেকে Send Money অপশনে গিয়ে উপরে উল্লেখিত নম্বরে সর্বমোট বিল পাঠান।',
            ],
            'rocket' => [
                'enabled'      => false,
                'name'         => 'রকেট (Rocket)',
                'number'       => '01558712810',
                'type'         => 'personal',
                'instructions' => 'রকেট একাউন্ট থেকে সেন্ড মানি করুন।',
            ],
            'upay' => [
                'enabled'      => false,
                'name'         => 'উপায় (Upay)',
                'number'       => '01558712810',
                'type'         => 'personal',
                'instructions' => 'উপায় একাউন্ট থেকে সেন্ড মানি করুন।',
            ],
            'cod' => [
                'enabled'      => true,
                'name'         => 'ক্যাশ অন ডেলিভারি (COD)',
                'instructions' => 'বই হাতে পেয়ে মূল্য পরিশোধ করুন।',
            ],
            'bank' => [
                'enabled'      => false,
                'bank_name'    => 'Islami Bank Bangladesh Ltd',
                'account_name' => 'Idea Prokashon',
                'account_no'   => '2050XXXXXXXXX',
                'branch'       => 'Rangpur Branch',
                'routing'      => '125XXXXXXXX',
                'instructions' => 'ব্যাংক ডিপোজিট করে রসিদ স্লিপ বা রেফারেন্স নম্বর দিন।',
            ],
        ];

        // System Diagnostics
        $diagnostics = [
            'php_version'    => PHP_VERSION,
            'laravel_version' => app()->version(),
            'db_connection'  => config('database.default'),
            'app_env'        => app()->environment(),
            'app_debug'      => config('app.debug') ? 'সক্রিয় (True)' : 'নিষ্ক্রিয় (False)',
            'storage_link'   => is_link(public_path('storage')) || is_dir(public_path('storage')) ? 'সংযুক্ত (Connected)' : 'অনুপস্থিত (Unlinked)',
            'server_os'      => PHP_OS,
        ];

        // Header Navigation Menu Items
        $headerMenuItems = \App\Support\SiteSetting::headerNav();

        // Authors list for Designer Attribution & Profile linking
        $authors = \Illuminate\Support\Facades\Schema::hasTable('authors')
            ? \Modules\Author\Models\Author::orderBy('name')->get(['id', 'name', 'name_bn', 'slug'])
            : collect();

        $settingsVersion = Schema::hasTable('admin_dashboard_settings') ? $this->settingsVersion() : '';

        return view('admin.system-settings', compact(
            'settings', 'noticeSetting', 'maintSetting', 'ecomSetting', 'themeSetting', 'invoiceSetting', 'paymentGateways', 'diagnostics', 'headerMenuItems', 'authors', 'settingsVersion'
        ));
    }

    /**
     * Save dashboard settings.
     */
    public function updateSystemSettings(Request $request)
    {
        $request->validate([
            'site_name'       => 'nullable|string|max:100',
            'site_tagline'    => 'nullable|string|max:200',
            'show_designer_credit' => 'nullable|boolean',
            'designer_author_id'   => 'nullable|integer',
            'designer_name'        => 'nullable|string|max:100',
            'designer_slug'        => 'nullable|string|max:150',
            'designer_url'         => 'nullable|string|max:255',
            'notice_text'     => 'nullable|string|max:500',
            'notice_active'   => 'nullable|boolean',
            'notice_type'     => 'required|in:info,warning,success,danger',
            'site_logo'       => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:3072',
            'site_favicon'    => 'nullable|image|mimes:jpeg,png,jpg,svg,webp,ico|max:1024',
            'banner_1'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'banner_2'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'blog_og_banner'  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'delivery_dhaka'  => 'nullable|numeric|min:0',
            'delivery_sub'    => 'nullable|numeric|min:0',
            'delivery_outside'=> 'nullable|numeric|min:0',
            'gift_wrap_fee'   => 'nullable|numeric|min:0',
            'free_delivery_threshold' => 'nullable|numeric|min:0',
            'helpline_phone'  => 'nullable|string|max:30',
            'helpline_email'  => 'nullable|email|max:100',
            'whatsapp_number' => 'nullable|string|max:30',
            'primary_color'   => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
            'maintenance_mode'=> 'nullable|boolean',
            'maintenance_reason' => 'nullable|string|max:300',
            'ideapatra_section_badge' => 'nullable|string|max:150',
            'ideapatra_section_title' => 'nullable|string|max:250',
            'ideapatra_section_subtitle' => 'nullable|string|max:500',
            'terms_badge'     => 'nullable|string|max:200',
            'terms_title'     => 'nullable|string|max:250',
            'terms_subtitle'  => 'nullable|string|max:1000',
            'terms_version'   => 'nullable|string|max:100',
            'terms_return_days' => 'nullable|integer|min:1|max:90',
            'terms_refund_timeline' => 'nullable|string|max:100',
            'terms_return_conditions' => 'nullable|string|max:5000',
            'terms_return_excluded' => 'nullable|string|max:5000',
            'terms_shipping_note' => 'nullable|string|max:3000',
            'terms_ebook_drm_note' => 'nullable|string|max:3000',
            'terms_author_royalty_note' => 'nullable|string|max:3000',
            'terms_custom_notice' => 'nullable|string|max:5000',
        ]);

        // Optimistic lock: this page saves every setting at once, so refuse to silently
        // overwrite what another admin changed after this page was opened.
        if (Schema::hasTable('admin_dashboard_settings') && ! $request->boolean('force') && $request->filled('_version')
            && (string) $request->input('_version') !== $this->settingsVersion()) {
            $who = $this->latestSettingsEditor();
            $msg = 'Settings were changed' . ($who ? " by {$who}" : '') . ' after you opened this page.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'conflict' => true, 'message' => $msg, 'version' => $this->settingsVersion()], 409)
                : back()->with('error', $msg . ' Reload the page and try again.');
        }

        $before = Schema::hasTable('admin_dashboard_settings') ? $this->settingsSnapshot() : [];
        $this->syncDuplicateFields($request, $before);

        if (Schema::hasTable('admin_dashboard_settings')) {
            // 1. Site Branding Texts
            if ($request->filled('site_name')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'site_name'],
                    ['value' => $request->string('site_name')->trim()->value(), 'updated_by' => auth()->id()]
                );
            }
            if ($request->filled('site_tagline')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'site_tagline'],
                    ['value' => $request->string('site_tagline')->trim()->value(), 'updated_by' => auth()->id()]
                );
            }

            // 1.1 Ideapatra Section Customization Texts
            if ($request->has('ideapatra_section_badge')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'ideapatra_section_badge'],
                    ['value' => $request->string('ideapatra_section_badge')->trim()->value(), 'updated_by' => auth()->id()]
                );
            }
            if ($request->has('ideapatra_section_title')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'ideapatra_section_title'],
                    ['value' => $request->string('ideapatra_section_title')->trim()->value(), 'updated_by' => auth()->id()]
                );
            }
            if ($request->has('ideapatra_section_subtitle')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'ideapatra_section_subtitle'],
                    ['value' => $request->string('ideapatra_section_subtitle')->trim()->value(), 'updated_by' => auth()->id()]
                );
            }

            // 1.2 Terms, Return Policy & Legal Framework Customization
            $termsKeys = [
                'terms_badge', 'terms_title', 'terms_subtitle', 'terms_version',
                'terms_return_days', 'terms_refund_timeline', 'terms_return_conditions',
                'terms_return_excluded', 'terms_shipping_note', 'terms_ebook_drm_note',
                'terms_author_royalty_note', 'terms_custom_notice'
            ];
            foreach ($termsKeys as $tKey) {
                if ($request->has($tKey)) {
                    $tVal = $tKey === 'terms_return_days' 
                        ? (int) $request->input($tKey, 7)
                        : $request->input($tKey);
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => $tKey],
                        ['value' => $tVal, 'updated_by' => auth()->id()]
                    );
                }
            }

            // 1.3 Designer Attribution & Author Profile Settings
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'show_designer_credit'],
                ['value' => $request->boolean('show_designer_credit'), 'updated_by' => auth()->id()]
            );
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'designer_author_id'],
                ['value' => $request->filled('designer_author_id') ? (int) $request->input('designer_author_id') : null, 'updated_by' => auth()->id()]
            );
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'designer_name'],
                ['value' => $request->filled('designer_name') ? $request->string('designer_name')->trim()->value() : null, 'updated_by' => auth()->id()]
            );
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'designer_slug'],
                ['value' => $request->filled('designer_slug') ? $request->string('designer_slug')->trim()->value() : null, 'updated_by' => auth()->id()]
            );
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'designer_url'],
                ['value' => $request->filled('designer_url') ? $request->string('designer_url')->trim()->value() : null, 'updated_by' => auth()->id()]
            );

            // 1.4 About Page Customization Settings
            if ($request->has('about_page_title') || $request->has('about_hero_badge')) {
                $aboutData = [
                    'hero_badge'        => $request->input('about_hero_badge'),
                    'page_title'        => $request->input('about_page_title'),
                    'page_subtitle'     => $request->input('about_page_subtitle'),
                    'quote_text'        => $request->input('about_quote_text'),
                    'quote_author'      => $request->input('about_quote_author'),
                    'stat_books_count'  => $request->input('about_stat_books_count'),
                    'stat_books_label'  => $request->input('about_stat_books_label'),
                    'stat_lib_count'    => $request->input('about_stat_lib_count'),
                    'stat_lib_label'    => $request->input('about_stat_lib_label'),
                    'stat_years_count'  => $request->input('about_stat_years_count'),
                    'stat_years_label'  => $request->input('about_stat_years_label'),
                    'stat_fair_count'   => $request->input('about_stat_fair_count'),
                    'stat_fair_label'   => $request->input('about_stat_fair_label'),
                    'publisher_name'    => $request->input('about_publisher_name'),
                    'publisher_role'    => $request->input('about_publisher_role'),
                    'publisher_note'    => $request->input('about_publisher_note'),
                    'publisher_url'     => $request->input('about_publisher_url'),
                    'statement_p1'      => $request->input('about_statement_p1'),
                    'statement_p2'      => $request->input('about_statement_p2'),
                    'statement_p3'      => $request->input('about_statement_p3'),
                    'statement_p4'      => $request->input('about_statement_p4'),
                    'statement_p5'      => $request->input('about_statement_p5'),
                    'statement_p6'      => $request->input('about_statement_p6'),
                    'statement_p7'      => $request->input('about_statement_p7'),
                    'statement_p8'      => $request->input('about_statement_p8'),
                    'statement_p9'      => $request->input('about_statement_p9'),
                    'statement_p10'     => $request->input('about_statement_p10'),
                    'statement_p11'     => $request->input('about_statement_p11'),
                    'statement_p12'     => $request->input('about_statement_p12'),
                ];
                $existingAbout = \App\Support\SiteSetting::aboutCustomizer();
                $finalAbout = array_merge($existingAbout, array_filter($aboutData, fn($v) => !is_null($v)));
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'about_page_settings'],
                    ['value' => $finalAbout, 'updated_by' => auth()->id()]
                );
            }

            // 1.5 Contact Page Customization Settings
            if ($request->has('contact_hero_title') || $request->has('contact_address')) {
                $contactData = [
                    'hero_badge'        => $request->input('contact_hero_badge'),
                    'hero_title'        => $request->input('contact_hero_title'),
                    'hero_subtitle'     => $request->input('contact_hero_subtitle'),
                    'helpline_phone'    => $request->input('contact_phone'),
                    'whatsapp_number'   => $request->input('contact_whatsapp'),
                    'helpline_email'    => $request->input('contact_email'),
                    'contact_address'   => $request->input('contact_address'),
                    'working_hours'     => $request->input('contact_working_hours'),
                    'friday_note'       => $request->input('contact_friday_note'),
                    'map_url'           => $request->input('contact_map_url'),
                    'dept_editorial'    => $request->input('contact_dept_editorial'),
                    'dept_wholesale'    => $request->input('contact_dept_wholesale'),
                    'dept_accounts'     => $request->input('contact_dept_accounts'),
                ];
                $existingContact = \App\Support\SiteSetting::contactCustomizer();
                $finalContact = array_merge($existingContact, array_filter($contactData, fn($v) => !is_null($v)));
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'contact_page_settings'],
                    ['value' => $finalContact, 'updated_by' => auth()->id()]
                );
                if ($request->filled('contact_phone')) {
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'contact_phone'],
                        ['value' => $request->input('contact_phone'), 'updated_by' => auth()->id()]
                    );
                }
                if ($request->filled('contact_whatsapp')) {
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'whatsapp_number'],
                        ['value' => $request->input('contact_whatsapp'), 'updated_by' => auth()->id()]
                    );
                }
                if ($request->filled('contact_email')) {
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'contact_email'],
                        ['value' => $request->input('contact_email'), 'updated_by' => auth()->id()]
                    );
                }
                if ($request->filled('contact_address')) {
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'contact_address'],
                        ['value' => $request->input('contact_address'), 'updated_by' => auth()->id()]
                    );
                }
            }

            // 1.6 Webzine & Literary Magazine Customizer Settings
            if ($request->has('webzine_hero_title') || $request->has('webzine_hero_badge')) {
                $webzineData = [
                    'hero_badge'        => $request->input('webzine_hero_badge'),
                    'hero_title'        => $request->input('webzine_hero_title'),
                    'hero_subtitle'     => $request->input('webzine_hero_subtitle'),
                    'editor_name'       => $request->input('webzine_editor_name'),
                    'editor_title'      => $request->input('webzine_editor_title'),
                    'editorial_note'    => $request->input('webzine_editorial_note'),
                    'archive_notice'    => $request->input('webzine_archive_notice'),
                    'cta_title'         => $request->input('webzine_cta_title'),
                    'cta_button_text'   => $request->input('webzine_cta_button_text'),
                    'cta_button_url'    => $request->input('webzine_cta_button_url'),
                ];
                $existingWebzine = \App\Support\SiteSetting::webzineCustomizer();
                $finalWebzine = array_merge($existingWebzine, array_filter($webzineData, fn($v) => !is_null($v)));
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'webzine_customizer_settings'],
                    ['value' => $finalWebzine, 'updated_by' => auth()->id()]
                );
            }


            // 2. Handle logo (File or Cropped Base64) & Dimensions
            if ($request->has('site_logo_height')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'site_logo_height'],
                    ['value' => (int) $request->input('site_logo_height', 52), 'updated_by' => auth()->id()]
                );
            }
            if ($request->has('site_logo_width')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'site_logo_width'],
                    ['value' => (int) $request->input('site_logo_width', 220), 'updated_by' => auth()->id()]
                );
            }
            if ($request->has('site_logo_scale')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'site_logo_scale'],
                    ['value' => (int) $request->input('site_logo_scale', 100), 'updated_by' => auth()->id()]
                );
            }
            if ($request->has('site_logo_padding_y')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'site_logo_padding_y'],
                    ['value' => (int) $request->input('site_logo_padding_y', 2), 'updated_by' => auth()->id()]
                );
            }
            if ($request->has('site_logo_padding_x')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'site_logo_padding_x'],
                    ['value' => (int) $request->input('site_logo_padding_x', 0), 'updated_by' => auth()->id()]
                );
            }
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'site_logo_show_text'],
                ['value' => $request->boolean('site_logo_show_text'), 'updated_by' => auth()->id()]
            );

            if ($request->boolean('remove_site_logo')) {
                AdminDashboardSetting::where('key', 'site_logo')->delete();
            } else {
                $savedLogo = $this->saveImageOrBase64($request->file('site_logo'), $request->input('site_logo_cropped'), 'images/brand');
                if ($savedLogo) {
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'site_logo'],
                        ['value' => $savedLogo, 'updated_by' => auth()->id()]
                    );
                }
            }

            // 2.1 Handle Login Page Dedicated Logo
            if ($request->boolean('remove_site_login_logo')) {
                AdminDashboardSetting::where('key', 'site_login_logo')->delete();
            } else {
                $savedLoginLogo = $this->saveImageOrBase64($request->file('site_login_logo'), $request->input('site_login_logo_cropped'), 'images/brand');
                if ($savedLoginLogo) {
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'site_login_logo'],
                        ['value' => $savedLoginLogo, 'updated_by' => auth()->id()]
                    );
                }
            }

            // 3. Handle favicon
            if ($request->boolean('remove_site_favicon')) {
                AdminDashboardSetting::where('key', 'site_favicon')->delete();
            } else {
                $savedFavicon = $this->saveImageOrBase64($request->file('site_favicon'), $request->input('site_favicon_cropped'), 'images/brand');
                if ($savedFavicon) {
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'site_favicon'],
                        ['value' => $savedFavicon, 'updated_by' => auth()->id()]
                    );
                }
            }

            // 4. Handle Banner 1
            $savedBanner1 = $this->saveImageOrBase64($request->file('banner_1'), $request->input('banner_1_cropped'), 'images/banners');
            if ($savedBanner1) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'home_banner_1'],
                    ['value' => $savedBanner1, 'updated_by' => auth()->id()]
                );
            }

            // 5. Handle Banner 2
            $savedBanner2 = $this->saveImageOrBase64($request->file('banner_2'), $request->input('banner_2_cropped'), 'images/banners');
            if ($savedBanner2) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'home_banner_2'],
                    ['value' => $savedBanner2, 'updated_by' => auth()->id()]
                );
            }

            // 5.0 Handle Hero Slides JSON
            if ($request->filled('home_hero_slides')) {
                $slides = json_decode($request->input('home_hero_slides'), true);
                if (is_array($slides)) {
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'home_hero_slides'],
                        ['value' => $slides, 'updated_by' => auth()->id()]
                    );
                }
            }

            // 5.0.1 Handle Header Menu Items JSON
            if ($request->filled('header_menu_items')) {
                $menuItems = json_decode($request->input('header_menu_items'), true);
                if (is_array($menuItems)) {
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'header_menu_items'],
                        ['value' => $menuItems, 'updated_by' => auth()->id()]
                    );
                }
            }

            // 5.1 Handle Blog / Social OG Share Banner
            if ($request->boolean('remove_blog_og_banner')) {
                AdminDashboardSetting::where('key', 'blog_og_banner')->delete();
            } else {
                $savedBlogOg = $this->saveImageOrBase64($request->file('blog_og_banner'), $request->input('blog_og_banner_cropped'), 'images/banners');
                if ($savedBlogOg) {
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'blog_og_banner'],
                        ['value' => $savedBlogOg, 'updated_by' => auth()->id()]
                    );
                }
            }

            // 6. System Notice
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'system_notice'],
                [
                    'value' => [
                        'text'   => $request->input('notice_text', ''),
                        'active' => $request->boolean('notice_active'),
                        'type'   => $request->input('notice_type', 'info'),
                    ],
                    'updated_by' => auth()->id(),
                ]
            );

            // 7. E-commerce, Coupon & Threshold Offer Settings
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'ecommerce_settings'],
                [
                    'value' => array_merge((array) ($before['ecommerce_settings'] ?? []), [
                        'delivery_dhaka'          => $request->float('delivery_dhaka', 50),
                        'delivery_sub'            => $request->float('delivery_sub', 100),
                        'delivery_outside'        => $request->float('delivery_outside', 120),
                        'gift_wrap_fee'           => $request->float('gift_wrap_fee', 20),
                        'free_delivery_threshold' => $request->float('free_delivery_threshold', 1500),
                        'helpline_phone'          => $request->input('helpline_phone', '01726976982'),
                        'helpline_email'          => $request->input('helpline_email', 'ideapbd@gmail.com'),
                        'whatsapp_number'         => $request->input('whatsapp_number', '01726976982'),
                        'bkash_number'            => $request->input('bkash_number', '01558712810'),
                        'nagad_number'            => $request->input('nagad_number', '01558712810'),
                        'rocket_number'           => $request->input('rocket_number', $before['ecommerce_settings']['rocket_number'] ?? '01558712810'),
                        'payment_instruction'     => $request->input('payment_instruction', 'বিকাশ বা নগদ থেকে উল্লেখিত নম্বরে সেন্ড মানি করে TrxID ও পেমেন্ট নম্বর দিন।'),
                        // Coupon Settings
                        'coupon_enabled'          => $request->boolean('coupon_enabled'),
                        'coupon_code'             => strtoupper(trim($request->input('coupon_code', 'IDEA2026'))),
                        'coupon_type'             => $request->input('coupon_type', 'percent'),
                        'coupon_discount'         => $request->float('coupon_discount', 10),
                        'coupon_min_order'        => $request->float('coupon_min_order', 500),
                        'coupon_description'      => $request->input('coupon_description', 'বিশেষ কুপন ছাড়'),
                        // Threshold Offer Settings
                        'threshold_offer_enabled' => $request->boolean('threshold_offer_enabled'),
                        'threshold_offer_amount'  => $request->float('threshold_offer_amount', 1000),
                        'threshold_offer_type'    => $request->input('threshold_offer_type', 'free_delivery'),
                        'threshold_offer_discount'=> $request->float('threshold_offer_discount', 100),
                        'threshold_offer_title'   => $request->input('threshold_offer_title', '৳১০০০+ অর্ডারে ফ্রি ডেলিভারি ও বিশেষ উপহার!'),
                    ]),
                    'updated_by' => auth()->id(),
                ]
            );

            // 8. Theme Settings
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'theme_settings'],
                [
                    'value' => array_merge((array) ($before['theme_settings'] ?? []), [
                        'primary_color'   => $request->input('primary_color', '#0066cc'),
                        'secondary_color' => $request->input('secondary_color', '#0099ff'),
                        'default_mode'    => $request->input('default_mode', 'light'),
                    ]),
                    'updated_by' => auth()->id(),
                ]
            );

            // 9. Invoice & Sender Settings
            // Saved as a MERGE: the accounting screen keeps the logo, bank details, QR codes, receipt styling and document titles
            // in the same record, and this tab must never wipe them.
            $existingInvoice = AdminDashboardSetting::where('key', 'invoice_settings')->first()?->value;
            if (is_string($existingInvoice)) { $existingInvoice = json_decode($existingInvoice, true); }
            $existingInvoice = is_array($existingInvoice) ? $existingInvoice : [];
            $pick = fn (string $field, string $key, $default) => $request->has($field) ? (string) $request->input($field) : ($existingInvoice[$key] ?? $default);
            $senderFields = [
                'sender_name' => $pick('invoice_sender_name', 'sender_name', 'আইডিয়া প্রকাশন'),
                'sender_address' => $pick('invoice_sender_address', 'sender_address', 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ'),
                'sender_phone' => $pick('invoice_sender_phone', 'sender_phone', '01558712870'),
                'sender_email' => $pick('invoice_sender_email', 'sender_email', 'ideapbd@gmail.com'),
                'sender_website' => $pick('invoice_sender_website', 'sender_website', 'www.ideaabd.com'),
                'invoice_title' => $pick('invoice_title', 'invoice_title', 'ক্যাশ মেমো / ইনভয়েস'),
                'invoice_terms' => $pick('invoice_terms', 'invoice_terms', 'পণ্য গ্রহণের সময় অনুগ্রহ করে চেক করে নিন। কোনো ত্রুটি থাকলে ডেলিভারি ম্যানের সামনেই হেল্পলাইনে যোগাযোগ করুন।'),
                'invoice_footer' => $pick('invoice_footer', 'invoice_footer', 'বই পড়ার আনন্দ ছড়িয়ে পড়ুক সবার মাঝে। ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!'),
            ];
            $mergedInvoice = array_merge($existingInvoice, $senderFields, [
                // the accounting documents (memo, challan, quotation) read these names: keep both spellings in step
                'business_name' => $senderFields['sender_name'],
                'address'       => $senderFields['sender_address'],
                'phone'         => $senderFields['sender_phone'],
                'email'         => $senderFields['sender_email'],
            ]);
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'invoice_settings'],
                ['value' => $mergedInvoice, 'updated_by' => auth()->id()]
            );

            // 9.1 Editorial & Publisher Settings
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'editorial_publisher'],
                ['value' => $request->input('editorial_publisher', 'আইডিয়া প্রকাশন'), 'updated_by' => auth()->id()]
            );
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'editorial_editor'],
                ['value' => $request->input('editorial_editor', 'সাকিল মাসুদ'), 'updated_by' => auth()->id()]
            );

            // Additional dynamic board members (role, name)
            $boardRoles = $request->input('board_role', []);
            $boardNames = $request->input('board_name', []);
            $boardMembers = [];
            if (is_array($boardRoles) && is_array($boardNames)) {
                foreach ($boardRoles as $idx => $r) {
                    $r = trim((string)$r);
                    $n = trim((string)($boardNames[$idx] ?? ''));
                    if ($r !== '' && $n !== '') {
                        $boardMembers[] = ['role' => $r, 'name' => $n];
                    }
                }
            }
            // 9.2 Header Navigation Menu Items
            if ($request->has('header_menu_items')) {
                $rawNav = $request->input('header_menu_items');
                if (is_string($rawNav)) {
                    $rawNav = json_decode($rawNav, true) ?: [];
                }
                if (is_array($rawNav)) {
                    // Filter and sanitize items
                    $sanitizedNav = [];
                    foreach ($rawNav as $idx => $m) {
                        if (!empty($m['label'])) {
                            $sanitizedNav[] = [
                                'id'        => (string) ($m['id'] ?? ($idx + 1)),
                                'label'     => trim((string) $m['label']),
                                'route'     => trim((string) ($m['route'] ?? '')),
                                'url'       => trim((string) ($m['url'] ?? '')),
                                'icon'      => trim((string) ($m['icon'] ?? 'link')),
                                'active'    => trim((string) ($m['active'] ?? '')),
                                'is_active' => filter_var($m['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                                'target'    => in_array($m['target'] ?? '_self', ['_self', '_blank']) ? $m['target'] : '_self',
                                'badge'     => trim((string) ($m['badge'] ?? '')),
                            ];
                        }
                    }
                    AdminDashboardSetting::updateOrCreate(
                        ['key' => 'header_menu_items'],
                        ['value' => $sanitizedNav, 'updated_by' => auth()->id()]
                    );
                }
            } elseif ($request->boolean('reset_header_menu')) {
                AdminDashboardSetting::where('key', 'header_menu_items')->delete();
            }

            // 10. Payment Gateways
            if ($request->has('gateways')) {
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'payment_gateways'],
                    [
                        'value' => array_replace_recursive((array) ($before['payment_gateways'] ?? []), (array) $request->input('gateways', [])),
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            // 11. Maintenance Mode
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'maintenance_mode'],
                [
                    'value' => [
                        'enabled' => $request->boolean('maintenance_mode'),
                        'reason'  => $request->input('maintenance_reason', ''),
                    ],
                    'updated_by' => auth()->id(),
                ]
            );

            $this->recordSettingsHistory($before, 'Saved');

            \App\Support\SiteSetting::clearCache();
            try {
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                \Illuminate\Support\Facades\Artisan::call('view:clear');
            } catch (\Throwable $e) {}

            $this->accessService->log('update_settings', 'সিস্টেম সেটিংস ও ব্র্যান্ডিং সফলভাবে আপডেট করা হয়েছে');
        }

        $message = 'Settings saved.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'version' => $this->settingsVersion(), 'reload' => $request->hasFile('site_logo') || $request->hasFile('site_favicon') || $request->hasFile('banner_1') || $request->hasFile('banner_2') || $request->hasFile('blog_og_banner') || $request->filled('site_logo_cropped') || $request->filled('site_favicon_cropped') || $request->filled('banner_1_cropped') || $request->filled('banner_2_cropped')]);
        }

        return back()->with('success', $message);
    }

    /**
     * Save uploaded file or decode base64 cropped string into storage.
     */
    private function saveImageOrBase64(?\Illuminate\Http\UploadedFile $file, ?string $base64Data, string $folder): ?string
    {
        if ($base64Data && str_starts_with($base64Data, 'data:image/')) {
            $path = \App\Services\ImageOptimizerService::convertBase64AndStore($base64Data, $folder, 'public', 85, 1920, 1080);
            if ($path) {
                return 'storage/' . $path;
            }
        }

        if ($file && $file->isValid()) {
            $path = \App\Services\ImageOptimizerService::convertAndStore($file, $folder, 'public', 85, 1920, 1080);
            return 'storage/' . $path;
        }

        return null;
    }

    /** Settings keys that never take part in snapshots, history or backups. */
    private const SETTINGS_HISTORY_KEY = 'settings_history';

    /** Which settings tab a stored key belongs to (for the history list). */
    private function settingsArea(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'site_') || str_starts_with($key, 'home_banner') || str_starts_with($key, 'blog_og') || str_contains($key, 'designer') => 'Brand',
            $key === 'home_hero_slides' => 'Hero',
            $key === 'header_menu_items' => 'Menu',
            $key === 'theme_settings' => 'Theme',
            $key === 'system_notice' => 'Notice',
            $key === 'ecommerce_settings' => 'Shop',
            $key === 'invoice_settings' => 'Invoice',
            $key === 'payment_gateways' => 'Payment',
            str_starts_with($key, 'editorial') || str_starts_with($key, 'ideapatra') => 'Editorial',
            $key === 'about_page_settings' => 'About',
            str_starts_with($key, 'contact') || $key === 'whatsapp_number' => 'Contact',
            $key === 'webzine_customizer_settings' => 'Webzine',
            str_starts_with($key, 'terms') => 'Terms',
            $key === 'maintenance_mode' => 'System',
            default => 'Other',
        };
    }

    /** @return array<string, mixed> every stored setting except the history itself */
    private function settingsSnapshot(): array
    {
        return AdminDashboardSetting::query()
            ->where('key', '!=', self::SETTINGS_HISTORY_KEY)
            ->get(['key', 'value'])
            ->mapWithKeys(fn ($r) => [$r->key => $r->value])
            ->all();
    }

    /** Changes whenever any real setting changes — used for the optimistic lock. */
    private function settingsVersion(): string
    {
        return (string) AdminDashboardSetting::where('key', '!=', self::SETTINGS_HISTORY_KEY)->max('updated_at');
    }

    private function latestSettingsEditor(): ?string
    {
        $row = AdminDashboardSetting::where('key', '!=', self::SETTINGS_HISTORY_KEY)->whereNotNull('updated_by')->latest('updated_at')->with('updatedBy:id,name')->first();

        return $row?->updatedBy?->name;
    }

    /**
     * The same fact is edited in two tabs (helpline <-> Contact page, bKash/Nagad number <-> Payment).
     * Whichever field the admin actually changed wins and is written to both places, so the two
     * copies can never drift apart. Ties go to the Contact / Payment tab.
     */
    private function syncDuplicateFields(Request $request, array $before): void
    {
        $pick = function (?string $primary, ?string $secondary, ?string $storedPrimary, ?string $storedSecondary): ?string {
            $primary = trim((string) $primary);
            $secondary = trim((string) $secondary);
            if ($primary !== '' && $primary !== trim((string) $storedPrimary)) {
                return $primary;
            }
            if ($secondary !== '' && $secondary !== trim((string) $storedSecondary)) {
                return $secondary;
            }

            return $primary !== '' ? $primary : ($secondary !== '' ? $secondary : null);
        };

        $contact = (array) ($before['contact_page_settings'] ?? []);
        $ecom = (array) ($before['ecommerce_settings'] ?? []);
        $merge = [];

        foreach ([
            ['contact_phone', 'helpline_phone', 'helpline_phone'],
            ['contact_whatsapp', 'whatsapp_number', 'whatsapp_number'],
            ['contact_email', 'helpline_email', 'helpline_email'],
        ] as [$contactField, $shopField, $storeKey]) {
            if ($request->has($contactField) || $request->has($shopField)) {
                $v = $pick($request->input($contactField), $request->input($shopField), $contact[$storeKey] ?? null, $ecom[$shopField] ?? null);
                if ($v !== null) {
                    $merge[$contactField] = $v;
                    $merge[$shopField] = $v;
                }
            }
        }

        $gateways = (array) $request->input('gateways', []);
        $storedGw = (array) ($before['payment_gateways'] ?? []);
        foreach (['bkash', 'nagad', 'rocket'] as $gw) {
            $field = $gw . '_number';
            if ($request->has($field) || isset($gateways[$gw]['number'])) {
                $v = $pick($gateways[$gw]['number'] ?? null, $request->input($field), $storedGw[$gw]['number'] ?? null, $ecom[$field] ?? null);
                if ($v !== null) {
                    $merge[$field] = $v;
                    if (isset($gateways[$gw]) || $request->has('gateways')) {
                        $gateways[$gw]['number'] = $v;
                    }
                }
            }
        }
        if ($gateways !== []) {
            $merge['gateways'] = $gateways;
        }

        if ($merge) {
            $request->merge($merge);
        }
    }

    /** Store what changed (old values of the changed keys) so it can be undone. Keeps the last 15. */
    private function recordSettingsHistory(array $before, string $label): void
    {
        $after = $this->settingsSnapshot();
        $old = [];
        foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $key) {
            if (json_encode($before[$key] ?? null) !== json_encode($after[$key] ?? null)) {
                $old[$key] = $before[$key] ?? null;
            }
        }
        if (! $old) {
            return;
        }

        $entry = [
            'id'    => substr(md5(uniqid('', true)), 0, 8),
            'at'    => now()->toIso8601String(),
            'by'    => auth()->user()?->name ?? 'System',
            'label' => $label,
            'keys'  => array_keys($old),
            'areas' => array_values(array_unique(array_map(fn ($k) => $this->settingsArea($k), array_keys($old)))),
            'old'   => strlen((string) json_encode($old)) <= 400000 ? $old : null, // too large -> listed but not restorable
        ];

        $row = AdminDashboardSetting::where('key', self::SETTINGS_HISTORY_KEY)->first();
        $list = array_slice(array_merge([$entry], (array) ($row?->value ?? [])), 0, 15);
        AdminDashboardSetting::updateOrCreate(['key' => self::SETTINGS_HISTORY_KEY], ['value' => $list, 'updated_by' => auth()->id()]);
    }

    /** Recent changes with who/when/where, newest first. */
    public function settingsHistory(): \Illuminate\Http\JsonResponse
    {
        $row = Schema::hasTable('admin_dashboard_settings') ? AdminDashboardSetting::where('key', self::SETTINGS_HISTORY_KEY)->first() : null;

        $items = collect((array) ($row?->value ?? []))->map(function ($e) {
            $at = \Carbon\Carbon::parse($e['at']);

            return [
                'id'          => $e['id'],
                'label'       => $e['label'] ?? 'Saved',
                'by'          => $e['by'] ?? '—',
                'ago'         => $at->locale('en')->diffForHumans(),
                'when'        => $at->format('d M Y, h:i A'),
                'areas'       => $e['areas'] ?? [],
                'count'       => count($e['keys'] ?? []),
                'restorable'  => is_array($e['old'] ?? null),
            ];
        })->values();

        return response()->json(['items' => $items, 'version' => $this->settingsVersion()]);
    }

    /** Put the old values of one history entry back (and record that as a new entry, so it can be undone too). */
    public function restoreSettings(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        abort_unless(Schema::hasTable('admin_dashboard_settings'), 404);

        $row = AdminDashboardSetting::where('key', self::SETTINGS_HISTORY_KEY)->first();
        $entry = collect((array) ($row?->value ?? []))->firstWhere('id', $id);

        if (! $entry) {
            return response()->json(['success' => false, 'message' => 'That change is no longer in the history.'], 404);
        }
        if (! is_array($entry['old'] ?? null)) {
            return response()->json(['success' => false, 'message' => 'This change was too large to be restored.'], 422);
        }

        $before = $this->settingsSnapshot();
        foreach ($entry['old'] as $key => $value) {
            if (! is_string($key) || ! preg_match('/^[a-z0-9_]{1,100}$/', $key) || $key === self::SETTINGS_HISTORY_KEY) {
                continue;
            }
            if ($value === null) {
                AdminDashboardSetting::where('key', $key)->delete();
            } else {
                AdminDashboardSetting::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => auth()->id()]);
            }
        }

        $this->recordSettingsHistory($before, 'Restored ' . $id);
        \App\Support\SiteSetting::clearCache();
        $this->accessService->log('restore_settings', "Settings restored from history entry {$id}");

        return response()->json(['success' => true, 'message' => 'Previous settings restored.', 'version' => $this->settingsVersion(), 'reload' => true]);
    }

    /**
     * Clear caches. type: all | views | config | routes | app (AJAX or form).
     */
    public function clearCache(Request $request)
    {
        $type = (string) $request->input('type', 'all');
        $map = [
            'all'    => ['view:clear', 'cache:clear', 'config:clear', 'route:clear'],
            'views'  => ['view:clear'],
            'config' => ['config:clear'],
            'routes' => ['route:clear'],
            'app'    => ['cache:clear'],
        ];
        $commands = $map[$type] ?? $map['all'];

        try {
            \App\Support\SiteSetting::clearCache();
            foreach ($commands as $cmd) {
                \Illuminate\Support\Facades\Artisan::call($cmd);
            }
            $this->accessService->log('clear_cache', 'Cache cleared: ' . (isset($map[$type]) ? $type : 'all'));
            $message = ($type === 'all' || ! isset($map[$type]) ? 'All caches' : ucfirst($type) . ' cache') . ' cleared.';

            return $request->expectsJson()
                ? response()->json(['success' => true, 'message' => $message])
                : back()->with('success', $message);
        } catch (\Throwable $e) {
            report($e);

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Could not clear the cache: ' . $e->getMessage()], 500)
                : back()->with('error', 'Could not clear the cache: ' . $e->getMessage());
        }
    }

    /**
     * Live system health: every check returns key, label, state (ok|warn|bad), detail.
     */
    public function health(): \Illuminate\Http\JsonResponse
    {
        $isProd = app()->environment('production');
        $checks = [];
        $add = function (string $key, string $label, string $state, string $detail) use (&$checks) {
            $checks[] = compact('key', 'label', 'state', 'detail');
        };

        $add('debug', 'Debug', ! config('app.debug') ? 'ok' : ($isProd ? 'bad' : 'warn'),
            ! config('app.debug') ? 'Off' : ($isProd ? 'On in production — turn off APP_DEBUG' : 'On (fine locally, off on the live site)'));
        $add('env', 'Environment', 'ok', strtoupper(app()->environment()));
        $secure = (bool) config('session.secure') || request()->isSecure();
        $add('https', 'HTTPS', $secure ? 'ok' : ($isProd ? 'bad' : 'warn'), $secure ? 'Secure connection and cookies' : 'Set SESSION_SECURE_COOKIE=true and serve over HTTPS');
        $add('key', 'Key', config('app.key') ? 'ok' : 'bad', config('app.key') ? 'Set' : 'Missing — run php artisan key:generate');

        // Database round-trip
        try {
            $t = microtime(true);
            \Illuminate\Support\Facades\DB::select('select 1');
            $ms = round((microtime(true) - $t) * 1000, 1);
            $add('db', 'Database', $ms < 150 ? 'ok' : 'warn', strtoupper(config('database.default')) . " · {$ms} ms");
        } catch (\Throwable $e) {
            $add('db', 'Database', 'bad', 'Connection failed');
        }

        // Cache read/write
        try {
            $k = 'health_' . bin2hex(random_bytes(4));
            \Illuminate\Support\Facades\Cache::put($k, 1, 10);
            $okCache = \Illuminate\Support\Facades\Cache::get($k) === 1;
            \Illuminate\Support\Facades\Cache::forget($k);
            $add('cache', 'Cache', $okCache ? 'ok' : 'bad', config('cache.default') . ($okCache ? ' · read/write ok' : ' · not working'));
        } catch (\Throwable $e) {
            $add('cache', 'Cache', 'bad', 'Failed');
        }

        $link = is_link(public_path('storage')) || is_dir(public_path('storage'));
        $add('storage', 'Storage', $link ? 'ok' : 'bad', $link ? 'public/storage connected' : 'Missing — run php artisan storage:link');

        $unwritable = array_filter([storage_path(), storage_path('logs'), storage_path('framework/cache'), base_path('bootstrap/cache')], fn ($p) => ! is_writable($p));
        $add('writable', 'Writable', $unwritable ? 'bad' : 'ok', $unwritable ? 'Not writable: ' . implode(', ', array_map('basename', $unwritable)) : 'storage and bootstrap/cache');

        $free = @disk_free_space(base_path());
        $total = @disk_total_space(base_path());
        if ($free && $total) {
            $pct = round($free / $total * 100);
            $add('disk', 'Disk', $pct >= 15 ? 'ok' : ($pct >= 5 ? 'warn' : 'bad'), $pct . '% free (' . round($free / 1073741824, 1) . ' GB)');
        }

        $mailer = config('mail.default');
        $add('mail', 'Mail', in_array($mailer, ['log', 'array'], true) ? 'warn' : 'ok', in_array($mailer, ['log', 'array'], true) ? "Driver '{$mailer}' — emails are not delivered" : strtoupper($mailer));
        $queue = config('queue.default');
        $add('queue', 'Queue', $queue === 'sync' ? 'warn' : 'ok', $queue === 'sync' ? 'Sync — jobs run during the request' : $queue);

        $add('php', 'PHP', version_compare(PHP_VERSION, '8.2.0', '>=') ? 'ok' : 'warn', PHP_VERSION . ' · memory ' . ini_get('memory_limit'));

        $maint = \App\Support\SiteSetting::get('maintenance_mode');
        $on = is_array($maint) && ! empty($maint['enabled']);
        $add('maintenance', 'Maintenance', $on ? 'warn' : 'ok', $on ? 'ON — visitors see the maintenance page' : 'Off');

        $weights = ['ok' => 1, 'warn' => .5, 'bad' => 0];
        $score = (int) round(array_sum(array_map(fn ($c) => $weights[$c['state']], $checks)) / max(1, count($checks)) * 100);

        return response()->json([
            'checks' => $checks,
            'score'  => $score,
            'meta'   => [
                'Laravel' => app()->version(),
                'PHP'     => PHP_VERSION,
                'OS'      => PHP_OS,
                'Time'    => now()->format('d M Y, h:i A'),
                'Zone'    => config('app.timezone'),
            ],
        ]);
    }

    /**
     * Download every stored setting as one JSON file (backup before risky edits).
     */
    public function exportSettings()
    {
        abort_unless(Schema::hasTable('admin_dashboard_settings'), 404);

        $rows = AdminDashboardSetting::query()->where('key', '!=', 'settings_history')->orderBy('key')->get(['key', 'value'])->pluck('value', 'key')->all();
        $this->accessService->log('export_settings', 'Settings exported (' . count($rows) . ' keys)');

        return response()->streamDownload(function () use ($rows) {
            echo json_encode(['app' => config('app.name'), 'exported_at' => now()->toIso8601String(), 'settings' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, 'settings-' . now()->format('Ymd-His') . '.json', ['Content-Type' => 'application/json']);
    }

    /**
     * Restore settings from a file produced by exportSettings().
     */
    public function importSettings(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['file' => 'required|file|max:2048']);
        abort_unless(Schema::hasTable('admin_dashboard_settings'), 404);

        $data = json_decode((string) file_get_contents($request->file('file')->getRealPath()), true);
        $settings = is_array($data) ? ($data['settings'] ?? null) : null;

        if (! is_array($settings) || $settings === []) {
            return response()->json(['success' => false, 'message' => 'This is not a valid settings backup.'], 422);
        }

        $count = 0;
        foreach ($settings as $key => $value) {
            if (! is_string($key) || $key === 'settings_history' || ! preg_match('/^[a-z0-9_]{1,100}$/', $key)) {
                continue;
            }
            if (strlen(json_encode($value)) > 300000) {
                continue;
            }
            AdminDashboardSetting::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => auth()->id()]);
            $count++;
        }

        \App\Support\SiteSetting::clearCache();
        $this->accessService->log('import_settings', "Settings imported ({$count} keys)");

        return response()->json(['success' => true, 'message' => "{$count} settings restored.", 'count' => $count]);
    }

    /**
     * AJAX Endpoint: Live Dynamic Theme Customizer Update.
     */
    public function updateTheme(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'primary_color'   => ['nullable', 'string', 'max:50'],
            'secondary_color' => ['nullable', 'string', 'max:50'],
            'accent_color'    => ['nullable', 'string', 'max:50'],
            'default_mode'    => ['nullable', 'string', 'in:light,dark,auto'],
            'sidebar_theme'   => ['nullable', 'string', 'max:50'],
            'font_family'     => ['nullable', 'string', 'max:100'],
            'border_radius'   => ['nullable', 'string', 'max:50'],
            'card_style'      => ['nullable', 'string', 'max:50'],
            'save_global'     => ['nullable', 'boolean'],
        ]);

        $themeData = [
            'primary_color'   => $validated['primary_color'] ?? '#0066cc',
            'secondary_color' => $validated['secondary_color'] ?? '#0099ff',
            'accent_color'    => $validated['accent_color'] ?? '#ff6b35',
            'default_mode'    => $validated['default_mode'] ?? 'light',
            'sidebar_theme'   => $validated['sidebar_theme'] ?? 'theme-deep-navy',
            'font_family'     => $validated['font_family'] ?? 'Hind Siliguri',
            'border_radius'   => $validated['border_radius'] ?? 'rounded-modern',
            'card_style'      => $validated['card_style'] ?? 'elevated',
        ];

        // 1. Save user personal preferences
        $user = auth()->user();
        if ($user) {
            $regData = $user->reg_data ?? [];
            $preferences = $regData['preferences'] ?? [];
            $preferences['theme'] = $themeData['default_mode'];
            $preferences['primary_color'] = $themeData['primary_color'];
            $preferences['secondary_color'] = $themeData['secondary_color'];
            $preferences['sidebar_theme'] = $themeData['sidebar_theme'];
            $preferences['font_family'] = $themeData['font_family'];
            $regData['preferences'] = $preferences;
            $user->reg_data = $regData;
            $user->save();
        }

        // 2. If requested to save as global default (Admin only)
        if ($request->boolean('save_global') && $user && $user->isAdmin()) {
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'theme_settings'],
                [
                    'value'      => $themeData,
                    'updated_by' => $user->id,
                ]
            );
            \App\Support\SiteSetting::clearCache();
            $this->accessService->log('update_theme', 'ড্যাশবোর্ড গ্লোবাল থিম সেটিংস সফলভাবে আপডেট করা হয়েছে');
        }

        return response()->json([
            'success' => true,
            'message' => 'থিম সেটিংস সফলভাবে সংরক্ষিত ও প্রয়োগ করা হয়েছে!',
            'theme'   => $themeData,
        ]);
    }

    /**
     * AJAX Endpoint: Reset Theme to Factory Default.
     */
    public function resetTheme(Request $request): \Illuminate\Http\JsonResponse
    {
        $defaultTheme = [
            'primary_color'   => '#0066cc',
            'secondary_color' => '#0099ff',
            'accent_color'    => '#ff6b35',
            'default_mode'    => 'light',
            'sidebar_theme'   => 'theme-deep-navy',
            'font_family'     => 'Hind Siliguri',
            'border_radius'   => 'rounded-modern',
            'card_style'      => 'elevated',
        ];

        $user = auth()->user();
        if ($user) {
            $regData = $user->reg_data ?? [];
            $preferences = $regData['preferences'] ?? [];
            $preferences['theme'] = 'auto';
            $preferences['primary_color'] = '#0066cc';
            $preferences['secondary_color'] = '#0099ff';
            $preferences['sidebar_theme'] = 'theme-deep-navy';
            $preferences['font_family'] = 'Hind Siliguri';
            $regData['preferences'] = $preferences;
            $user->reg_data = $regData;
            $user->save();
        }

        if ($request->boolean('reset_global') && $user && $user->isAdmin()) {
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'theme_settings'],
                [
                    'value'      => $defaultTheme,
                    'updated_by' => $user->id,
                ]
            );
            \App\Support\SiteSetting::clearCache();
        }

        return response()->json([
            'success' => true,
            'message' => 'থিম ডিফল্ট অবস্থায় ফিরিয়ে আনা হয়েছে!',
            'theme'   => $defaultTheme,
        ]);
    }
}

