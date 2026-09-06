<?php

namespace Tests\Feature;

use Tests\TestCase;

class BookshopPageTest extends TestCase
{
    public function test_home_page_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_about_page_is_accessible(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
    }

    public function test_contact_page_is_accessible(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
    }

    public function test_faq_page_is_accessible(): void
    {
        $response = $this->get('/faq');

        $response->assertStatus(200);
        $response->assertSee('সচরাচর জিজ্ঞাসিত প্রশ্নোত্তর');
    }

    public function test_documents_page_is_accessible(): void
    {
        $response = $this->get('/documents');

        $response->assertStatus(200);
        $response->assertSee('নথি, ফর্ম ও প্রকাশনা গাইডলাইন');
    }

    public function test_contact_submit_is_working(): void
    {
        $response = $this->postJson('/contact/submit', [
            'name'    => 'Test User',
            'phone'   => '01711223344',
            'email'   => 'test@example.com',
            'subject' => 'বই অর্ডার ও ডেলিভারি জিজ্ঞাসা',
            'message' => 'আমি একটি নতুন বই অর্ডার করতে চাই।',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }
}
