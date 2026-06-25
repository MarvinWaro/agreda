<?php

use App\Models\User;
use App\Support\Rbac;
use Inertia\Testing\AssertableInertia as Assert;

test('a super admin can view the users list', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.users.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users')
            ->has('users')
            ->has('roles'));
});

test('a user without users.manage cannot access user management', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('a role-less user is blocked from user management', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('a super admin can create a user with a role', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.users.index'))
        ->post(route('admin.users.store'), [
            'name' => 'New Staffer',
            'email' => 'staffer@agreda.test',
            'password' => 'password123',
            'role' => Rbac::STAFF,
        ])
        ->assertRedirect(route('admin.users.index'));

    $user = User::query()->where('email', 'staffer@agreda.test')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasRole(Rbac::STAFF))->toBeTrue();
});

test('a super admin can change a user role', function () {
    $target = User::factory()->staff()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.users.index'))
        ->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => Rbac::OWNER,
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($target->refresh()->hasRole(Rbac::OWNER))->toBeTrue()
        ->and($target->hasRole(Rbac::STAFF))->toBeFalse();
});

test('a super admin can delete another user', function () {
    $target = User::factory()->staff()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.users.index'))
        ->delete(route('admin.users.destroy', $target));

    $this->assertModelMissing($target);
});

test('a user cannot delete their own account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('admin.users.index'))
        ->delete(route('admin.users.destroy', $admin));

    $this->assertModelExists($admin);
});

test('creating a user requires name, email, password and role', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.users.index'))
        ->post(route('admin.users.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});
