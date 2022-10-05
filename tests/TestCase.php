<?php

declare(strict_types=1);

namespace ParcelTrap\TrackingMore\Tests;

use ParcelTrap\ParcelTrapServiceProvider;
use ParcelTrap\TrackingMore\TrackingMoreServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ParcelTrapServiceProvider::class, TrackingMoreServiceProvider::class];
    }
}
