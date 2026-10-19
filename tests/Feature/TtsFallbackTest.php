<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TtsFallbackTest extends TestCase
{
    public function test_bengali_speech_is_fetched_once_and_cached(): void
    {
        Storage::fake('local');
        Http::fake(['translate.google.com/*' => Http::response('MP3DATA', 200)]);

        $q = 'বাংলা সাহিত্যের বিবর্তন।';
        $this->get(route('tts.bn', ['q' => $q]))->assertOk()->assertHeader('content-type', 'audio/mpeg');
        $this->get(route('tts.bn', ['q' => $q]))->assertOk();

        Http::assertSentCount(1);
    }

    public function test_rejects_empty_or_oversized_text_and_reports_provider_failure(): void
    {
        $this->get(route('tts.bn'))->assertStatus(422);
        $this->get(route('tts.bn', ['q' => str_repeat('অ', 201)]))->assertStatus(422);

        Storage::fake('local');
        Http::fake(['translate.google.com/*' => Http::response('', 500)]);
        $this->get(route('tts.bn', ['q' => 'পরীক্ষা']))->assertStatus(502);
    }

    public function test_article_page_ships_the_player_with_the_fallback_url(): void
    {
        $this->assertStringContainsString('data-tts-url', view('partials.audiobook-dock')->render());
    }
}
