<?php

declare(strict_types=1);

namespace ParcelTrap\TrackingMore;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use ParcelTrap\Contracts\Factory;
use ParcelTrap\ParcelTrap;

class TrackingMoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var ParcelTrap $factory */
        $factory = $this->app->make(Factory::class);

        $factory->extend(TrackingMore::IDENTIFIER, function (Container $container) {
            /** @var ConfigRepository $config */
            $config = $container->make(ConfigRepository::class);

            return new TrackingMore(
                /** @phpstan-ignore-next-line */
                apiKey: (string) $config->get('parceltrap.drivers.trackingmore.api_key'),
            );
        });
    }
}
