<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/faqs', [
            'faqs' => Faq::query()
                ->ordered()
                ->get(['id', 'category', 'question', 'answer']),
        ]);
    }
}
