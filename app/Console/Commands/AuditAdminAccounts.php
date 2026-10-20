<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Lists who is an administrator and who might have been one through the old phone/e-mail/id rule.
 * Run it once on the live server after this update: any "was admin before" row that is not you needs a look.
 */
class AuditAdminAccounts extends Command
{
    protected $signature = 'security:audit-admins';

    protected $description = 'List administrator accounts and accounts that only looked like administrators under the old rule';

    public function handle(): int
    {
        $legacyPhones = ['01726976982', '+8801726976982', '8801726976982'];
        $legacyEmails = ['adideabd@gmail.com', 'ideapbd@gmail.com'];

        $this->info('Administrators (role admin / super_admin) — the only accounts with full control:');
        $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])->get(['id', 'name', 'email', 'phone', 'role', 'is_active', 'created_at']);
        $this->table(['id', 'name', 'email', 'phone', 'role', 'active', 'created'], $admins->map(fn ($u) => [$u->id, $u->name, $u->email, $u->phone, $u->role, $u->is_active ? 'yes' : 'NO', $u->created_at])->all());

        $suspects = User::whereNotIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN])
            ->where(function ($q) use ($legacyPhones, $legacyEmails) {
                $q->where('id', 1)->orWhereIn('phone', $legacyPhones)->orWhereIn(\DB::raw('LOWER(email)'), $legacyEmails);
            })->get(['id', 'name', 'email', 'phone', 'role', 'updated_at']);

        if ($suspects->isEmpty()) {
            $this->info('No non-admin account carries the old admin identity. Nothing to review.');
        } else {
            $this->warn('These accounts WERE administrators under the old rule (id 1 / old phone / old e-mail) but are not any more.');
            $this->warn('If one is yours, set its role to admin; if you do not recognise it, disable it and reset its password.');
            $this->table(['id', 'name', 'email', 'phone', 'role', 'updated'], $suspects->map(fn ($u) => [$u->id, $u->name, $u->email, $u->phone, $u->role, $u->updated_at])->all());
        }

        $staff = User::whereNotNull('custom_role_id')->orWhereIn('role', [User::ROLE_SUB_ADMIN, User::ROLE_SELLER])->count();
        $this->line("Staff-type accounts (sub admin / seller / custom role): {$staff}. They only reach what their role was granted.");

        return self::SUCCESS;
    }
}
