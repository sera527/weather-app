<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetWeatherRequest;
use App\Services\Weather\CurrentWeatherService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    public function __construct(private readonly CurrentWeatherService $weatherService) {}

    public function index(GetWeatherRequest $request): JsonResponse
    {
        try {
            $weatherData = $this->weatherService->getWeatherForCity($request->input('city'));

            return response()->json($weatherData);
        } catch (Exception $e) {
            Log::error('Weather error: ' . $e->getMessage());

            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
