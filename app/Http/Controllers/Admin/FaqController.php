<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaqRequest;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/faqs', [
            'faqs' => Faq::query()
                ->ordered()
                ->get(['id', 'category', 'question', 'answer', 'sort_order']),
        ]);
    }

    public function store(FaqRequest $request): RedirectResponse
    {
        Faq::create($request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'FAQ added.',
        ]);
    }

    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update($request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'FAQ updated.',
        ]);
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'FAQ deleted.',
        ]);
    }
}
