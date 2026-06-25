<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/roles', [
            'roles' => Role::query()
                ->with('permissions:id,name')
                ->orderBy('name')
                ->get()
                ->map(fn (Role $role): array => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->all(),
                    'users_count' => $role->users()->count(),
                    'protected' => $role->name === Rbac::SUPER_ADMIN,
                ])
                ->all(),
            'permissions' => Rbac::PERMISSIONS,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::findOrCreate($data['name'], Rbac::GUARD);
        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Role {$role->name} created.",
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === Rbac::SUPER_ADMIN) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => 'The Super Admin role always has every permission.',
            ]);
        }

        $role->syncPermissions($request->validated()['permissions'] ?? []);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Role {$role->name} updated.",
        ]);
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === Rbac::SUPER_ADMIN) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => 'The Super Admin role cannot be deleted.',
            ]);
        }

        $role->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Role deleted.',
        ]);
    }
}
