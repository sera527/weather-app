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
        private readonly WeatherSubscriptionRepository $subscriptionRepository,
    ) {}

    public function sendWeatherUpdates(FrequencyType $frequency): array
    {
        try {
            $subscriptions = $this->subscriptionRepository->getSubscriptionsToSend($frequency);

            if ($subscriptions->isEmpty()) {
                return [
                    'success' => true,
                    'message' => "No active subscriptions found for {$frequency->value} updates.",
                    'sent' => 0,
                ];
            }

            $groupedSubscriptions = $this->groupSubscriptionsByCity($subscriptions);

            $sent = 0;
            $failed = 0;

            foreach ($groupedSubscriptions as $city => $citySubscriptions) {
                try {
                    $weatherData = $this->weatherService->getWeatherForCity($city);

                    foreach ($citySubscriptions as $subscription) {
                        try {
                            $this->sendWeatherUpdateEmail($subscription, $weatherData);

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
                'failed' => $failed,
            ];
        } catch (Exception $e) {
            Log::error("Error sending {$frequency->value} weather updates: {$e->getMessage()}");

            return [
                'success' => false,
                'message' => "Error sending weather updates: {$e->getMessage()}",
                'sent' => 0,
            ];
        }
    }

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

    private function sendWeatherUpdateEmail(WeatherSubscription $subscription, array $weatherData): void
    {
        $unsubscribeUrl = URL::route('api.unsubscribe', ['token' => $subscription->token]);

        Mail::to($subscription->email)
            ->send(new WeatherUpdate($subscription, $weatherData, $unsubscribeUrl));
    }
}
