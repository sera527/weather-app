<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetWeatherRequest;
use App\Services\Weather\CurrentWeatherService;
use Illuminate\Http\JsonResponse;

class WeatherController extends Controller
{
    public function __construct(private readonly CurrentWeatherService $weatherService) {}

    public function index(GetWeatherRequest $request): JsonResponse
    {
        $weatherData = $this->weatherService->getWeatherForCity($request->input('city'));

        return response()->json($weatherData);
    }
}
