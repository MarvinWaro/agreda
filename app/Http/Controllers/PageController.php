<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function about(): Response
    {
        $page = Page::query()->where('slug', 'about')->first();

        return Inertia::render('public/about', [
            'page' => [
                'title' => $page !== null ? $page->title : 'About AGREDA PHASE III',
                'body' => $page !== null ? ($page->body ?? '') : '',
            ],
        ]);
    }
}
