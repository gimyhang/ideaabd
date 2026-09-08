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
        abort_unless(auth()->user() && auth()->user()->isAdmin(), 403, 'এই সিকিউরিটি ও আইএএম কন্ট্রোল হাবটি শুধুমাত্র একক মূল সুপার অ্যাডমিনের এখতিয়ারাধীন।');

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
     * Update permissions assigned to roles across the full matrix.
     */
    public function updatePermissions(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => 'nullable|array',
        ]);

        $this->accessService->syncMatrix($data['permissions'] ?? []);

        return back()->with('success', 'রোল ও পারমিশন ম্যাট্রিক্স সফলভাবে আপডেট ও সিঙ্ক করা হয়েছে!');
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

        return view('admin.system-settings', compact(
            'settings', 'noticeSetting', 'maintSetting', 'ecomSetting', 'themeSetting', 'invoiceSetting', 'paymentGateways', 'diagnostics', 'headerMenuItems', 'authors'
        ));
    }

    /**
     * Save dashboard settings.
     */
    public function updateSystemSettings(Request $request): RedirectResponse
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
                    'value' => [
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
                        'rocket_number'           => $request->input('rocket_number', '01558712810'),
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
                    ],
                    'updated_by' => auth()->id(),
                ]
            );

            // 8. Theme Settings
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'theme_settings'],
                [
                    'value' => [
                        'primary_color'   => $request->input('primary_color', '#0066cc'),
                        'secondary_color' => $request->input('secondary_color', '#0099ff'),
                        'default_mode'    => $request->input('default_mode', 'light'),
                    ],
                    'updated_by' => auth()->id(),
                ]
            );

            // 9. Invoice & Sender Settings
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'invoice_settings'],
                [
                    'value' => [
                        'sender_name'    => $request->input('invoice_sender_name', 'আইডিয়া প্রকাশন'),
                        'sender_address' => $request->input('invoice_sender_address', 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ'),
                        'sender_phone'   => $request->input('invoice_sender_phone', '01558712870'),
                        'sender_email'   => $request->input('invoice_sender_email', 'ideapbd@gmail.com'),
                        'sender_website' => $request->input('invoice_sender_website', 'www.ideaabd.com'),
                        'invoice_title'  => $request->input('invoice_title', 'ক্যাশ মেমো / ইনভয়েস'),
                        'invoice_terms'  => $request->input('invoice_terms', 'পণ্য গ্রহণের সময় অনুগ্রহ করে চেক করে নিন। কোনো ত্রুটি থাকলে ডেলিভারি ম্যানের সামনেই হেল্পলাইনে যোগাযোগ করুন।'),
                        'invoice_footer' => $request->input('invoice_footer', 'বই পড়ার আনন্দ ছড়িয়ে পড়ুক সবার মাঝে। ideaabd-এর সাথে থাকার জন্য ধন্যবাদ!'),
                    ],
                    'updated_by' => auth()->id(),
                ]
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
                        'value' => $request->input('gateways', []),
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

            \App\Support\SiteSetting::clearCache();
            try {
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                \Illuminate\Support\Facades\Artisan::call('view:clear');
            } catch (\Throwable $e) {}

            $this->accessService->log('update_settings', 'সিস্টেম সেটিংস ও ব্র্যান্ডিং সফলভাবে আপডেট করা হয়েছে');
        }

        return back()->with('success', 'সকল সিস্টেম সেটিংস ও ব্র্যান্ডিং ইমেজ সফলভাবে সংরক্ষিত হয়েছে!');
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

    /**
     * Quick Clear Cache action for Admin.
     */
    public function clearCache(Request $request): RedirectResponse
    {
        try {
            \App\Support\SiteSetting::clearCache();
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            
            $this->accessService->log('clear_cache', 'অ্যাডমিন ড্যাশবোর্ড থেকে সিস্টেম ক্যাশ ও ভিউ ক্যাশ ক্লিয়ার করা হয়েছে');
            return back()->with('success', 'সিস্টেম ক্যাশ, ভিউ ক্যাশ এবং কনফিগারেশন ক্যাশ সফলভাবে ক্লিয়ার করা হয়েছে!');
        } catch (\Throwable $e) {
            return back()->with('error', 'ক্যাশ ক্লিয়ার করতে ত্রুটি হয়েছে: ' . $e->getMessage());
        }
    }
}
