<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/users', [
            'users' => User::query()
                ->with('roles:id,name')
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->all(),
                    'created_at' => $user->created_at?->format('M j, Y'),
                ])
                ->all(),
            'roles' => Role::query()->orderBy('name')->pluck('name')->all(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->syncRoles([$data['role']]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "User {$user->name} created.",
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "User {$user->name} updated.",
        ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->is($user)) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => "You can't delete your own account.",
            ]);
        }

        $user->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'User deleted.',
        ]);
    }
}
