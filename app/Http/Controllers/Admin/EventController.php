<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/events', [
            'events' => Event::query()
                ->orderByDesc('event_date')
                ->get()
                ->map(fn (Event $event): array => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'event_date' => $event->event_date?->toDateString(),
                    'is_featured' => $event->is_featured,
                    'image_url' => $event->imageUrl(),
                ])
                ->all(),
        ]);
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $event = new Event($this->attributes($request));
        $event->image_path = $this->upload($request->file('image'));
        $event->save();

        return back()->with('toast', ['type' => 'success', 'message' => 'Event added.']);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $event->fill($this->attributes($request));

        $image = $request->file('image');
        if ($image instanceof UploadedFile) {
            $this->deleteImage($event->image_path);
            $event->image_path = $this->upload($image);
        }

        $event->save();

        return back()->with('toast', ['type' => 'success', 'message' => 'Event updated.']);
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->deleteImage($event->image_path);
        $event->delete();

        return back()->with('toast', ['type' => 'success', 'message' => 'Event deleted.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(StoreEventRequest|UpdateEventRequest $request): array
    {
        return [
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'event_date' => $request->validated('event_date'),
            'is_featured' => $request->boolean('is_featured'),
        ];
    }

    private function upload(mixed $image): ?string
    {
        if (! $image instanceof UploadedFile) {
            return null;
        }

        return $image->store('events', 'public') ?: null;
    }

    private function deleteImage(?string $path): void
    {
        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }
    }
}
