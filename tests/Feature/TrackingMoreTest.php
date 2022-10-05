<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ParcelTrap\Contracts\Factory;
use ParcelTrap\DTOs\TrackingDetails;
use ParcelTrap\Enums\Status;
use ParcelTrap\ParcelTrap;
use ParcelTrap\TrackingMore\TrackingMore;

it('can add the TrackingMore driver to ParcelTrap', function () {
    /** @var ParcelTrap $client */
    $client = $this->app->make(Factory::class);

    $client->extend('trackingmore_other', fn () => new TrackingMore(
        apiKey: 'abcdefg'
    ));

    expect($client)->driver(TrackingMore::IDENTIFIER)->toBeInstanceOf(TrackingMore::class)
        ->and($client)->driver('trackingmore_other')->toBeInstanceOf(TrackingMore::class);
});

it('can retrieve the TrackingMore driver from ParcelTrap', function () {
    expect($this->app->make(Factory::class)->driver(TrackingMore::IDENTIFIER))->toBeInstanceOf(TrackingMore::class);
});

it('can call `find` on the TrackingMore driver', function () {
    $trackingDetails = [
        'tracking_number' => 'UB209300714LV',
        'delivery_status' => 'transit',
        'status_info' => 'The parcel is currently in transit',
        'scheduled_delivery_date' => '2022-01-01T00:00:00+00:00',
    ];

    $httpMockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'data' => [$trackingDetails],
        ])),
    ]);

    $handlerStack = HandlerStack::create($httpMockHandler);

    $httpClient = new Client([
        'handler' => $handlerStack,
    ]);

    $this->app->make(Factory::class)->extend(TrackingMore::IDENTIFIER, fn () => new TrackingMore(
        apiKey: 'abcdefg',
        client: $httpClient,
    ));

    expect($this->app->make(Factory::class)->driver('trackingmore')->find('UB209300714LV'))
        ->toBeInstanceOf(TrackingDetails::class)
        ->identifier->toBe('UB209300714LV')
        ->status->toBe(Status::In_Transit)
        ->status->description()->toBe('In Transit')
        ->summary->toBe('The parcel is currently in transit')
        ->estimatedDelivery->toEqual(new DateTimeImmutable('2022-01-01T00:00:00+00:00'))
        ->raw->toBe($trackingDetails);
});
