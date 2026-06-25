<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSlideRequest;
use App\Http\Requests\UpdateSlideRequest;
use App\Models\CarouselSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SlideController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/slides', [
            'slides' => CarouselSlide::query()
                ->ordered()
                ->get()
                ->map(fn (CarouselSlide $slide): array => [
                    'id' => $slide->id,
                    'title' => $slide->title,
                    'caption' => $slide->caption,
                    'link_url' => $slide->link_url,
                    'sort_order' => $slide->sort_order,
                    'is_visible' => $slide->is_visible,
                    'image_url' => $slide->imageUrl(),
                ])
                ->all(),
        ]);
    }

    public function store(StoreSlideRequest $request): RedirectResponse
    {
        $slide = new CarouselSlide($this->attributes($request));
        $slide->image_path = $this->upload($request->file('image'));
        $slide->save();

        return back()->with('toast', ['type' => 'success', 'message' => 'Slide added.']);
    }

    public function update(UpdateSlideRequest $request, CarouselSlide $slide): RedirectResponse
    {
        $slide->fill($this->attributes($request));

        $image = $request->file('image');
        if ($image instanceof UploadedFile) {
            $this->deleteImage($slide->image_path);
            $slide->image_path = $this->upload($image);
        }

        $slide->save();

        return back()->with('toast', ['type' => 'success', 'message' => 'Slide updated.']);
    }

    public function destroy(CarouselSlide $slide): RedirectResponse
    {
        $this->deleteImage($slide->image_path);
        $slide->delete();

        return back()->with('toast', ['type' => 'success', 'message' => 'Slide deleted.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(StoreSlideRequest|UpdateSlideRequest $request): array
    {
        return [
            'title' => $request->validated('title'),
            'caption' => $request->validated('caption'),
            'link_url' => $request->validated('link_url'),
            'sort_order' => $request->validated('sort_order') ?? 0,
            'is_visible' => $request->boolean('is_visible'),
        ];
    }

    private function upload(mixed $image): ?string
    {
        if (! $image instanceof UploadedFile) {
            return null;
        }

        return $image->store('slides', 'public') ?: null;
    }

    private function deleteImage(?string $path): void
    {
        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }
    }
}
