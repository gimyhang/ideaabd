<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Map of admin route names to required granular permissions.
     */
    public const ROUTE_PERMISSION_MAP = [
        // Dashboard & Metrics
        'admin.dashboard'                          => 'dashboard.view',
        'admin.index'                              => 'dashboard.view',
        
        // Catalog & Books
        'admin.books'                              => 'catalog.view',
        'admin.books.sync-serials'                 => 'catalog.stock_update',
        'admin.books.generate-serial'              => 'catalog.stock_update',
        'admin.books.print-labels'                 => 'catalog.view',
        'admin.books.toggle-status'                => 'catalog.edit',
        'admin.books.approve'                      => 'catalog.edit',
        'admin.books.reject'                       => 'catalog.edit',
        'admin.books.quick-stock'                  => 'catalog.stock_update',
        'admin.books.quick-update'                 => 'catalog.edit',
        'admin.categories'                         => 'catalog.view',
        'admin.authors'                            => 'catalog.view',
        'admin.authors.quick-store'                => 'catalog.create',
        'admin.authors.quick-update'               => 'catalog.edit',
        'admin.publishers'                         => 'catalog.view',
        'admin.publishers.quick-store'             => 'catalog.create',
        'admin.publishers.quick-update'            => 'catalog.edit',
        'admin.bundles.index'                      => 'catalog.view',
        'admin.bundles.store'                      => 'catalog.create',
        
        // Orders, POS & Sales
        'admin.orders'                             => 'orders.view',
        'admin.ecommerce-orders'                   => 'orders.view',
        'admin.ecommerce-orders.show'              => 'orders.view',
        'admin.ecommerce-orders.update'            => 'orders.manage',
        'admin.ecommerce-orders.status'            => 'orders.manage',
        'admin.ecommerce-orders.destroy'           => 'orders.cancel_refund',
        'admin.ecommerce-orders.invoice'           => 'orders.invoice_generate',
        'admin.ecommerce-orders.slip'              => 'orders.invoice_generate',
        'admin.pos.index'                          => 'pos.operate',
        'admin.pos.checkout'                       => 'pos.operate',
        'admin.pos.void'                           => 'pos.operate',
        'admin.gateway-reports'                    => 'orders.view',
        'admin.payments.index'                     => 'orders.view',
        
        // Accounting, Invoices & Payroll
        'admin.accounting.index'                   => 'accounting.view',
        'admin.accounting.entries.store'           => 'accounting.ledger_manage',
        'admin.accounting.entries.destroy'         => 'accounting.ledger_manage',
        'admin.accounting.invoices.index'          => 'accounting.view',
        'admin.accounting.invoices.create'         => 'accounting.ledger_manage',
        'admin.accounting.invoices.store'          => 'accounting.ledger_manage',
        'admin.accounting.invoices.show'           => 'accounting.view',
        'admin.accounting.invoices.edit'           => 'accounting.ledger_manage',
        'admin.accounting.invoices.update'         => 'accounting.ledger_manage',
        'admin.accounting.invoices.destroy'        => 'accounting.ledger_manage',
        'admin.accounting.invoices.payments.store' => 'accounting.ledger_manage',
        'admin.accounting.customer-ledger.index'   => 'accounting.view',
        'admin.accounting.customer-ledger.payments.store' => 'accounting.ledger_manage',
        'admin.accounting.reports.index'           => 'accounting.reports',
        'admin.accounting.salary.index'            => 'accounting.salary_disburse',
        'admin.accounting.salary.store'            => 'accounting.salary_disburse',
        'admin.purchases.index'                    => 'purchases.manage',
        'admin.purchases.create'                   => 'purchases.manage',
        'admin.purchases.store'                    => 'purchases.manage',
        'admin.purchases.payments'                 => 'purchases.manage',
        'admin.purchases.payments.store'           => 'purchases.manage',
        'admin.purchases.ledger'                   => 'purchases.manage',
        'admin.purchases.monthly-report'           => 'purchases.manage',
        
        // HR, Staff & Team
        'admin.accounting.employees.index'         => 'staff.view',
        'admin.accounting.employees.store'         => 'staff.manage',
        'admin.accounting.employees.update'        => 'staff.manage',
        'admin.accounting.employees.destroy'       => 'staff.manage',
        'admin.accounting.employees.ledger'        => 'staff.view',
        'admin.accounting.employees.work-logs.index'   => 'staff.worklog_verify',
        'admin.accounting.employees.work-logs.store'   => 'staff.worklog_verify',
        'admin.accounting.employees.work-logs.destroy' => 'staff.worklog_verify',
        'admin.sub-admins.index'                   => 'roles.manage',
        'admin.sub-admins.create'                  => 'roles.manage',
        'admin.sub-admins.store'                   => 'roles.manage',
        
        // Editorial & Content
        'admin.blog'                               => 'editorial.view',
        'admin.blog.details'                       => 'editorial.view',
        'admin.blog.settings.update'               => 'editorial.publish',
        'admin.blog.toggle-status'                 => 'editorial.publish',
        'admin.blog.destroy'                       => 'editorial.publish',
        'admin.author-honorariums.index'           => 'editorial.honorarium',
        'admin.author-honorariums.status'          => 'editorial.honorarium',
        'admin.webzines'                           => 'editorial.view',
        'admin.content.create'                     => 'editorial.create',
        'admin.content.store'                      => 'editorial.create',
        'admin.content.edit'                       => 'editorial.publish',
        'admin.content.approve'                    => 'editorial.publish',
        'admin.content.reject'                     => 'editorial.publish',
        
        // Digital Marketing & Growth
        'admin.visitor-reports'                    => 'marketing.view',
        'admin.affiliates.index'                   => 'marketing.affiliates_manage',
        'admin.affiliates.store'                   => 'marketing.affiliates_manage',
        'admin.affiliates.payout'                  => 'marketing.affiliates_manage',
        'admin.media.index'                        => 'marketing.banners_manage',
        
        // Support & CRM
        'admin.tickets.index'                      => 'support.view',
        'admin.tickets.show'                       => 'support.view',
        'admin.tickets.reply'                      => 'support.reply',
        'admin.tickets.status'                     => 'support.reply',
        'admin.customers'                          => 'support.broadcast',
        'admin.customers.broadcast'                => 'support.broadcast',
        'admin.book-requests.index'                => 'support.view',
        'admin.book-requests.update'               => 'support.reply',
        
        // E-Books & Royalties
        'admin.ebooks'                             => 'ebooks.view',
        'admin.ebooks.settings'                    => 'ebooks.manage',
        'admin.ebooks.toggle-status'               => 'ebooks.manage',
        'admin.ebooks.approve'                     => 'ebooks.manage',
        'admin.ebooks.reject'                      => 'ebooks.manage',
        'admin.subscriptions.index'                => 'ebooks.manage',
        'admin.ebook-sales-report'                 => 'ebooks.view',
        'admin.author-royalties.index'             => 'royalties.manage',
        'admin.author-payouts.index'               => 'royalties.manage',
        'admin.royalty-payout-logs'                => 'royalties.manage',
        
        // Security, Roles & Settings
        'admin.users'                              => 'users.view',
        'admin.registrations.index'                => 'users.manage',
        'admin.registrations.approve'              => 'users.manage',
        'admin.registrations.reject'               => 'users.manage',
        'admin.users.security.index'               => 'security.sessions',
        'admin.roles.index'                        => 'roles.manage',
        'admin.roles.update'                       => 'roles.manage',
        'admin.roles.store'                        => 'roles.manage',
        'admin.roles.edit'                         => 'roles.manage',
        'admin.roles.destroy'                      => 'roles.manage',
        'admin.roles.clone'                        => 'roles.manage',
        'admin.staff.inspector'                    => 'roles.manage',
        'admin.staff.direct-permissions'           => 'roles.manage',
        'admin.staff.toggle-status'                => 'roles.manage',
        'admin.staff.terminate-sessions'           => 'security.sessions',
        'admin.staff.force-password-reset'         => 'users.manage',
        'admin.staff.update-role'                  => 'roles.manage',
        'admin.activity-logs'                      => 'activity_logs.view',
        'admin.audit-logs.index'                   => 'activity_logs.view',
        'admin.system-settings'                    => 'settings.manage',
        'admin.system-settings.update'             => 'settings.manage',
        'admin.system-settings.clear-cache'        => 'settings.manage',
        'admin.cache.manage'                       => 'settings.manage',
        'admin.backup.index'                       => 'settings.manage',
        'admin.currencies.index'                   => 'settings.manage',
        'admin.translations.index'                 => 'editorial.view',
        'admin.communication.index'                => 'support.broadcast',
    ];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 1. Account Active & Status Check
        if (!$user->is_active || (in_array($user->role, ['author', 'seller', 'publisher'], true) && $user->reg_status !== 'approved')) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'আপনার অ্যাকাউন্টটি সাময়িকভাবে স্থগিত বা অনুমোদনের অপেক্ষায় রয়েছে। বিস্তারিত জানতে অ্যাডমিনের সাথে যোগাযোগ করুন।'
            ]);
        }

        // 2. IP Whitelist Check (if configured for staff)
        if (!empty($user->ip_whitelist)) {
            $allowedIps = array_filter(array_map('trim', explode(',', $user->ip_whitelist)));
            $clientIp = $request->ip();
            if (!empty($allowedIps) && !in_array($clientIp, $allowedIps, true) && !in_array('*', $allowedIps, true)) {
                auth()->logout();
                return redirect()->route('login')->withErrors([
                    'email' => "আপনার বর্তমান আইপি ({$clientIp}) থেকে প্রবেশের অনুমতি নেই। সিকিউরিটি রেস্ট্রিকশন সক্রিয় রয়েছে।"
                ]);
            }
        }

        // 3. Super Admin bypass — Full unrestricted access
        if ($user->isAdmin()) {
            return $next($request);
        }

        // 4. Role Membership Evaluation (including dynamic subordinate staff roles)
        $isAdminRouteGroup = in_array(User::ROLE_ADMIN, $roles, true);
        $isSubAdminRouteGroup = in_array(User::ROLE_SUB_ADMIN, $roles, true);
        
        $hasRoleAccess = in_array($user->role, $roles, true);

        // Subordinate administrative staff (custom roles, sub-admins, departmental staff)
        $isAdministrativeStaff = $user->custom_role_id !== null || in_array($user->role, [
            User::ROLE_SUB_ADMIN,
            'operations_manager',
            'chief_editor',
            'digital_marketer',
            'tech_admin',
            'finance_controller',
            'support_agent',
            User::ROLE_SELLER,
        ], true);

        if ($isAdminRouteGroup && $isAdministrativeStaff) {
            $hasRoleAccess = true;
        }

        if (!$hasRoleAccess) {
            abort(403, 'আপনার এই পোর্টালে প্রবেশের অনুমতি নেই।');
        }

        // 5. Dynamic Granular Permission Check for Current Route
        $routeName = $request->route()?->getName();
        if ($routeName && isset(self::ROUTE_PERMISSION_MAP[$routeName])) {
            $requiredPermission = self::ROUTE_PERMISSION_MAP[$routeName];
            if (!$user->hasPermission($requiredPermission)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'অনুমতি নেই: অ্যাডমিন আপনার রোলে এই অ্যাকশন বা ফিচারের অনুমতি বন্ধ রেখেছেন।',
                    ], 403);
                }

                abort(403, "অ্যাক্সেস সংরক্ষিত: অ্যাডমিন আপনার রোলে '{$requiredPermission}' পারমিশনটি বন্ধ রেখেছেন। এই কাজের জন্য অ্যাডমিনের অনুমতি প্রয়োজন।");
            }
        }

        return $next($request);
    }
}
