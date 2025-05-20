<?php

namespace App\Repositories;

use App\Enums\FrequencyType;
use App\Models\WeatherSubscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class WeatherSubscriptionRepository
{
    public function create(array $data): WeatherSubscription
    {
        // Генеруємо унікальний токен для підписки
        $data['token'] = Str::random(64);

        return WeatherSubscription::create($data);
    }

    public function exists(string $email): bool
    {
        return WeatherSubscription::where('email', $email)->exists();
    }

    public function getByEmail(string $email): ?WeatherSubscription
    {
        return WeatherSubscription::where('email', $email)->first();
    }

    public function getSubscriptionsToSend(FrequencyType $frequency): Collection
    {
        return WeatherSubscription::active()
            ->where('frequency', $frequency)
            ->get();
    }

    public function updateLastSentAt(WeatherSubscription $subscription): bool
    {
        $subscription->last_sent_at = now();

        return $subscription->save();
    }

    public function cancelByToken(string $token): bool
    {
        $subscription = WeatherSubscription::where('token', $token)->first();

        if (!$subscription) {
            return false;
        }

        $subscription->delete();

        return true;
    }

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
}
