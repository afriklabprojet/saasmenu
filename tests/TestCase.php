<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Force SQLite in-memory database for tests only if needed
        if (property_exists($this, 'needsDatabase') && $this->needsDatabase) {
            $this->setupDatabase();
        }
    }

    /**
     * Setup database for tests that need it
     */
    protected function setupDatabase(): void
    {
        config(['database.default' => 'testing']);
        config(['database.connections.testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);
    }
}
