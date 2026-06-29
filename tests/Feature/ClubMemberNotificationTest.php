<?php

use App\Jobs\NotifyApplicantOfDecision;
use App\Jobs\NotifyClubOfApplication;
use App\Models\ClubMember;
use App\Services\FacebookService;

use function Pest\Laravel\mock;

test('the club application notification job sends a message and records it', function () {
    $member = ClubMember::factory()->pending()->create();

    mock(FacebookService::class)
        ->shouldReceive('sendOwnerMessage')
        ->once();

    (new NotifyClubOfApplication($member))->handle(app(FacebookService::class));

    $this->assertDatabaseHas('club_member_notifications', [
        'club_member_id' => $member->id,
        'channel' => 'facebook',
        'status' => 'sent',
    ]);
});

test('the applicant decision notification job sends a message and records it', function () {
    $member = ClubMember::factory()->approved()->create();

    mock(FacebookService::class)
        ->shouldReceive('sendGuestMessage')
        ->once();

    (new NotifyApplicantOfDecision($member))->handle(app(FacebookService::class));

    $this->assertDatabaseHas('club_member_notifications', [
        'club_member_id' => $member->id,
        'channel' => 'facebook',
        'status' => 'sent',
    ]);
});
