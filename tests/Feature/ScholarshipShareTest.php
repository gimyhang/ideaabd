<?php

namespace Tests\Feature;

use App\Models\EventCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Sharing /jsb must preview the scholarship's own banner (a 1200x630 card), not the site's main
 * banner, in every state of the campaign.
 */
class ScholarshipShareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');   // cards and banners never touch the real storage
    }

    private function campaign(array $over = []): EventCampaign
    {
        return EventCampaign::create($over + [
            'slug' => 'jsb', 'title' => 'Joyee Shikkha Britti Application Form', 'type' => 'scholarship',
            'is_active' => true, 'has_fee_or_donation' => false, 'fee_amount' => 0,
            'short_description' => 'Scholarship for HSC and Honours students.',
            'form_settings' => ['is_scholarship_form' => true],
        ]);
    }

    private function png(int $w, int $h): string
    {
        $im = imagecreatetruecolor($w, $h);
        imagefill($im, 0, 0, imagecolorallocate($im, 200, 40, 40));
        imagefilledrectangle($im, (int) ($w * .1), (int) ($h * .2), (int) ($w * .9), (int) ($h * .8), imagecolorallocate($im, 255, 255, 255));
        ob_start();
        imagepng($im);

        return (string) ob_get_clean();
    }

    private function meta(string $html, string $property): ?string
    {
        preg_match('/<meta property="' . preg_quote($property, '/') . '" content="([^"]*)"/', $html, $m);

        return isset($m[1]) ? html_entity_decode($m[1]) : null;
    }

    public function test_a_closed_campaign_previews_its_own_card_not_the_site_banner(): void
    {
        $this->campaign(['is_active' => false]);

        $html = $this->get('/jsb')->assertOk()->getContent();

        $image = $this->meta($html, 'og:image');
        $this->assertStringContainsString('/event-share/jsb.jpg', $image);
        $this->assertStringNotContainsString('og-banner', $image);
        $this->assertSame('1200', $this->meta($html, 'og:image:width'));
        $this->assertSame('630', $this->meta($html, 'og:image:height'));
        $this->assertSame('image/jpeg', $this->meta($html, 'og:image:type'));
        $this->assertStringEndsWith('/jsb', $this->meta($html, 'og:url'));
        $this->assertStringContainsString('Joyee Shikkha Britti Application Form', $this->meta($html, 'og:title'));
        $this->assertSame('Scholarship for HSC and Honours students.', $this->meta($html, 'og:description'));
    }

    public function test_the_open_form_shares_the_same_card_and_its_own_address(): void
    {
        $this->campaign();

        $html = $this->get('/jsb')->assertOk()->getContent();

        $this->assertStringContainsString('/event-share/jsb.jpg', $this->meta($html, 'og:image'));
        $this->assertStringEndsWith('/jsb', $this->meta($html, 'og:url'), 'not the old fixed /scholarship address');
        $this->assertStringContainsString('/event-share/jsb.jpg', $html);
    }

    public function test_the_card_is_a_1200_by_630_jpeg_with_or_without_a_banner(): void
    {
        $campaign = $this->campaign();

        $res = $this->get(route('event.share-image', 'jsb'))->assertOk();
        $this->assertSame('image/jpeg', $res->headers->get('Content-Type'));
        $size = getimagesizefromstring($res->baseResponse->getFile()->getContent());
        $this->assertSame([1200, 630], [$size[0], $size[1]], 'themed card when there is no banner');

        Storage::fake('public');
        foreach ([[1200, 630], [2400, 300], [800, 1200]] as [$w, $h]) {
            Storage::disk('public')->put("banners/b-$w.png", $this->png($w, $h));
            $campaign->update(['banner_image' => "banners/b-$w.png"]);

            $res = $this->get($campaign->fresh()->shareImageUrl())->assertOk();
            $size = getimagesizefromstring($res->baseResponse->getFile()->getContent());
            $this->assertSame([1200, 630], [$size[0], $size[1]], "banner {$w}x{$h} becomes a 1200x630 card");
        }
    }

    public function test_the_card_address_changes_when_the_banner_or_title_changes(): void
    {
        $campaign = $this->campaign();
        $before = $campaign->shareImageUrl();

        $campaign->update(['title' => 'Joyee Shikkha Britti 2027']);

        $this->assertNotSame($before, $campaign->fresh()->shareImageUrl());
    }

    public function test_an_unknown_campaign_has_no_card(): void
    {
        $this->get('/event-share/nothing-here.jpg')->assertNotFound();
    }

    public function test_a_student_who_already_applied_can_find_their_form_while_applications_are_closed(): void
    {
        $this->campaign(['is_active' => false]);

        $html = $this->get('/jsb')->assertOk()->getContent();

        $this->assertStringContainsString('id="closedLookupForm"', $html);
        $this->assertStringContainsString(route('event.lookup', 'jsb'), $html);
        $this->assertStringContainsString('Already applied?', $html);
        preg_match('#<div class="container py-4 py-md-5">.*?</script>#s', $html, $body);
        $this->assertNotEmpty($body, 'found the closed-page content');
        $this->assertDoesNotMatchRegularExpression('/[ঀ-৿]/u', $body[0], 'the closed scholarship page is English only');

        $this->postJson(route('event.lookup', 'jsb'), ['name' => 'Nobody Here', 'phone' => '01700000000'])
            ->assertOk()->assertJson(['found' => false]);
    }

    public function test_closed_reasons_are_explained_in_plain_words(): void
    {
        $this->campaign(['is_active' => true, 'ends_at' => now()->subDay()]);
        $this->get('/jsb')->assertOk()->assertSee('Applications are closed: the deadline has passed');

        EventCampaign::where('slug', 'jsb')->update(['ends_at' => null, 'is_active' => false]);
        $this->get('/jsb')->assertOk()->assertSee('Applications are closed');
    }

    public function test_a_second_application_with_the_same_mobile_gets_an_english_message(): void
    {
        $campaign = $this->campaign();
        \App\Models\EventRegistration::create([
            'event_campaign_id' => $campaign->id, 'registration_number' => 'JSB-1', 'name' => 'Test Student',
            'phone' => '01711222333', 'status' => 'pending', 'form_data' => [],
        ]);

        $this->post('/jsb', ['name' => 'Test Student', 'phone' => '01711222333'])
            ->assertSessionHasErrors();   // incomplete form is rejected before any duplicate check: nothing Bengali is shown

        $text = (new \ReflectionMethod(\App\Http\Controllers\PublicEventRegistrationController::class, 'alreadyRegisteredMessage'));
        $text->setAccessible(true);
        $msg = $text->invoke(new \App\Http\Controllers\PublicEventRegistrationController(), $campaign, 'JSB-1');
        $this->assertSame('You have already applied with this mobile number. Application No: #JSB-1', $msg);

        $event = EventCampaign::create(['slug' => 'fest-2', 'title' => 'Fest', 'type' => 'event', 'is_active' => true, 'form_settings' => []]);
        $bn = $text->invoke(new \App\Http\Controllers\PublicEventRegistrationController(), $event, 'F-1');
        $this->assertMatchesRegularExpression('/[ঀ-৿]/u', $bn, 'other campaigns keep their Bengali wording');
    }

    public function test_other_kinds_of_closed_campaign_keep_their_bengali_page(): void
    {
        EventCampaign::create(['slug' => 'fest-3', 'title' => 'Fest', 'type' => 'event', 'is_active' => false, 'form_settings' => []]);

        $this->get('/fest-3')->assertOk()->assertSee('আবেদন এখন বন্ধ আছে')->assertDontSee('closedLookupForm', false);
    }
}
