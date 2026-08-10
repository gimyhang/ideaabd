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
}
