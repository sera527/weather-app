<?php

namespace App\Services\Weather;

use App\Enums\FrequencyType;
use App\Mail\WeatherUpdate;
use App\Models\WeatherSubscription;
use App\Repositories\WeatherSubscriptionRepository;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class WeatherNotificationService
{
    public function __construct(
        private readonly CurrentWeatherService $weatherService,
        private readonly WeatherSubscriptionRepository $subscriptionRepository
    ) {}

    public function sendWeatherUpdates(FrequencyType $frequency): array
    {
        try {
            // Отримуємо активні підписки для заданої частоти
            $subscriptions = $this->subscriptionRepository->getSubscriptionsToSend($frequency);

            if ($subscriptions->isEmpty()) {
                return [
                    'success' => true,
                    'message' => "No active subscriptions found for {$frequency->value} updates.",
                    'sent' => 0
                ];
            }

            // Групуємо підписки за містами для оптимізації запитів погоди
            $groupedSubscriptions = $this->groupSubscriptionsByCity($subscriptions);

            $sent = 0;
            $failed = 0;

            // Відправляємо оновлення для кожного міста
            foreach ($groupedSubscriptions as $city => $citySubscriptions) {
                try {
                    // Отримуємо погоду для міста
                    $weatherData = $this->weatherService->getWeatherForCity($city);

                    // Відправляємо електронні листи кожному підписнику цього міста
                    foreach ($citySubscriptions as $subscription) {
                        try {
                            $this->sendWeatherUpdateEmail($subscription, $weatherData);

                            // Оновлюємо час останньої відправки
                            $this->subscriptionRepository->updateLastSentAt($subscription);

                            $sent++;
                        } catch (Exception $e) {
                            Log::error("Failed to send weather update to {$subscription->email}: {$e->getMessage()}");
                            $failed++;
                        }
                    }
                } catch (Exception $e) {
                    Log::error("Failed to get weather data for {$city}: {$e->getMessage()}");
                    $failed += count($citySubscriptions);
                }
            }

            return [
                'success' => true,
                'message' => "Sent {$sent} {$frequency->value} weather updates. Failed: {$failed}.",
                'sent' => $sent,
                'failed' => $failed
            ];

        } catch (Exception $e) {
            Log::error("Error sending {$frequency->value} weather updates: {$e->getMessage()}");

            return [
                'success' => false,
                'message' => "Error sending weather updates: {$e->getMessage()}",
                'sent' => 0
            ];
        }
    }

    /**
     * Групує підписки за містами
     *
     * @param Collection $subscriptions
     * @return array
     */
    private function groupSubscriptionsByCity(Collection $subscriptions): array
    {
        $groupedSubscriptions = [];

        foreach ($subscriptions as $subscription) {
            if (!isset($groupedSubscriptions[$subscription->city])) {
                $groupedSubscriptions[$subscription->city] = [];
            }

            $groupedSubscriptions[$subscription->city][] = $subscription;
        }

        return $groupedSubscriptions;
    }

    /**
     * Відправити електронний лист з погодним оновленням
     *
     * @param WeatherSubscription $subscription
     * @param array $weatherData
     * @return void
     */
    private function sendWeatherUpdateEmail(WeatherSubscription $subscription, array $weatherData): void
    {
        $unsubscribeUrl = URL::route('api.unsubscribe', ['token' => $subscription->token]);

        Mail::to($subscription->email)
            ->send(new WeatherUpdate($subscription, $weatherData, $unsubscribeUrl));
    }
}
