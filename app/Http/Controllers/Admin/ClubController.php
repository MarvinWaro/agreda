<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClubRequest;
use App\Http\Requests\UpdateClubRequest;
use App\Models\Club;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ClubController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/clubs', [
            'clubs' => Club::query()
                ->with('sport:id,name')
                ->withCount(['members', 'roles'])
                ->orderBy('name')
                ->get()
                ->map(fn (Club $club): array => [
                    'id' => $club->id,
                    'name' => $club->name,
                    'slug' => $club->slug,
                    'sport' => $club->sport?->name,
                    'description' => $club->description,
                    'membership_fee' => $club->membership_fee,
                    'is_active' => $club->is_active,
                    'members_count' => $club->members_count,
                    'roles_count' => $club->roles_count,
                ])
                ->all(),
            'sports' => Sport::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Club $club): Response
    {
        $club->loadCount('members');
        $club->load(['roles' => fn ($query) => $query->orderBy('name')]);

        return Inertia::render('admin/club-detail', [
            'club' => [
                'id' => $club->id,
                'name' => $club->name,
                'slug' => $club->slug,
                'is_active' => $club->is_active,
                'members_count' => $club->members_count,
            ],
            'roles' => $club->roles
                ->map(fn ($role): array => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'is_default' => $role->is_default,
                ])
                ->all(),
        ]);
    }

    public function store(StoreClubRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Club::query()->create([
            ...$data,
            'slug' => $this->uniqueSlug($data['name']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Club created.']);
    }

    public function update(UpdateClubRequest $request, Club $club): RedirectResponse
    {
        $club->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Club updated.']);
    }

    public function destroy(Club $club): RedirectResponse
    {
        $club->delete();

        return back()->with('toast', ['type' => 'success', 'message' => 'Club deleted.']);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Club::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
