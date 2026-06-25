<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/pages', [
            'pages' => Page::query()
                ->orderBy('title')
                ->get(['id', 'slug', 'title', 'body']),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $page->update($request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => "{$page->title} saved.",
        ]);
    }
}
