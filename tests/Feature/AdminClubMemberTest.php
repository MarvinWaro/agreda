<?php

use App\Enums\ClubMemberStatus;
use App\Jobs\NotifyApplicantOfDecision;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubRole;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

test('the club members index can filter by club and status', function () {
    $club = Club::factory()->create();
    ClubMember::factory()->for($club)->pending()->create();
    ClubMember::factory()->for($club)->approved()->create();
    ClubMember::factory()->approved()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->get(route('admin.club-members.index', ['club_id' => $club->id, 'status' => 'approved']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/club-members')
            ->has('members.data', 1)
            ->where('filters.club_id', $club->id)
            ->where('filters.status', 'approved'));
});

test('approving a pending member assigns the club default role', function () {
    $club = Club::factory()->create();
    $defaultRole = ClubRole::factory()->for($club)->default()->create(['name' => 'Member']);
    $member = ClubMember::factory()->for($club)->pending()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.club-members.index'))
        ->patch(route('admin.club-members.approve', $member))
        ->assertRedirect(route('admin.club-members.index'));

    $member->refresh();

    expect($member->status)->toBe(ClubMemberStatus::Approved)
        ->and($member->reviewed_at)->not->toBeNull()
        ->and($member->club_role_id)->toBe($defaultRole->id);
});

test('declining a pending member leaves the role unassigned', function () {
    $member = ClubMember::factory()->pending()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->patch(route('admin.club-members.decline', $member));

    $member->refresh();

    expect($member->status)->toBe(ClubMemberStatus::Declined)
        ->and($member->club_role_id)->toBeNull();
});

test('a non-pending member is not changed by approve and no notification is dispatched', function () {
    Queue::fake();
    $member = ClubMember::factory()->declined()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->patch(route('admin.club-members.approve', $member));

    expect($member->refresh()->status)->toBe(ClubMemberStatus::Declined);
    Queue::assertNotPushed(NotifyApplicantOfDecision::class);
});

test('approving a pending member dispatches the applicant notification', function () {
    Queue::fake();
    $member = ClubMember::factory()->pending()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->patch(route('admin.club-members.approve', $member));

    Queue::assertPushed(NotifyApplicantOfDecision::class, 1);
});

test('an owner can reassign the role of an approved member', function () {
    $club = Club::factory()->create();
    $role = ClubRole::factory()->for($club)->create(['name' => 'Treasurer']);
    $member = ClubMember::factory()->for($club)->approved()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->put(route('admin.club-members.update', $member), ['club_role_id' => $role->id]);

    expect($member->refresh()->club_role_id)->toBe($role->id);
});

test('an owner can toggle fee paid without disturbing the assigned role', function () {
    $club = Club::factory()->create(['membership_fee' => 200]);
    $role = ClubRole::factory()->for($club)->create(['name' => 'Treasurer']);
    $member = ClubMember::factory()->for($club)->approved()->create(['club_role_id' => $role->id]);

    $this->actingAs(User::factory()->owner()->create())
        ->put(route('admin.club-members.update', $member), ['fee_paid' => true]);

    $member->refresh();
    expect($member->fee_paid_at)->not->toBeNull()
        ->and($member->club_role_id)->toBe($role->id);

    $this->actingAs(User::factory()->owner()->create())
        ->put(route('admin.club-members.update', $member), ['fee_paid' => false]);

    expect($member->refresh()->fee_paid_at)->toBeNull()
        ->and($member->club_role_id)->toBe($role->id);
});

test('a user without clubs.manage cannot review club members', function () {
    $member = ClubMember::factory()->pending()->create();

    $this->actingAs(User::factory()->staff()->create())
        ->patch(route('admin.club-members.approve', $member))
        ->assertForbidden();
});
