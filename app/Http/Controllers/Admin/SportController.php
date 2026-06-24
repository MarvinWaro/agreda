<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSportRequest;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/sports', [
            'sports' => Sport::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'rate_offpeak', 'rate_peak', 'is_active']),
        ]);
    }

    public function update(UpdateSportRequest $request, Sport $sport): RedirectResponse
    {
        $sport->update($request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$sport->name} updated.",
        ]);
    }
}
