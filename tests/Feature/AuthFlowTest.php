<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * End-to-end: register → pending → admin approve → sign in → dashboard → sign out,
 * for every public role (buyer, author, publisher, seller).
 */
class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Zx9#Tq!mW4$vLr7';

    private function admin(): User
    {
        return User::create([
            'name' => 'Root Admin', 'email' => 'root@example.test', 'phone' => '01700000001',
            'password' => Hash::make(self::PASSWORD), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
            'email_verified_at' => now(), 'phone_verified_at' => now(),
        ]);
    }

    private function register(string $category, string $phone, string $email): \Illuminate\Testing\TestResponse
    {
        $info = PhoneNormalizer::normalize($phone, '+880');
        $payload = [
            'name' => ucfirst($category) . ' Tester', 'email' => $email, 'phone' => $phone, 'country_code' => '+880',
            'password' => self::PASSWORD, 'category' => $category,
            'author_name' => 'Author Tester', 'publisher_name' => 'Tester House', 'shop_name' => 'Tester Shop',
        ];

        return $this->withSession([
                'otp_verified_phone' => $info['local_phone'],
                'otp_verified_full_phone' => $info['formatted_phone'],
                'otp_verified_email' => $email,
            ])->postJson('/register/complete', $payload);
    }

    public static function roles(): array
    {
        return [
            'buyer'     => ['buyer',     'my-account',          '01711000001'],
            'author'    => ['author',    'author.dashboard',    '01711000002'],
            'publisher' => ['publisher', 'publisher.dashboard', '01711000003'],
            'seller'    => ['seller',    'subadmin.dashboard',  '01711000004'],
        ];
    }

    public function test_public_pages_open(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertRedirect('/login?mode=register');
        $this->get('/register/choose')->assertRedirect();
        $this->get('/pending-approval')->assertRedirect(route('login'));
        $this->get(route('password.request'))->assertOk();
    }

    public function test_admin_can_reject_and_applicant_stays_locked_out(): void
    {
        $admin = $this->admin();
        $this->register('publisher', '01711000007', 'rej@example.test')->assertOk();
        $user = User::where('email', 'rej@example.test')->firstOrFail();
        $this->post('/logout');
        $this->actingAs($admin)->post(route('admin.registrations.reject', $user->id), ['reason' => 'incomplete'])->assertRedirect();
        $this->post('/logout');
        $this->assertSame(User::STATUS_REJECTED, $user->fresh()->reg_status);
        $this->actingAs($user->fresh())->get(route('publisher.dashboard'))->assertRedirect(route('pending.approval'));
    }

    /** @dataProvider roles */
    public function test_full_lifecycle(string $category, string $dashboardRoute, string $phone): void
    {
        $email = $category . '@example.test';
        $admin = $this->admin();

        // 1. register
        $res = $this->register($category, $phone, $email);
        $res->assertOk()->assertJson(['success' => true]);
        $user = User::where('email', $email)->firstOrFail();
        $this->assertSame($category, $user->reg_type);
        $this->assertAuthenticatedAs($user);

        // 2. while pending: non-buyers are sent to the waiting page
        if ($category === 'buyer') {
            $this->assertSame(User::STATUS_APPROVED, $user->reg_status);
            $this->get('/user')->assertRedirect(route('my-account'));
            $this->get(route('my-account'))->assertOk();
        } else {
            $this->assertSame(User::STATUS_PENDING, $user->reg_status);
            $this->get('/user')->assertRedirect(route('pending.approval'));
            $this->get(route('pending.approval'))->assertOk();
            $this->get(route($dashboardRoute))->assertRedirect(route('pending.approval')); // blocked until approved
        }

        // 3. sign out
        $this->post('/logout')->assertRedirect();
        $this->assertGuest();

        // 4. sign in with the password
        $this->postJson('/login', ['email' => $email, 'password' => self::PASSWORD])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertAuthenticatedAs($user->fresh());
        $this->post('/logout');
        $this->assertGuest();

        // 5. admin approves
        if ($category !== 'buyer') {
            $this->actingAs($admin)
                ->post(route('admin.registrations.approve', $user->id))
                ->assertRedirect();
            $user->refresh();
            $this->assertSame(User::STATUS_APPROVED, $user->reg_status);
            $this->assertSame($category === 'seller' ? 'seller' : $category, $user->role);
            $this->post('/logout');
        }

        // 6. sign in again → portal → dashboard opens
        $this->postJson('/login', ['email' => $email, 'password' => self::PASSWORD])->assertOk();
        $this->get('/user')->assertRedirect(route($dashboardRoute));
        $this->get(route($dashboardRoute))->assertOk();

        // 7. sign out
        $this->post('/logout')->assertRedirect();
        $this->assertGuest();
    }

    public function test_admin_dashboard_opens(): void
    {
        $this->actingAs($this->admin());
        $this->get('/user')->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.registrations.approvals'))->assertOk();
    }

    public function test_duplicate_registration_is_blocked(): void
    {
        $this->register('buyer', '01711000009', 'dup@example.test')->assertOk();
        $this->post('/logout');
        $this->postJson('/register/complete', [
            'name' => 'Intruder', 'email' => 'dup@example.test', 'phone' => '01711000009', 'country_code' => '+880',
            'password' => self::PASSWORD, 'category' => 'author',
        ])->assertStatus(422);
        $this->assertSame('buyer', User::where('email', 'dup@example.test')->value('reg_type'));
    }
}
