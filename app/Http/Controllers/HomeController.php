<?php

namespace App\Http\Controllers;

use App\Models\CarouselSlide;
use App\Models\Event;
use App\Models\Sport;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/home', [
            'slides' => CarouselSlide::query()
                ->visible()
                ->ordered()
                ->get(['id', 'title', 'caption', 'image_path', 'link_url']),
            'events' => Event::query()
                ->featured()
                ->orderBy('event_date')
                ->take(6)
                ->get()
                ->map(fn (Event $event): array => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'date' => $event->event_date?->format('M j, Y'),
                    'image_path' => $event->image_path,
                ])
                ->all(),
            'sports' => Sport::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }
}
