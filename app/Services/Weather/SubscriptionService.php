<?php

namespace App\Services\Weather;

use App\Mail\SubscriptionConfirmation;
use App\Models\WeatherSubscription;
use App\Repositories\WeatherSubscriptionRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionService
{
    public function __construct(
        private readonly WeatherSubscriptionRepository $repository,
        private readonly CurrentWeatherService $weatherService,
    ) {}

    public function subscribe(string $email, string $city, string $frequency): array
    {
        $existingSubscription = $this->repository->getByEmail($email);

        if ($existingSubscription) {
            throw new HttpException(409, 'Email already subscribed.');
        }

        if (!$this->weatherService->isCityExists($city)) {
            throw new HttpException(400, 'Invalid input.');
        }

        $subscription = $this->repository->create([
            'email' => $email,
            'city' => $city,
            'frequency' => $frequency,
        ]);

        $this->sendConfirmationEmail($subscription);

        return [
            'success' => true,
            'message' => 'Subscription successful. Confirmation email sent.',
        ];
    }

    private function sendConfirmationEmail(WeatherSubscription $subscription): void
    {
        try {
            $confirmUrl = URL::route('api.confirm', ['token' => $subscription->token]);
            $unsubscribeUrl = URL::route('api.unsubscribe', ['token' => $subscription->token]);

            Mail::to($subscription->email)
                ->send(new SubscriptionConfirmation($subscription, $confirmUrl, $unsubscribeUrl));

            Log::info("Confirmation email sent to {$subscription->email}");
        } catch (Exception $e) {
            Log::error("Failed to send confirmation email: {$e->getMessage()}");
        }
    }

    public function confirmSubscription(string $token): array
    {
        $subscription = $this->repository->confirmByToken($token);

        if (!$subscription) {
            throw new HttpException(400, 'Invalid token.');
        }

        return [
            'success' => true,
            'message' => 'Subscription confirmed successfully',
        ];
    }

    public function unsubscribe(string $token): bool
    {
        return $this->repository->cancelByToken($token);
    }
}
