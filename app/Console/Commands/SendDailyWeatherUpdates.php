<?php

namespace App\Console\Commands;

use App\Enums\FrequencyType;
use App\Services\Weather\WeatherNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDailyWeatherUpdates extends Command
{
    protected $signature = 'weather:send-daily';

    protected $description = 'Send daily weather updates to active subscribers';

    public function handle(WeatherNotificationService $notificationService): int
    {
        $this->info('Starting daily weather updates...');

        $result = $notificationService->sendWeatherUpdates(FrequencyType::DAILY);

        if ($result['success']) {
            $this->info($result['message']);
            Log::info($result['message']);
        } else {
            $this->error($result['message']);
            Log::error($result['message']);
        }

        return $result['success'] ? 0 : 1;
    }
}
