<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Tests must only ever touch the throw-away in-memory database from phpunit.xml.
     * Abort immediately if the connection points anywhere else (protects real data).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSafeDatabase();
    }

    /**
     * RefreshDatabase runs migrate:fresh inside setUpTraits(), i.e. before setUp() continues, so the
     * guard must run first or a cached config (php artisan optimize) pointing at MySQL gets wiped.
     */
    protected function setUpTraits()
    {
        $this->assertSafeDatabase();

        return parent::setUpTraits();
    }

    private function assertSafeDatabase(): void
    {
        $conn = config('database.default');
        $db = (string) config("database.connections.$conn.database");

        if ($conn !== 'sqlite' || $db !== ':memory:') {
            throw new \RuntimeException("Refusing to run tests on '$conn' / '$db'. Use the in-memory sqlite database from phpunit.xml (run php artisan config:clear first).");
        }
    }
}