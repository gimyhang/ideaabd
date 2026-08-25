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

class AdminAccessController extends Controller
{
    public function __construct(private readonly AdminAccessService $accessService)
    {
    }

    /**
     * View and manage role permissions matrix.
     */
    public function rolesPermissions(): View
    {
        $permissions = Schema::hasTable('admin_permissions')
            ? AdminPermission::all()->groupBy('module')
            : collect();

        $rolePermissions = [];
        if (Schema::hasTable('role_has_permissions')) {
            $raw = DB::table('role_has_permissions')->get();
            foreach ($raw as $row) {
                $rolePermissions[$row->role][] = $row->permission_id;
            }
        }

        $roles = [
            'admin'     => 'সাইট অ্যাডমিন (Super Admin)',
            'sub_admin' => 'সাব-অ্যাডমিন (Sub Admin)',
            'manager'   => 'ম্যানেজার (Manager)',
            'seller'    => 'সেলার (Seller)',
        ];

        return view('admin.roles-permissions', compact('permissions', 'rolePermissions', 'roles'));
    }

    /**
     * Update permissions assigned to roles.
     */
    public function updatePermissions(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => 'nullable|array',
        ]);

        if (! Schema::hasTable('role_has_permissions')) {
            return back()->with('error', 'পারমিশন টেবিল মাইগ্রেট করা হয়নি।');
        }

        DB::transaction(function () use ($data) {
            DB::table('role_has_permissions')->truncate();

            $permissionsByRole = $data['permissions'] ?? [];
            foreach ($permissionsByRole as $role => $permIds) {
                foreach ((array) $permIds as $permId) {
                    DB::table('role_has_permissions')->insert([
                        'role'          => $role,
                        'permission_id' => (int) $permId,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        });

        $this->accessService->log('update_permissions', 'অ্যাডমিন রোল পারমিশন ম্যাট্রিক্স আপডেট করা হয়েছে');

        return back()->with('success', 'রোল ও পারমিশন সফলভাবে আপডেট করা হয়েছে!');
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

        return view('admin.system-settings', compact(
            'settings', 'noticeSetting', 'maintSetting', 'ecomSetting', 'themeSetting', 'invoiceSetting', 'paymentGateways', 'diagnostics'
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

            // 2. Handle logo (File or Cropped Base64)
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
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'editorial_board'],
                ['value' => $boardMembers, 'updated_by' => auth()->id()]
            );

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
            @list($type, $data) = explode(';', $base64Data);
            @list(, $data) = explode(',', $data);
            $decoded = base64_decode($data);
            if ($decoded !== false) {
                $ext = 'png';
                if (str_contains($type, 'jpeg') || str_contains($type, 'jpg')) $ext = 'jpg';
                elseif (str_contains($type, 'webp')) $ext = 'webp';
                
                $filename = $folder . '/' . uniqid('crop_', true) . '.' . $ext;
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
                return 'storage/' . $filename;
            }
        }

        if ($file && $file->isValid()) {
            $path = $file->store($folder, 'public');
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
