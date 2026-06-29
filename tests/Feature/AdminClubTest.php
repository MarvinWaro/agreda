<?php

use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ClubRole;
use App\Models\Sport;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('an owner can view the clubs index', function () {
    Club::factory()->count(2)->create();

    $this->actingAs(User::factory()->owner()->create())
        ->get(route('admin.clubs.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/clubs')
            ->has('clubs', 2)
            ->has('sports'));
});

test('a user without clubs.manage cannot reach the clubs index', function () {
    $this->actingAs(User::factory()->staff()->create())
        ->get(route('admin.clubs.index'))
        ->assertForbidden();
});

test('an owner can view a club detail page with its roles', function () {
    $club = Club::factory()->create();
    ClubRole::factory()->for($club)->default()->create(['name' => 'Member']);
    ClubRole::factory()->for($club)->create(['name' => 'President']);

    $this->actingAs(User::factory()->owner()->create())
        ->get(route('admin.clubs.show', $club))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/club-detail')
            ->where('club.id', $club->id)
            ->has('roles', 2));
});

test('an owner can create a club', function () {
    $sport = Sport::factory()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.clubs.index'))
        ->post(route('admin.clubs.store'), [
            'name' => 'Volleyball Club',
            'sport_id' => $sport->id,
            'description' => 'A club for volleyball players.',
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.clubs.index'));

    $club = Club::sole();

    expect($club->name)->toBe('Volleyball Club')
        ->and($club->slug)->toBe('volleyball-club')
        ->and($club->sport_id)->toBe($sport->id)
        ->and($club->is_active)->toBeTrue();
});

test('a club membership fee persists on create and update and is nullable', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.clubs.index'))
        ->post(route('admin.clubs.store'), [
            'name' => 'Volleyball Club',
            'membership_fee' => 150,
            'is_active' => true,
        ]);

    $club = Club::sole();
    expect($club->membership_fee)->toBe('150.00');

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.clubs.index'))
        ->put(route('admin.clubs.update', $club), [
            'name' => $club->name,
            'membership_fee' => null,
            'is_active' => true,
        ]);

    expect($club->refresh()->membership_fee)->toBeNull();
});

test('creating a club validates the name', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.clubs.index'))
        ->post(route('admin.clubs.store'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('an owner can update a club', function () {
    $club = Club::factory()->create(['name' => 'Old Name']);

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.clubs.index'))
        ->put(route('admin.clubs.update', $club), [
            'name' => 'New Name',
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.clubs.index'));

    expect($club->refresh()->name)->toBe('New Name')
        ->and($club->is_active)->toBeFalse();
});

test('deleting a club cascades its roles and members', function () {
    $club = Club::factory()->create();
    $role = ClubRole::factory()->for($club)->create();
    $member = ClubMember::factory()->for($club)->create();

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.clubs.index'))
        ->delete(route('admin.clubs.destroy', $club))
        ->assertRedirect(route('admin.clubs.index'));

    expect(Club::query()->find($club->id))->toBeNull()
        ->and(ClubRole::query()->find($role->id))->toBeNull()
        ->and(ClubMember::query()->find($member->id))->toBeNull();
});
