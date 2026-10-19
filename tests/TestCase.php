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

        $conn = config('database.default');
        $db = (string) config("database.connections.$conn.database");

        if ($conn !== 'sqlite' || $db !== ':memory:') {
            $this->fail("Refusing to run tests on '$conn' / '$db'. Use the in-memory sqlite database from phpunit.xml.");
        }
    }
}