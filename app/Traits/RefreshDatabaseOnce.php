<?php

namespace App\Traits;

trait RefreshDatabaseOnce
{
    private static bool $databaseRefreshed = false;

    public function setUp(): void
    {
        parent::setUp();

        if (!self::$databaseRefreshed) {
            $this->artisan('migrate:fresh --seed');
            self::$databaseRefreshed = true;
        }
    }
}
