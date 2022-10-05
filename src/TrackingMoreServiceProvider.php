<?php

declare(strict_types=1);

namespace ParcelTrap\TrackingMore;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use ParcelTrap\Contracts\Factory;
use ParcelTrap\ParcelTrap;

class TrackingMoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var ParcelTrap $factory */
        $factory = $this->app->make(Factory::class);

        $factory->extend(TrackingMore::IDENTIFIER, function () {
            /** @var Repository $config */
            $config = $this->app->make(Repository::class);

            return new TrackingMore(
                /** @phpstan-ignore-next-line */
                apiKey: (string) $config->get('parceltrap.trackingmore.api_key'),
            );
        });
    }
}
