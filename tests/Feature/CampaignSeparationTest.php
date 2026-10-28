<?php

namespace Tests\Feature;

use App\Models\EventCampaign;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Scholarship (/jsb), writer (/rsu), library and plain events never mix, and their forms behave. */
class CampaignSeparationTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();
        EventCampaign::withTrashed()->forceDelete();   // the migration seeds a scholarship campaign; each test sets up its own
    }

    private function make(string $slug, string $type, array $fs = []): EventCampaign
    {
        return EventCampaign::create([
            'title' => strtoupper($slug), 'slug' => $slug, 'type' => $type, 'is_active' => true,
            'has_fee_or_donation' => false, 'fee_amount' => 0, 'custom_fields' => [], 'form_settings' => $fs,
        ]);
    }

    private function writer(string $slug = 'rsu', array $fs = ['is_writer_form' => true, 'requires_approval' => true]): EventCampaign
    {
        return $this->make($slug, 'writer', $fs);
    }

    private function w(array $o = []): array
    {
        return array_merge(['name' => 'Writer', 'phone' => '01711222333', 'district' => 'Rangpur', 'author_category' => 'কবি', 'optimized_photo_data' => self::PNG], $o);
    }

    private function reg(EventCampaign $c, string $no, string $status = 'confirmed', string $phone = '01711000001'): EventRegistration
    {
        return EventRegistration::create(['event_campaign_id' => $c->id, 'registration_number' => $no, 'name' => 'A', 'phone' => $phone, 'status' => $status, 'form_data' => []]);
    }

    public function test_every_campaign_has_exactly_one_kind_and_a_renamed_slug_keeps_it(): void
    {
        $this->assertSame('scholarship', $this->make('jsb', 'scholarship', ['is_scholarship_form' => true])->kind());
        $this->assertSame('writer', $this->make('rsu26', 'writer')->kind());
        $this->assertSame('writer', $this->make('old-rsu', 'event', ['is_writer_form' => true])->kind());
        $this->assertSame('library', $this->make('lib-2027', 'library')->kind());
        $this->assertSame('event', $this->make('workshop-1', 'workshop')->kind());
        $this->assertSame('scholarship', $this->make('jshikkhabritti', 'event')->kind(), 'a campaign saved before types existed is recognised by its first slug');
    }

    public function test_old_addresses_open_their_own_kind_and_never_another(): void
    {
        $jsb = $this->make('jsb', 'scholarship', ['is_scholarship_form' => true]);
        $rsu = $this->writer('rsu26');
        $lib = $this->make('lib-2027', 'library');
        $before = EventCampaign::count();

        $this->assertSame($jsb->id, EventCampaign::resolve('jshikkhabritti')->id);
        $this->assertSame($jsb->id, EventCampaign::resolve('scholarship')->id);
        $this->assertSame($rsu->id, EventCampaign::resolve('rsu')->id);
        $this->assertSame($rsu->id, EventCampaign::resolve('rsutshab')->id);
        $this->assertSame($lib->id, EventCampaign::resolve('library')->id);
        $this->assertNull(EventCampaign::resolve('no-such-thing'));

        foreach (['/jshikkhabritti', '/jsb', '/rsu', '/rangpursutsab', '/library', '/pathagar'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->assertSame($before, EventCampaign::count(), 'opening an old address never creates a duplicate campaign');
    }

    public function test_the_page_for_each_kind_is_the_right_form(): void
    {
        $this->make('jsb', 'scholarship', ['is_scholarship_form' => true]);
        $this->writer('rsu26');
        $this->make('lib-2027', 'library');

        $this->get('/jsb')->assertSee('name="scholarship_degree"', false)->assertDontSee('name="author_category"', false);
        $this->get('/rsu')->assertSee('name="author_category"', false)->assertDontSee('name="scholarship_degree"', false);
        $this->get('/lib-2027')->assertSee('name="president_name"', false)->assertDontSee('name="author_category"', false);
    }

    public function test_a_deleted_campaign_stays_deleted_instead_of_breaking_or_reappearing(): void
    {
        $this->writer('rsu')->delete();
        $this->get('/rsu')->assertNotFound();
        $this->assertSame(0, EventCampaign::count());
    }

    public function test_the_success_page_only_shows_its_own_campaign_summary(): void
    {
        Http::fake();
        $this->writer('rsu');
        $this->make('other', 'event');
        $this->post('/rsu', $this->w())->assertSessionHasNoErrors();

        $this->get('/event-confirmed/rsu')->assertOk()->assertSee('RSU-2026-001');
        $this->get('/event-confirmed/other')->assertOk()->assertDontSee('RSU-2026-001');
        $this->get('/event-confirmed/does-not-exist')->assertNotFound();
    }

    public function test_a_deleted_registration_never_makes_its_number_come_back(): void
    {
        Http::fake();
        $this->writer();
        foreach (['01711000001', '01711000002'] as $phone) {
            $this->post('/rsu', $this->w(['phone' => $phone]))->assertSessionHasNoErrors();
            Auth::logout();
            $this->flushSession();
        }
        EventRegistration::orderByDesc('id')->first()->delete();

        $this->post('/rsu', $this->w(['phone' => '01711000003']))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(['RSU-2026-001', 'RSU-2026-002', 'RSU-2026-003'], EventRegistration::withTrashed()->orderBy('id')->pluck('registration_number')->all());
    }

    public function test_a_second_person_on_the_same_signed_in_browser_gets_their_own_registration(): void
    {
        Http::fake();
        $this->writer();
        $this->post('/rsu', $this->w(['phone' => '01711000001', 'name' => 'First', 'password' => 'Zx9#Tq!mW4$vLr7']))->assertSessionHasNoErrors();
        $this->assertAuthenticated();

        $this->post('/rsu', $this->w(['phone' => '01711000002', 'name' => 'Second']))->assertSessionHasNoErrors();

        $this->assertSame(2, EventRegistration::count());
        $second = EventRegistration::where('name', 'Second')->first();
        $this->assertNull($second->user_id, 'a stranger on a shared computer is not linked to the signed-in account');
        $this->assertNotNull(EventRegistration::where('name', 'First')->first()->user_id);
    }

    public function test_the_same_phone_still_registers_once_per_campaign_but_once_in_each_campaign(): void
    {
        Http::fake();
        $this->writer();
        $this->make('lib', 'library');
        $this->post('/rsu', $this->w())->assertSessionHasNoErrors();
        $this->post('/rsu', $this->w(['name' => 'Again']));
        $this->assertSame(1, EventRegistration::count());
    }

    public function test_a_pending_writer_cannot_approve_themselves_by_submitting_again(): void
    {
        Http::fake();
        $this->writer('rsu', ['is_writer_form' => true]);   // no explicit requires_approval flag
        $this->post('/rsu', $this->w())->assertSessionHasNoErrors();
        Auth::logout();
        $this->flushSession();
        $this->post('/rsu', $this->w());

        $this->assertSame('pending', EventRegistration::first()->status);
    }

    public function test_attendance_check_in_needs_an_exact_approved_entry(): void
    {
        $c = $this->writer();
        $pending = $this->reg($c, 'RSU-2026-001', 'pending');
        $rejected = $this->reg($c, 'RSU-2026-002', 'rejected', '01711000002');
        $ok = $this->reg($c, 'RSU-2026-003', 'confirmed', '01711000003');

        $this->get('/v/RSU-2026-001')->assertForbidden();
        $this->get('/v/RSU-2026-002')->assertForbidden();
        $this->get('/v/' . rawurlencode('%'))->assertNotFound();
        $this->get('/v/2026-003')->assertNotFound();

        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame('rejected', $rejected->fresh()->status);
        $this->assertSame('confirmed', $ok->fresh()->status);

        $this->get('/v/RSU-2026-003')->assertOk();
        $this->assertSame('attended', $ok->fresh()->status);
    }

    public function test_a_card_of_a_deleted_campaign_is_a_404_not_a_crash(): void
    {
        $c = $this->writer();
        $this->reg($c, 'RSU-2026-001');
        $c->delete();
        $this->get('/card/RSU-2026-001')->assertNotFound();
    }

    public function test_only_real_images_are_accepted_as_the_photo(): void
    {
        Http::fake();
        $this->writer();
        $data = $this->w();
        unset($data['optimized_photo_data']);

        $this->post('/rsu', $data + ['student_photo' => UploadedFile::fake()->createWithContent('x.html', '<script>alert(1)</script>')])->assertSessionHasErrors('student_photo');
        $this->post('/rsu', $this->w(['optimized_photo_data' => 'data:image/png;base64,' . base64_encode('not an image at all')]))->assertSessionHas('error');
        $this->assertSame(0, EventRegistration::count());

        $this->post('/rsu', $this->w())->assertSessionHasNoErrors();
        $this->assertSame(1, EventRegistration::count());
        $this->assertStringEndsWith('.jpg', EventRegistration::first()->form_data['student_photo']);
    }

    public function test_long_answers_are_refused(): void
    {
        Http::fake();
        $this->writer();
        $this->post('/rsu', $this->w(['notable_books' => str_repeat('x', 1001)]))->assertSessionHasErrors('notable_books');
        $this->post('/rsu', $this->w(['magazine_name' => str_repeat('y', 256)]))->assertSessionHasErrors('magazine_name');
        $this->assertSame(0, EventRegistration::count());
    }

    public function test_the_last_seat_cannot_be_oversold(): void
    {
        Http::fake();
        $c = $this->writer();
        $c->update(['max_participants' => 1]);
        $this->reg($c, 'RSU-2026-001');
        $this->post('/rsu', $this->w())->assertSessionHas('error');
        $this->assertSame(1, EventRegistration::count());
    }

    public function test_admin_added_entries_follow_the_same_rules(): void
    {
        Http::fake();
        $jsb = $this->make('jsb', 'scholarship', ['is_scholarship_form' => true]);
        $admin = User::create(['name' => 'A', 'email' => 'a@x.test', 'phone' => '01710000098', 'password' => Hash::make('x'), 'role' => 'admin', 'reg_type' => 'admin', 'reg_status' => 'approved', 'is_active' => true]);
        $url = route('admin.event-campaigns.registrations.store', $jsb->id);

        $this->actingAs($admin)->post($url, ['name' => 'X', 'phone' => '01711555666']);
        $this->actingAs($admin)->post($url, ['name' => 'X again', 'phone' => '01711555666'])->assertSessionHas('error');

        $this->assertSame(['2027-1'], EventRegistration::pluck('registration_number')->all(), 'scholarship numbering, one entry per phone');
    }

    public function test_the_admin_can_pick_the_writer_type(): void
    {
        $admin = User::create(['name' => 'A', 'email' => 'a@x.test', 'phone' => '01710000098', 'password' => Hash::make('x'), 'role' => 'admin', 'reg_type' => 'admin', 'reg_status' => 'approved', 'is_active' => true]);
        $c = $this->writer('rsu');

        $this->actingAs($admin)->put(route('admin.event-campaigns.update', $c->id), ['title' => 'RSU', 'slug' => 'rsu26', 'type' => 'writer', 'is_active' => 1])
            ->assertSessionHasNoErrors();

        $this->assertSame('rsu26', $c->fresh()->slug);
        $this->get('/rsu')->assertOk();
        $this->assertSame(1, EventCampaign::count());
    }
}
