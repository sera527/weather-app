<?php

namespace App\Mail;

use App\Enums\FrequencyType;
use App\Models\WeatherSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeatherUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public WeatherSubscription $subscription,
        public array $weatherData,
        public string $unsubscribeUrl,
    ) {}

    public function envelope(): Envelope
    {
        $frequency = $this->subscription->frequency === FrequencyType::HOURLY ? 'Hourly' : 'Daily';

        return new Envelope(
            subject: "{$frequency} Weather Update for {$this->subscription->city}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weather-update',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
