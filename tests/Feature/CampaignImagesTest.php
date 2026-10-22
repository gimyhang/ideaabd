<?php

namespace Tests\Feature;

use App\Models\EventCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampaignImagesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Campaign Admin', 'email' => 'camp-admin@example.test', 'phone' => '01710000066',
            'password' => Hash::make('test-password'), 'role' => 'admin', 'reg_type' => 'admin',
            'reg_status' => 'approved', 'is_active' => true,
        ]);
    }

    private function fields(EventCampaign $c, array $over = []): array
    {
        return array_merge([
            'title' => $c->title, 'slug' => $c->slug, 'type' => 'scholarship',
            'form_settings_json' => json_encode($c->form_settings ?? []),
        ], $over);
    }

    public function test_banner_and_logo_can_be_replaced_repeatedly_even_when_large_or_stale(): void
    {
        Storage::fake('public');
        $c = EventCampaign::create(['title' => 'C', 'slug' => 'c-one', 'type' => 'scholarship', 'is_active' => true, 'has_fee_or_donation' => false, 'fee_amount' => 0]);
        $admin = $this->actingAs($this->admin());

        // 3 MB banner (used to be rejected above 2 MB) + logo
        $admin->put(route('admin.event-campaigns.update', $c->id), $this->fields($c, [
            'banner_image' => UploadedFile::fake()->image('b1.jpg', 1600, 700)->size(3000),
            'form_logo_image' => UploadedFile::fake()->image('l1.png', 300, 300),
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $c->refresh();
        $banner1 = $c->banner_image; $logo1 = $c->form_settings['logo_image'];
        Storage::disk('public')->assertExists([$banner1, $logo1]);

        // replace both; the page still posts the OLD logo path inside form_settings_json
        $admin->put(route('admin.event-campaigns.update', $c->id), $this->fields($c, [
            'banner_image' => UploadedFile::fake()->image('b2.jpg', 1600, 700),
            'form_logo_image' => UploadedFile::fake()->image('l2.png', 300, 300),
        ]))->assertSessionHasNoErrors()->assertRedirect();
        $c->refresh();
        $this->assertNotSame($banner1, $c->banner_image);
        $this->assertNotSame($logo1, $c->form_settings['logo_image']);
        Storage::disk('public')->assertMissing([$banner1, $logo1]);
        Storage::disk('public')->assertExists([$c->banner_image, $c->form_settings['logo_image']]);

        // a save that does not touch the images, with a stale logo path in the posted JSON, keeps the current logo
        $current = $c->form_settings['logo_image'];
        $admin->put(route('admin.event-campaigns.update', $c->id), $this->fields($c, ['form_settings_json' => json_encode(['logo_image' => $logo1, 'logo_size' => 90])]))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($current, $c->fresh()->form_settings['logo_image']);

        // remove the banner
        $admin->put(route('admin.event-campaigns.update', $c->id), $this->fields($c, ['remove_banner' => '1']))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull($c->fresh()->banner_image);
    }

    public function test_a_bad_file_gives_a_clear_error_and_keeps_the_old_banner(): void
    {
        Storage::fake('public');
        $c = EventCampaign::create(['title' => 'C', 'slug' => 'c-two', 'type' => 'scholarship', 'is_active' => true, 'has_fee_or_donation' => false, 'fee_amount' => 0, 'banner_image' => 'campaigns/old.jpg']);
        Storage::disk('public')->put('campaigns/old.jpg', 'x');

        $this->actingAs($this->admin())->put(route('admin.event-campaigns.update', $c->id), $this->fields($c, [
            'banner_image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ]))->assertSessionHasErrors('banner_image');

        $this->assertSame('campaigns/old.jpg', $c->fresh()->banner_image);
        Storage::disk('public')->assertExists('campaigns/old.jpg');
    }
}
