<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    private static bool $optimizeCleared = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! self::$optimizeCleared) {
            $this->artisan('optimize:clear');
            self::$optimizeCleared = true;
        }
    }
}
