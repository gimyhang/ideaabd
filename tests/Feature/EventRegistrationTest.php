<?php

namespace Tests\Feature;

use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private function campaign(array $custom = []): EventCampaign
    {
        return EventCampaign::create([
            'title' => 'RSU', 'slug' => 'rangpursutsab', 'type' => 'event', 'is_active' => true,
            'has_fee_or_donation' => false, 'fee_amount' => 0,
            'custom_fields' => $custom,
            'form_settings' => ['is_writer_form' => true, 'requires_approval' => true],
        ]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'name' => 'Writer One', 'phone' => '01711222333', 'email' => 'w1@example.test',
            'district' => 'Rangpur', 'author_category' => 'Poet', 'optimized_photo_data' => self::PNG,
        ], $over);
    }

    private function user(array $over = []): User
    {
        return User::create(array_merge([
            'name' => 'Existing', 'email' => 'existing@example.test', 'phone' => '01800000001',
            'password' => Hash::make('Zx9#Tq!mW4$vLr7'), 'role' => 'buyer', 'reg_type' => 'customer',
            'reg_status' => User::STATUS_APPROVED, 'is_active' => true,
        ], $over));
    }

    public function test_page_loads_with_device_layer_and_admin_defined_fields(): void
    {
        $this->campaign([
            ['name' => 'tshirt', 'label' => 'T-Shirt', 'type' => 'select', 'required' => true, 'options' => 'S, M, L'],
            ['name' => 'age', 'label' => 'Age', 'type' => 'number', 'required' => false],
            ['name' => 'bio', 'label' => 'Bio', 'type' => 'textarea'],
        ]);

        $this->get('/rangpursutsab')->assertOk()
            ->assertSee('event-register.css', false)
            ->assertSee('event-register.js', false)
            ->assertSee("classList.add('ev-reg')", false)
            ->assertSee('name="tshirt"', false)
            ->assertSee('<option value="M"', false)
            ->assertSee('name="age"', false)
            ->assertSee('name="bio"', false);
    }

    public function test_aliases_resolve_and_unknown_slug_is_404(): void
    {
        $this->campaign();
        $this->get('/rsu')->assertOk();
        $this->get('/rsutshab')->assertOk();
        $this->get('/no-such-campaign')->assertNotFound();
    }

    public function test_custom_fields_are_validated_and_saved(): void
    {
        $this->campaign([
            ['name' => 'tshirt', 'label' => 'T-Shirt', 'type' => 'select', 'required' => true, 'options' => 'S, M, L'],
            ['name' => 'age', 'label' => 'Age', 'type' => 'number'],
        ]);

        $this->post('/rangpursutsab', $this->payload())->assertSessionHasErrors('tshirt');
        $this->post('/rangpursutsab', $this->payload(['tshirt' => 'XXL']))->assertSessionHasErrors('tshirt');
        $this->post('/rangpursutsab', $this->payload(['tshirt' => 'M', 'age' => 'abc']))->assertSessionHasErrors('age');
        $this->assertSame(0, EventRegistration::count());

        $this->post('/rangpursutsab', $this->payload(['tshirt' => 'M', 'age' => '31']))->assertSessionHasNoErrors();
        $reg = EventRegistration::firstOrFail();
        $this->assertSame('M', $reg->form_data['tshirt']);
        $this->assertSame('pending', $reg->status);
    }

    public function test_custom_field_cannot_shadow_builtin_columns(): void
    {
        $this->campaign([['name' => 'phone', 'label' => 'Other phone', 'type' => 'text', 'required' => true]]);

        // the fake "phone" custom field is ignored; the real phone rule still applies
        $this->post('/rangpursutsab', $this->payload())->assertSessionHasNoErrors();
        $this->assertSame(1, EventRegistration::count());
    }

    public function test_registering_with_someone_elses_email_never_signs_in_as_them(): void
    {
        $this->campaign();
        $admin = $this->user(['email' => 'boss@example.test', 'phone' => '01800000099', 'role' => 'admin', 'reg_type' => 'admin']);

        $this->post('/rangpursutsab', $this->payload(['email' => 'boss@example.test', 'phone' => '01999999999']))
            ->assertSessionHasNoErrors();

        $this->assertNotSame($admin->id, auth()->id(), 'visitor must not be signed in as the admin');
        $this->assertFalse(auth()->check() && auth()->user()->isAdmin());
        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertSame(1, User::where('email', 'boss@example.test')->count());
    }

    public function test_existing_phone_owner_is_not_logged_in_or_modified(): void
    {
        $this->campaign();
        $banned = $this->user(['phone' => '01711222333', 'is_active' => false, 'reg_status' => User::STATUS_REJECTED, 'phone_verified_at' => null]);

        $this->post('/rangpursutsab', $this->payload(['password' => 'Vq7#rT2!mX9zLp']))->assertSessionHasNoErrors();

        $this->assertGuest();
        $fresh = $banned->fresh();
        $this->assertFalse((bool) $fresh->is_active);
        $this->assertSame(User::STATUS_REJECTED, $fresh->reg_status);
        $this->assertNull($fresh->phone_verified_at);
        $this->assertTrue(Hash::check('Zx9#Tq!mW4$vLr7', $fresh->password));
        $this->assertSame(1, EventRegistration::count());
        $this->assertNull(EventRegistration::first()->user_id);
    }

    public function test_new_visitor_gets_an_account_signed_in_but_unverified(): void
    {
        $this->campaign();
        $this->post('/rangpursutsab', $this->payload())->assertSessionHasNoErrors();

        $this->assertAuthenticated();
        $user = auth()->user();
        $this->assertSame('01711222333', $user->phone);
        $this->assertNull($user->phone_verified_at);
        $this->assertNull($user->email_verified_at);
        $this->assertSame($user->id, EventRegistration::first()->user_id);
    }

    public function test_double_submit_creates_one_registration_with_one_number(): void
    {
        $this->campaign();
        $this->post('/rangpursutsab', $this->payload())->assertRedirect(route('event.success', 'rangpursutsab'));
        $this->post('/rangpursutsab', $this->payload())->assertRedirect(route('event.success', 'rangpursutsab'));

        $this->assertSame(1, EventRegistration::count());
        $this->assertSame(1, EventRegistration::distinct('registration_number')->count('registration_number'));
    }

    public function test_photo_is_required_for_a_new_writer_but_not_a_repeat_submit(): void
    {
        $this->campaign();
        $noPhoto = $this->payload();
        unset($noPhoto['optimized_photo_data']);

        $this->post('/rangpursutsab', $noPhoto)->assertSessionHas('error');
        $this->assertSame(0, EventRegistration::count());

        $this->post('/rangpursutsab', $this->payload())->assertSessionHasNoErrors();
        auth()->logout();
        $this->flushSession();
        // registered phone, no photo this time: lands on the existing registration, not a photo error
        $this->post('/rangpursutsab', $noPhoto)->assertRedirect(route('event.success', 'rangpursutsab'));
        $this->assertSame(1, EventRegistration::count());
    }

    public function test_scholarship_and_library_pages_render(): void
    {
        EventCampaign::updateOrCreate(['slug' => 'jshikkhabritti'], ['title' => 'Sch', 'type' => 'scholarship', 'is_active' => true, 'has_fee_or_donation' => false, 'fee_amount' => 0, 'custom_fields' => [['name' => 'extra', 'label' => 'Extra', 'type' => 'text']], 'form_settings' => ['is_scholarship_form' => true]]);
        $this->get('/jshikkhabritti')->assertOk()->assertSee('event-register.js', false)->assertSee('name="extra"', false);
        $this->get('/pathagar')->assertOk()->assertSee('event-register.js', false);
    }
}
