<?php

use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubRole;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('an admin hitting dashboard is redirected to the admin dashboard', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('a club member sees their applications on the membership page', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create(['membership_fee' => 200]);
    $role = ClubRole::factory()->for($club)->create(['name' => 'Member']);
    $member = ClubMember::factory()->for($club)->approved()->create([
        'user_id' => $user->id,
        'club_role_id' => $role->id,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/membership')
            ->has('memberships', 1)
            ->where('memberships.0.id', $member->id)
            ->where('memberships.0.club', $club->name)
            ->where('memberships.0.role', 'Member')
            ->where('memberships.0.status', 'approved')
            ->where('memberships.0.fee_paid', false));
});

test('a logged-in user with no applications is redirected home', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('home'));
});
