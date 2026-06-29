<?php

use App\Enums\ClubMemberStatus;
use App\Jobs\NotifyClubOfApplication;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

test('the clubs listing shows only active clubs', function () {
    Club::factory()->count(2)->create();
    Club::factory()->inactive()->create();

    $this->get(route('clubs.index'))
        ->assertInertia(fn (Assert $page) => $page->component('public/clubs')->has('clubs', 2));
});

test('the join form renders for an active club', function () {
    $club = Club::factory()->create();

    $this->get(route('club.join.create', $club))
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/club-join')
            ->where('club.slug', $club->slug));
});

test('a visitor can submit a membership application and gets an account', function () {
    Queue::fake();
    $club = Club::factory()->create();

    $response = $this->post(route('club.join.store', $club), [
        'full_name' => 'Juan Dela Cruz',
        'age' => 25,
        'sex' => 'male',
        'occupation' => 'Driver',
        'address' => 'Brgy. Example, Agreda',
        'phone' => '09171234567',
        'notes' => 'Plays point guard',
        'email' => 'juan@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $member = ClubMember::sole();
    $user = User::sole();

    $response->assertRedirect(route('club.join.done', $member));

    expect($member->club_id)->toBe($club->id)
        ->and($member->status)->toBe(ClubMemberStatus::Pending)
        ->and($member->club_role_id)->toBeNull()
        ->and($member->full_name)->toBe('Juan Dela Cruz')
        ->and($member->user_id)->toBe($user->id)
        ->and($user->email)->toBe('juan@example.com');

    $this->assertGuest();

    Queue::assertPushed(NotifyClubOfApplication::class, 1);
});

test('membership application validation requires the key fields', function () {
    $club = Club::factory()->create();

    $this->from(route('club.join.create', $club))
        ->post(route('club.join.store', $club), [])
        ->assertSessionHasErrors(['full_name', 'age', 'sex', 'occupation', 'address', 'phone', 'email', 'password']);
});

test('a membership application is rejected when the email is already registered', function () {
    $club = Club::factory()->create();
    $existing = User::factory()->create(['email' => 'taken@example.com']);

    $this->from(route('club.join.create', $club))
        ->post(route('club.join.store', $club), [
            'full_name' => 'Juan Dela Cruz',
            'age' => 25,
            'sex' => 'male',
            'occupation' => 'Driver',
            'address' => 'Brgy. Example, Agreda',
            'phone' => '09171234567',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertSessionHasErrors('email');

    expect(ClubMember::count())->toBe(0)
        ->and(User::count())->toBe(1)
        ->and($existing->id)->not->toBeNull();
});

test('an invalid sex value is rejected', function () {
    $club = Club::factory()->create();

    $this->from(route('club.join.create', $club))
        ->post(route('club.join.store', $club), [
            'full_name' => 'Juan Dela Cruz',
            'age' => 25,
            'sex' => 'other',
            'occupation' => 'Driver',
            'address' => 'Brgy. Example, Agreda',
            'phone' => '09171234567',
        ])
        ->assertSessionHasErrors('sex');

    expect(ClubMember::count())->toBe(0);
});

test('the confirmation page shows the applicant and club', function () {
    $club = Club::factory()->create();
    $member = ClubMember::factory()->for($club)->pending()->create(['full_name' => 'Mia Reyes']);

    $this->get(route('club.join.done', $member))
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/club-join-confirmation')
            ->where('member.full_name', 'Mia Reyes')
            ->where('member.club', $club->name)
            ->where('member.status', 'pending'));
});
