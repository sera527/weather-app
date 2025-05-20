<?php

namespace App\Services\Weather;

use App\Exceptions\CityNotFound;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WeatherApiClient
{
    private const int ERROR_NO_LOCATION_FOUND = 1006;

    private ClientInterface $client;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('weather-api.apiKey');
        $this->baseUrl = 'https://api.weatherapi.com/v1';
    }

    public function getCurrentWeather(string $city): array
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/current.json", [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $city,
                    'aqi' => 'no',
                ],
            ]);

            return $this->getDecodedBody($response);
        } catch (GuzzleException $e) {
            if (
                $e->getCode() === 400
                && $this->getDecodedBody($e->getResponse())['error']['code'] === self::ERROR_NO_LOCATION_FOUND
            ) {
                throw new CityNotFound();
            }

            Log::error("Failed to get weather data: {$e->getMessage()}");
            throw new HttpException(400, 'Invalid request.');
        }
    }

    private function getDecodedBody(ResponseInterface $response): array
    {
        return json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
    }
}
