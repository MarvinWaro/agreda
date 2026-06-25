<?php

use App\Models\User;
use App\Support\Rbac;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('a super admin can view roles and the permission catalog', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.roles.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/roles')
            ->has('roles')
            ->has('permissions'));
});

test('a user without roles.manage cannot manage roles', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->get(route('admin.roles.index'))
        ->assertForbidden();
});

test('a super admin can create a role with permissions', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.roles.index'))
        ->post(route('admin.roles.store'), [
            'name' => 'Receptionist',
            'permissions' => ['admin.access', 'bookings.view'],
        ])
        ->assertRedirect(route('admin.roles.index'));

    $role = Role::findByName('Receptionist');

    expect($role->hasPermissionTo('admin.access'))->toBeTrue()
        ->and($role->hasPermissionTo('bookings.manage'))->toBeFalse();
});

test('a super admin can update a role permission set', function () {
    $role = Role::findOrCreate('Receptionist', Rbac::GUARD);

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.roles.index'))
        ->put(route('admin.roles.update', $role), [
            'permissions' => ['admin.access'],
        ])
        ->assertRedirect(route('admin.roles.index'));

    expect($role->fresh()->hasPermissionTo('admin.access'))->toBeTrue();
});

test('the super admin role cannot have its permissions changed', function () {
    $admin = User::factory()->admin()->create();
    $role = Role::findByName(Rbac::SUPER_ADMIN);

    $this->actingAs($admin)
        ->from(route('admin.roles.index'))
        ->put(route('admin.roles.update', $role), ['permissions' => []]);

    expect($role->fresh()->permissions()->count())->toBe(count(Rbac::PERMISSIONS));
});

test('the super admin role cannot be deleted', function () {
    $admin = User::factory()->admin()->create();
    $role = Role::findByName(Rbac::SUPER_ADMIN);

    $this->actingAs($admin)
        ->from(route('admin.roles.index'))
        ->delete(route('admin.roles.destroy', $role));

    expect(Role::query()->where('name', Rbac::SUPER_ADMIN)->exists())->toBeTrue();
});

test('a super admin can delete a custom role', function () {
    $role = Role::findOrCreate('Temporary', Rbac::GUARD);

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.roles.index'))
        ->delete(route('admin.roles.destroy', $role));

    expect(Role::query()->where('name', 'Temporary')->exists())->toBeFalse();
});

test('creating a role validates the name and permissions', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.roles.index'))
        ->post(route('admin.roles.store'), [
            'name' => '',
            'permissions' => ['not.a.permission'],
        ])
        ->assertSessionHasErrors(['name', 'permissions.0']);
});
