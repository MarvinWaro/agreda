<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Inertia\Inertia;
use Inertia\Response;

class ClubController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/clubs', [
            'clubs' => Club::query()
                ->active()
                ->with('sport:id,name')
                ->orderBy('name')
                ->get()
                ->map(fn (Club $club): array => [
                    'id' => $club->id,
                    'name' => $club->name,
                    'slug' => $club->slug,
                    'sport' => $club->sport?->name,
                    'description' => $club->description,
                    'membership_fee' => $club->membership_fee,
                ])
                ->all(),
        ]);
    }
}
