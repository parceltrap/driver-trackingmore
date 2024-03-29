<?php

declare(strict_types=1);

namespace ParcelTrap\TrackingMore;

use DateTimeImmutable;
use GrahamCampbell\GuzzleFactory\GuzzleFactory;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use ParcelTrap\Contracts\Driver;
use ParcelTrap\DTOs\TrackingDetails;
use ParcelTrap\Enums\Status;

class TrackingMore implements Driver
{
    public const IDENTIFIER = 'trackingmore';

    public const BASE_URI = 'https://api.trackingmore.com';

    private ClientInterface $client;

    public function __construct(private readonly string $apiKey, ?ClientInterface $client = null)
    {
        $this->client = $client ?? GuzzleFactory::make(['base_uri' => self::BASE_URI]);
    }

    public function find(string $identifier, array $parameters = []): TrackingDetails
    {
        $request = $this->client->request('GET', '/v3/trackings/get', [
            RequestOptions::HEADERS => $this->getHeaders(),
            RequestOptions::QUERY => array_merge(['tracking_numbers' => $identifier], $parameters),
        ]);

        /** @var array $json */
        $json = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        assert(
            isset($json['code']) && ($json['code'] !== 203 && $json['code'] < 400),
            $json['message'] ?? 'An unknown error occurred'
        );
        assert(isset($json['data']) && $json['data'] !== null, 'No data was set on the response');
        assert(isset($json['data'][0]), 'No shipment could be found with this id');
        $json = $json['data'][0];

        assert(isset($json['tracking_number']), 'The identifier is missing from the response');
        assert(isset($json['delivery_status']), 'The status is missing from the response');

        return new TrackingDetails(
            identifier: $json['tracking_number'],
            status: $this->mapStatus($json['delivery_status']),
            summary: $json['status_info'] ?? null,
            estimatedDelivery: isset($json['scheduled_delivery_date']) ? new DateTimeImmutable($json['scheduled_delivery_date']) : null,
            events: [],
            raw: $json,
        );
    }

    private function mapStatus(string $status): Status
    {
        return match ($status) {
            'pending' => Status::Pending,
            'notfound' => Status::Not_Found,
            'transit', 'pickup' => Status::In_Transit,
            'delivered' => Status::Delivered,
            'undelivered' => Status::Failure,
            default => Status::Unknown,
        };
    }

    /**
     * @param  array<string, mixed>  $headers
     * @return array<string, mixed>
     */
    private function getHeaders(array $headers = []): array
    {
        return array_merge([
            'Tracking-Api-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ], $headers);
    }
}
