<?php

namespace App\Repositories;

use App\Enums\FrequencyType;
use App\Models\WeatherSubscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class WeatherSubscriptionRepository
{
    /**
     * Створення нової підписки на погоду
     *
     * @param array $data
     * @return WeatherSubscription
     */
    public function create(array $data): WeatherSubscription
    {
        // Генеруємо унікальний токен для підписки
        $data['token'] = Str::random(64);

        return WeatherSubscription::create($data);
    }

    /**
     * Перевірка чи існує підписка з заданою email-адресою
     *
     * @param string $email
     * @return bool
     */
    public function exists(string $email): bool
    {
        return WeatherSubscription::where('email', $email)->exists();
    }

    /**
     * Отримати існуючу підписку за email
     *
     * @param string $email
     * @return WeatherSubscription|null
     */
    public function getByEmail(string $email): ?WeatherSubscription
    {
        return WeatherSubscription::where('email', $email)->first();
    }

    /**
     * Отримання підписок, що потребують відправки
     *
     * @param FrequencyType $frequency
     * @return Collection
     */
    public function getSubscriptionsToSend(FrequencyType $frequency): Collection
    {
        return WeatherSubscription::active()
            ->where('frequency', $frequency)
            ->get();
    }

    /**
     * Оновлення часу останньої відправки
     *
     * @param WeatherSubscription $subscription
     * @return bool
     */
    public function updateLastSentAt(WeatherSubscription $subscription): bool
    {
        $subscription->last_sent_at = now();
        return $subscription->save();
    }

    /**
     * Скасування підписки за токеном
     *
     * @param string $token
     * @return bool
     */
    public function cancelByToken(string $token): bool
    {
        $subscription = WeatherSubscription::where('token', $token)->first();

        if (!$subscription) {
            return false;
        }

        $subscription->delete();

        return true;
    }

    /**
     * Активація підписки за токеном
     *
     * @param string $token
     * @return WeatherSubscription|null
     */
    public function confirmByToken(string $token): ?WeatherSubscription
    {
        $subscription = WeatherSubscription::where('token', $token)->first();

        if (!$subscription) {
            return null;
        }

        $subscription->is_active = true;
        $subscription->save();

        return $subscription;
    }

    /**
     * Отримання підписки за токеном
     *
     * @param string $token
     * @return WeatherSubscription|null
     */
    public function getByToken(string $token): ?WeatherSubscription
    {
        return WeatherSubscription::where('token', $token)->first();
    }
}
