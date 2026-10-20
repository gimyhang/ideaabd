<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Middleware\RoleMiddleware;
use App\Models\AdminRole;
use App\Models\User;
use App\Support\SafeRedirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The administrator controls everything; every other account sees only its own function.
 * These tests pin the rules that were broken before: identity by phone/e-mail/id, staff reaching unmapped
 * admin routes, portals open to anyone, and unsafe "view as" access.
 */
class AccessControlSecurityTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function user(string $role, array $extra = []): User
    {
        $this->seq++;

        return User::create($extra + [
            'name' => "U{$this->seq}", 'email' => "u{$this->seq}@sec.test", 'phone' => '0171000' . str_pad((string) $this->seq, 4, '0', STR_PAD_LEFT),
            'password' => 'Zx9#Tq!mW4$vLr7', 'role' => $role, 'reg_type' => $role, 'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    private function admin(): User
    {
        return $this->user('admin', ['phone' => '01728976982', 'email' => 'adideabd@gmail.com']);
    }

    /** Runs the role:admin middleware alone for every admin route and returns what the given user can reach. */
    private function reachableAdminRoutes(User $user): array
    {
        $reach = [];
        foreach (app('router')->getRoutes() as $route) {
            if (!in_array('role:admin', $route->gatherMiddleware(), true)) {
                continue;
            }
            $req = Request::create('/' . preg_replace('/\{[^}]+\}/', '1', $route->uri()), $route->methods()[0]);
            $req->setUserResolver(fn () => $user);
            $req->setRouteResolver(fn () => $route);

            try {
                $res = (new RoleMiddleware())->handle($req, fn () => response('PASS'), 'admin');
                if ($res->getContent() === 'PASS') {
                    $reach[] = (string) $route->getName();
                }
            } catch (\Throwable) {
                // denied
            }
        }

        return $reach;
    }

    // ── 1. Administrator is decided by role only ───────────────────────────────

    public function test_admin_is_never_inferred_from_phone_email_or_id(): void
    {
        $this->admin(); // takes id 1
        foreach (['01726976982', '+8801726976982', '8801726976982'] as $i => $phone) {
            $this->assertFalse($this->user('buyer', ['phone' => $phone, 'email' => "x{$i}@sec.test"])->isAdmin(), "phone $phone must not grant admin");
        }
        foreach (['adideabd@gmail.com', 'ideapbd@gmail.com'] as $mail) {
            $this->assertFalse($this->user('buyer', ['email' => $mail . '.evil'])->isAdmin());
        }
        $this->assertTrue($this->user('super_admin')->isAdmin());
        $this->assertTrue(User::where('role', 'admin')->first()->isAdmin());
    }

    public function test_user_with_old_magic_identity_gets_no_admin_pages(): void
    {
        $this->admin();
        $buyer = $this->user('buyer', ['phone' => '+8801726976982']);
        $this->actingAs($buyer)->get(route('admin.dashboard'))->assertForbidden();
    }

    // ── 2. Phone changes need a code sent to the new number ────────────────────

    public function test_phone_cannot_be_changed_without_the_code_or_to_a_lookalike_of_another_number(): void
    {
        $this->admin();
        $buyer = $this->user('buyer');
        $old = $buyer->phone;

        $this->actingAs($buyer)->post(route('my-account.profile.update'), ['name' => 'B', 'phone' => '+8801728976982'])
            ->assertSessionHasErrors('phone');
        $this->assertSame($old, $buyer->fresh()->phone);

        // every spelling of the administrator's number counts as taken
        foreach (['01728976982', '+8801728976982', '8801728976982'] as $variant) {
            $this->actingAs($buyer)->postJson(route('my-account.phone.send-otp'), ['phone' => $variant])->assertStatus(422);
        }
    }

    public function test_phone_changes_with_the_right_code_and_is_stored_in_one_format(): void
    {
        $buyer = $this->user('buyer');
        $this->actingAs($buyer)->postJson(route('my-account.phone.send-otp'), ['phone' => '+8801911223344'])->assertOk();

        $pending = Cache::get('phone_change:' . $buyer->id);
        $this->assertSame('01911223344', $pending['phone']);

        // wrong code
        $this->actingAs($buyer)->post(route('my-account.profile.update'), ['name' => 'B', 'phone' => '+8801911223344', 'phone_otp' => '000000'])
            ->assertSessionHasErrors('phone');
        $this->assertNotSame('01911223344', $buyer->fresh()->phone);

        // right code
        Cache::put('phone_change:' . $buyer->id, ['phone' => '01911223344', 'hash' => \Illuminate\Support\Facades\Hash::make('123456'), 'attempts' => 0], 600);
        $this->actingAs($buyer)->post(route('my-account.profile.update'), ['name' => 'B', 'phone' => '+8801911223344', 'phone_otp' => '123456'])
            ->assertSessionHasNoErrors();
        $this->assertSame('01911223344', $buyer->fresh()->phone);
    }

    // ── 3. Staff reach only what an administrator granted ─────────────────────

    public function test_unmapped_or_new_admin_routes_are_administrator_only(): void
    {
        $this->assertSame(RoleMiddleware::ADMIN_ONLY, RoleMiddleware::permissionFor('admin.brand-new-feature', 'POST'));
        $this->assertSame(RoleMiddleware::ADMIN_ONLY, RoleMiddleware::permissionFor(null, 'GET'));
        $this->assertSame(RoleMiddleware::ADMIN_ONLY, RoleMiddleware::permissionFor('admin.backup.download', 'GET'));
        $this->assertSame(RoleMiddleware::ADMIN_ONLY, RoleMiddleware::permissionFor('admin.users.quick-password-reset', 'POST'));
        $this->assertSame('purchases.manage', RoleMiddleware::permissionFor('admin.purchases.items.merge', 'POST'));
        $this->assertSame('catalog.view', RoleMiddleware::permissionFor('admin.products.index', 'GET'));
        $this->assertSame('catalog.edit', RoleMiddleware::permissionFor('admin.products.update', 'PUT'));
    }

    public function test_seller_cannot_touch_passwords_backups_cache_or_other_users(): void
    {
        $root = $this->admin();
        $seller = $this->user('seller');

        $this->actingAs($seller)->postJson(route('admin.users.quick-password-reset', $root->id), ['password' => 'Hacked#12345'])->assertForbidden();
        $this->actingAs($seller)->get(route('admin.backup.download', 'x.zip'))->assertForbidden();
        $this->actingAs($seller)->postJson(route('admin.cache.run-artisan'), ['command' => 'optimize:clear'])->assertForbidden();
        $this->actingAs($seller)->postJson(route('admin.users.toggle-status', $root->id))->assertForbidden();
        $this->actingAs($seller)->postJson(route('admin.users.assign-role', $root->id), ['role' => 'admin'])->assertForbidden();
        $this->actingAs($seller)->post(route('admin.users.impersonate', $root->id))->assertForbidden();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Zx9#Tq!mW4$vLr7', $root->fresh()->password));
        $this->assertTrue($root->fresh()->is_active);
    }

    public function test_custom_role_without_permissions_reaches_nothing_but_its_own_profile(): void
    {
        $this->admin();
        $role = AdminRole::create(['name' => 'Empty', 'slug' => 'empty-role']);
        $staff = $this->user('buyer', ['custom_role_id' => $role->id]);

        $reach = $this->reachableAdminRoutes($staff);
        $this->assertSame([], array_values(array_filter($reach, fn ($n) => !str_starts_with($n, 'admin.profile'))), 'unexpected routes: ' . implode(', ', $reach));
    }

    public function test_seller_reaches_only_mapped_routes_it_was_given(): void
    {
        $this->admin();
        $reach = $this->reachableAdminRoutes($this->user('seller'));

        foreach ($reach as $name) {
            $perm = RoleMiddleware::permissionFor($name, 'GET');
            $this->assertNotSame(RoleMiddleware::ADMIN_ONLY, $perm, "$name is admin-only but a seller reached it");
        }
        $this->assertNotContains('admin.backup.download', $reach);
        $this->assertNotContains('admin.users.quick-password-reset', $reach);
    }

    public function test_staff_with_a_permission_can_use_it_and_nothing_beyond(): void
    {
        $this->admin();
        $role = AdminRole::create(['name' => 'Buyer desk', 'slug' => 'buyer-desk']);
        $ids = \Illuminate\Support\Facades\DB::table('admin_permissions')->whereIn('key', ['purchases.manage', 'catalog.view'])->pluck('id');
        foreach ($ids as $id) {
            \Illuminate\Support\Facades\DB::table('role_has_permissions')->insert(['role' => 'buyer-desk', 'permission_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
        }
        $staff = $this->user('buyer', ['custom_role_id' => $role->id]);

        $this->actingAs($staff)->get(route('admin.purchases.items.stock-list'))->assertOk();            // purchases.manage
        $this->actingAs($staff)->get(route('admin.products.index'))->assertOk();                         // catalog.view: allowed
        $this->actingAs($staff)->put(route('admin.products.update', 1), [])->assertForbidden();         // catalog.edit: not granted
        $this->actingAs($staff)->get(route('admin.backup.index'))->assertForbidden();                  // settings.manage: not granted
        $this->actingAs($staff)->get(route('admin.backup.download', 'x.zip'))->assertForbidden();      // administrator only
        $this->actingAs($staff)->postJson(route('admin.cache.clear-all'))->assertForbidden();          // settings.manage: not granted
        $this->actingAs($staff)->get(route('admin.users'))->assertForbidden();                         // users.view: not granted
    }

    public function test_the_administrator_still_reaches_every_admin_route(): void
    {
        $reach = $this->reachableAdminRoutes($this->admin());
        $total = collect(app('router')->getRoutes())->filter(fn ($r) => in_array('role:admin', $r->gatherMiddleware(), true))->count();
        $this->assertCount($total, $reach);
    }

    // ── 4. Portals belong to their own role ───────────────────────────────────

    public function test_buyers_cannot_open_author_or_publisher_portals(): void
    {
        $buyer = $this->user('buyer');
        $this->actingAs($buyer)->get(route('author.dashboard'))->assertForbidden();
        $this->actingAs($buyer)->get(route('publisher.dashboard'))->assertForbidden();

        $author = $this->user('author');
        $this->actingAs($author)->get(route('publisher.dashboard'))->assertForbidden();
        $this->assertNotSame(403, $this->actingAs($author)->get(route('author.dashboard'))->getStatusCode());

        $publisher = $this->user('publisher');
        $this->actingAs($publisher)->get(route('author.dashboard'))->assertForbidden();

        $this->assertNotSame(403, $this->actingAs($this->admin())->get(route('author.dashboard'))->getStatusCode());
    }

    // ── 5. "View as user" ─────────────────────────────────────────────────────

    public function test_admin_can_view_a_users_dashboard_read_only_and_come_back(): void
    {
        $root = $this->admin();
        $author = $this->user('author');

        $this->actingAs($root)->post(route('admin.users.impersonate', $author->id))->assertRedirect(route('user.portal'));
        $this->assertSame($author->id, auth()->id());
        $this->assertSame($root->id, session(ImpersonationController::SESSION_KEY));

        // the banner is shown on public pages, and nothing can be changed in the user's name
        $this->get(route('my-account'))->assertOk()->assertSee('impersonationBar', false);
        $this->post(route('my-account.profile.update'), ['name' => 'Changed', 'phone' => $author->phone])->assertRedirect();
        $this->assertNotSame('Changed', $author->fresh()->name);
        $this->postJson(route('my-account.password.update'), ['current_password' => 'x', 'password' => 'NewPass#1234', 'password_confirmation' => 'NewPass#1234'])->assertForbidden();

        // and the admin pages stay closed while looking as that user
        $this->get(route('admin.dashboard'))->assertForbidden();

        $this->post(route('impersonate.leave'))->assertRedirect(route('admin.users'));
        $this->assertSame($root->id, auth()->id());
        $this->assertNull(session(ImpersonationController::SESSION_KEY));
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_only_the_real_admin_can_view_as_and_never_another_admin(): void
    {
        $root = $this->admin();
        $other = $this->user('admin');
        $seller = $this->user('seller');
        $buyer = $this->user('buyer');

        $this->actingAs($seller)->post(route('admin.users.impersonate', $buyer->id))->assertForbidden();
        $this->actingAs($this->user('sub_admin'))->post(route('admin.users.impersonate', $buyer->id))->assertForbidden();

        $this->actingAs($root)->post(route('admin.users.impersonate', $other->id))->assertForbidden();
        $this->actingAs($root)->post(route('admin.users.impersonate', $root->id))->assertForbidden();
        $this->assertSame($root->id, auth()->id());
    }

    public function test_a_tampered_view_as_key_logs_out_instead_of_switching_accounts(): void
    {
        $this->admin();
        $buyer = $this->user('buyer');
        $victim = $this->user('seller');

        $this->actingAs($buyer)->withSession([ImpersonationController::SESSION_KEY => $victim->id])
            ->post(route('impersonate.leave'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ── 5b. Module admin areas and pages that used to fail ────────────────────

    public function test_seo_admin_is_closed_to_ordinary_users_and_open_to_the_admin(): void
    {
        $this->actingAs($this->user('buyer'))->get(route('admin.seo.scanner'))->assertForbidden();
        $this->actingAs($this->user('author'))->post(route('admin.seo.redirects.store'), ['from_path' => '/a', 'to_path' => '/b'])->assertForbidden();
        $this->actingAs($this->user('seller'))->get(route('admin.seo.index'))->assertForbidden();

        $this->actingAs($this->admin())->get(route('admin.seo.scanner'))->assertOk()->assertSee('scanForm', false);
    }

    public function test_formerly_broken_pages_now_answer_properly(): void
    {
        $this->get('/books/this-book-does-not-exist')->assertNotFound();            // used to be a 500 (undefined $request)
        $this->get(route('blog.write'))->assertRedirect();                            // used to be a 500 (route name register.type)
        $this->get('/api/api/tags')->assertNotFound();                                // unfinished module endpoints are off
        $this->get(route('sitemap.ping'))->assertRedirect(route('login'));            // no longer public

        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.moderation'))->assertOk()->assertSee('Moderation');
        $this->actingAs($admin)->get(route('admin.accounting.salary.slip', 999))->assertNotFound();
    }

    // ── 6. Redirects and one-time codes ───────────────────────────────────────

    public function test_login_redirects_stay_on_this_site(): void
    {
        $fb = 'https://app.test/home';
        $this->assertSame('/my-account', SafeRedirect::sanitize('/my-account', $fb));
        $this->assertSame($fb, SafeRedirect::sanitize('https://evil.test/phish', $fb));
        $this->assertSame($fb, SafeRedirect::sanitize('//evil.test', $fb));
        $this->assertSame($fb, SafeRedirect::sanitize('/\\evil.test', $fb));
        $this->assertSame($fb, SafeRedirect::sanitize('javascript:alert(1)', $fb));
        $this->assertSame($fb, SafeRedirect::sanitize('', $fb));
        $own = 'http://' . parse_url((string) config('app.url'), PHP_URL_HOST) . '/books';
        $this->assertSame($own, SafeRedirect::sanitize($own, $fb));
    }
}
