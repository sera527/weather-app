<?php

namespace App\Services\Weather;

use App\Enums\FrequencyType;
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

    /**
     * Підписати користувача на оновлення погоди
     *
     * @param string $email
     * @param string $city
     * @param string $frequency
     * @return array
     * @throws Exception
     */
    public function subscribe(string $email, string $city, string $frequency): array
    {
        // Перевіряємо, чи існує вже підписка з таким email
        $existingSubscription = $this->repository->getByEmail($email);

        if ($existingSubscription) {
            throw new HttpException(409, 'Email already subscribed.');
        }

        if (!$this->weatherService->isCityExists($city)) {
            throw new HttpException(400, 'Invalid input.');
        }

        // Створюємо нову підписку (неактивну)
        $subscription = $this->repository->create([
            'email' => $email,
            'city' => $city,
            'frequency' => $frequency,
        ]);

        // Відправляємо електронного листа з підтвердженням
        $this->sendConfirmationEmail($subscription);

        return [
            'success' => true,
            'message' => 'Subscription successful. Confirmation email sent.',
        ];
    }

    /**
     * Відправити електронного листа з підтвердженням підписки
     *
     * @param WeatherSubscription $subscription
     * @return void
     */
    private function sendConfirmationEmail(WeatherSubscription $subscription): void
    {
        try {
            // Генеруємо URL для підтвердження та відписки
            $confirmUrl = URL::route('api.confirm', ['token' => $subscription->token]);
            $unsubscribeUrl = URL::route('api.unsubscribe', ['token' => $subscription->token]);

            // Відправляємо електронного листа
            Mail::to($subscription->email)
                ->send(new SubscriptionConfirmation($subscription, $confirmUrl, $unsubscribeUrl));

            Log::info("Confirmation email sent to {$subscription->email}");
        } catch (Exception $e) {
            Log::error("Failed to send confirmation email: {$e->getMessage()}");
            // Продовжуємо виконання, навіть якщо відправка не вдалася
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
