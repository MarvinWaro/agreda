<?php

namespace App\Jobs;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\Booking;
use App\Services\FacebookService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class NotifyGuestOfDecision implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Booking $booking) {}

    /**
     * Backoff between retries, in seconds.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(FacebookService $facebook): void
    {
        $this->booking->loadMissing('sport');

        $facebook->sendGuestMessage(
            $this->booking->guest_phone,
            sprintf(
                'Your AGREDA booking for %s on %s %s has been %s.',
                $this->booking->sport->name,
                $this->booking->booking_date->format('M j'),
                CarbonImmutable::parse($this->booking->start_time)->format('g:i A'),
                $this->booking->status->value,
            ),
        );

        $this->booking->notifications()->create([
            'channel' => NotificationChannel::Facebook,
            'status' => NotificationStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->booking->notifications()->create([
            'channel' => NotificationChannel::Facebook,
            'status' => NotificationStatus::Failed,
        ]);
    }
}
