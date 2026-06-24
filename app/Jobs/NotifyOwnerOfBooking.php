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

class NotifyOwnerOfBooking implements ShouldQueue
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

        $facebook->sendOwnerMessage(sprintf(
            'New booking request: %s on %s %s. From %s (%s).',
            $this->booking->sport->name,
            $this->booking->booking_date->format('M j'),
            CarbonImmutable::parse($this->booking->start_time)->format('g:i A'),
            $this->booking->guest_name,
            $this->booking->guest_phone,
        ));

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
