<?php

namespace App\Jobs;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\ClubMember;
use App\Services\FacebookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class NotifyApplicantOfDecision implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public ClubMember $member) {}

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
        $this->member->loadMissing('club');

        $facebook->sendGuestMessage(
            $this->member->phone,
            sprintf(
                'Your application to join %s has been %s.',
                $this->member->club->name,
                $this->member->status->value,
            ),
        );

        $this->member->notifications()->create([
            'channel' => NotificationChannel::Facebook,
            'status' => NotificationStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->member->notifications()->create([
            'channel' => NotificationChannel::Facebook,
            'status' => NotificationStatus::Failed,
        ]);
    }
}
