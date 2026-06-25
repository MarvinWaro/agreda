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
                ->get()
                ->map(fn (CarouselSlide $slide): array => [
                    'id' => $slide->id,
                    'title' => $slide->title,
                    'caption' => $slide->caption,
                    'image_path' => $slide->imageUrl(),
                    'link_url' => $slide->link_url,
                ])
                ->all(),
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
                    'image_path' => $event->imageUrl(),
                ])
                ->all(),
            'sports' => Sport::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }
}
