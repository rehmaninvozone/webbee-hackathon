<?php

namespace Tests;

use App\Traits\RefreshDatabaseOnce;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabaseOnce;
}
