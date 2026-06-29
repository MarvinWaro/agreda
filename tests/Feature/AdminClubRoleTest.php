<?php

use App\Models\Club;
use App\Models\ClubRole;
use App\Models\User;

test('an owner can add a role to a club', function () {
    $club = Club::factory()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.clubs.show', $club))
        ->post(route('admin.clubs.roles.store', $club), [
            'name' => 'Secretary',
            'is_default' => false,
        ])
        ->assertRedirect(route('admin.clubs.show', $club));

    expect($club->roles()->where('name', 'Secretary')->exists())->toBeTrue();
});

test('flagging a new role as default unsets the club previous default', function () {
    $club = Club::factory()->create();
    $current = ClubRole::factory()->for($club)->default()->create(['name' => 'Member']);

    $this->actingAs(User::factory()->owner()->create())
        ->post(route('admin.clubs.roles.store', $club), [
            'name' => 'President',
            'is_default' => true,
        ]);

    expect($current->refresh()->is_default)->toBeFalse()
        ->and($club->roles()->where('name', 'President')->first()->is_default)->toBeTrue();
});

test('updating a role to default unsets any other default on that club', function () {
    $club = Club::factory()->create();
    $member = ClubRole::factory()->for($club)->default()->create(['name' => 'Member']);
    $president = ClubRole::factory()->for($club)->create(['name' => 'President']);

    $this->actingAs(User::factory()->owner()->create())
        ->put(route('admin.clubs.roles.update', [$club, $president]), [
            'name' => 'President',
            'is_default' => true,
        ]);

    expect($member->refresh()->is_default)->toBeFalse()
        ->and($president->refresh()->is_default)->toBeTrue();
});

test('deleting the default role is blocked', function () {
    $club = Club::factory()->create();
    $role = ClubRole::factory()->for($club)->default()->create();

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.clubs.show', $club))
        ->delete(route('admin.clubs.roles.destroy', [$club, $role]))
        ->assertRedirect(route('admin.clubs.show', $club));

    expect(ClubRole::query()->find($role->id))->not->toBeNull();
});

test('deleting a non-default role succeeds', function () {
    $club = Club::factory()->create();
    $role = ClubRole::factory()->for($club)->create();

    $this->actingAs(User::factory()->owner()->create())
        ->delete(route('admin.clubs.roles.destroy', [$club, $role]));

    expect(ClubRole::query()->find($role->id))->toBeNull();
});

test('role names must be unique per club but can repeat across clubs', function () {
    $clubA = Club::factory()->create();
    $clubB = Club::factory()->create();
    ClubRole::factory()->for($clubA)->create(['name' => 'Member']);

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.clubs.show', $clubA))
        ->post(route('admin.clubs.roles.store', $clubA), ['name' => 'Member'])
        ->assertSessionHasErrors('name');

    $this->actingAs(User::factory()->owner()->create())
        ->post(route('admin.clubs.roles.store', $clubB), ['name' => 'Member'])
        ->assertSessionHasNoErrors();

    expect($clubB->roles()->where('name', 'Member')->exists())->toBeTrue();
});

test('a user without clubs.manage cannot manage club roles', function () {
    $club = Club::factory()->create();

    $this->actingAs(User::factory()->staff()->create())
        ->post(route('admin.clubs.roles.store', $club), ['name' => 'Secretary'])
        ->assertForbidden();
});
