<?php

namespace App\Services\Weather;

use App\Exceptions\CityNotFound;

class CurrentWeatherService
{
    public function __construct(private readonly WeatherApiClient $apiClient) {}

    public function getWeatherForCity(string $city): array
    {
        $weatherData = $this->apiClient->getCurrentWeather($city);

        return [
            'temperature' => $weatherData['current']['temp_c'],
            'humidity' => $weatherData['current']['humidity'],
            'description' => $weatherData['current']['condition']['text'],
        ];
    }

    public function isCityExists(string $city): bool
    {
        try {
            $this->apiClient->getCurrentWeather($city);
        } catch (CityNotFound) {
            return false;
        }

        return true;
    }
}
