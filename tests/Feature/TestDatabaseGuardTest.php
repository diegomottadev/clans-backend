<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TestDatabaseGuardTest extends TestCase
{
    public function test_los_tests_usan_la_base_clans_test()
    {
        $this->assertSame('clans_test', DB::connection()->getDatabaseName());
    }
}
