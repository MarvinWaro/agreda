<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Wraps outbound Facebook messaging so the delivery channel can be swapped
 * later (Messenger Send API / Graph API). Stubbed for now: logs only.
 */
class FacebookService
{
    public function sendOwnerMessage(string $message): void
    {
        Log::info('[FacebookService] owner message', ['message' => $message]);
    }

    public function sendGuestMessage(string $recipient, string $message): void
    {
        Log::info('[FacebookService] guest message', [
            'to' => $recipient,
            'message' => $message,
        ]);
    }
}
