<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Sport;
use Inertia\Inertia;
use Inertia\Response;

class FacilityController extends Controller
{
    public function index(): Response
    {
        $page = Page::query()->where('slug', 'facilities')->first();

        return Inertia::render('public/facilities', [
            'page' => [
                'title' => $page !== null ? $page->title : 'One court, four sports',
                'body' => $page !== null ? ($page->body ?? '') : '',
            ],
            'sports' => Sport::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'icon']),
            'amenities' => ['Parking', 'Restrooms', 'Lighting', 'Seating', 'Drinking water'],
        ]);
    }
}
