<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClubRoleRequest;
use App\Http\Requests\UpdateClubRoleRequest;
use App\Models\Club;
use App\Models\ClubRole;
use Illuminate\Http\RedirectResponse;

class ClubRoleController extends Controller
{
    public function store(StoreClubRoleRequest $request, Club $club): RedirectResponse
    {
        $data = $request->validated();

        if ($request->boolean('is_default')) {
            $club->roles()->update(['is_default' => false]);
        }

        $club->roles()->create([
            'name' => $data['name'],
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Role added.']);
    }

    public function update(UpdateClubRoleRequest $request, Club $club, ClubRole $role): RedirectResponse
    {
        if ($request->boolean('is_default')) {
            $club->roles()->where('id', '!=', $role->id)->update(['is_default' => false]);
        }

        $role->update([
            'name' => $request->validated('name'),
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Role updated.']);
    }

    public function destroy(Club $club, ClubRole $role): RedirectResponse
    {
        if ($role->is_default) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => "Cannot delete {$role->name} — it is the default role assigned to new members.",
            ]);
        }

        $role->delete();

        return back()->with('toast', ['type' => 'success', 'message' => 'Role deleted.']);
    }
}
