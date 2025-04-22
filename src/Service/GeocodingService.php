<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private string $googleApiKey;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, string $googleApiKey)
    {
        $this->httpClient = $httpClient;
        $this->googleApiKey = $googleApiKey;
    }

    public function geocode(string $address): ?array
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json';
        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'address' => $address,
                'key' => $this->googleApiKey,
            ]
        ]);

        $data = $response->toArray();

        if ($data['status'] !== 'OK' || empty($data['results'][0]['geometry']['location'])) {
            return null;
        }

        return $data['results'][0]['geometry']['location'];
    }
}