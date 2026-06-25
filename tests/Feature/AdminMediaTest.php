<?php

use App\Models\CarouselSlide;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('staff without content.manage cannot manage the carousel', function () {
    $this->actingAs(User::factory()->staff()->create())
        ->get(route('admin.slides.index'))
        ->assertForbidden();
});

test('an owner can upload a carousel slide', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.slides.index'))
        ->post(route('admin.slides.store'), [
            'title' => 'Grand opening',
            'image' => UploadedFile::fake()->image('slide.jpg'),
            'sort_order' => 1,
            'is_visible' => 1,
        ])
        ->assertRedirect(route('admin.slides.index'));

    $slide = CarouselSlide::sole();

    expect($slide->image_path)->not->toBeNull()
        ->and($slide->is_visible)->toBeTrue();
    Storage::disk('public')->assertExists($slide->image_path);
});

test('updating a slide replaces the previous image', function () {
    Storage::fake('public');
    $old = UploadedFile::fake()->image('old.jpg')->store('slides', 'public');
    $slide = CarouselSlide::create([
        'title' => 'Old',
        'image_path' => $old,
        'sort_order' => 0,
        'is_visible' => true,
    ]);

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.slides.index'))
        ->put(route('admin.slides.update', $slide), [
            'title' => 'New title',
            'image' => UploadedFile::fake()->image('new.jpg'),
        ])
        ->assertRedirect(route('admin.slides.index'));

    $slide->refresh();

    expect($slide->title)->toBe('New title');
    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($slide->image_path);
});

test('deleting a slide removes its image file', function () {
    Storage::fake('public');
    $path = UploadedFile::fake()->image('x.jpg')->store('slides', 'public');
    $slide = CarouselSlide::create([
        'title' => 'X',
        'image_path' => $path,
        'is_visible' => true,
    ]);

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.slides.index'))
        ->delete(route('admin.slides.destroy', $slide));

    $this->assertDatabaseMissing('carousel_slides', ['id' => $slide->id]);
    Storage::disk('public')->assertMissing($path);
});

test('a slide image must be a valid image type', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.slides.index'))
        ->post(route('admin.slides.store'), [
            'title' => 'Bad upload',
            'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('image');
});

test('an owner can upload an event with an image', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.events.index'))
        ->post(route('admin.events.store'), [
            'title' => 'Weekend tournament',
            'image' => UploadedFile::fake()->image('event.png'),
            'event_date' => '2026-08-01',
            'is_featured' => 1,
        ])
        ->assertRedirect(route('admin.events.index'));

    $event = Event::sole();

    expect($event->is_featured)->toBeTrue();
    Storage::disk('public')->assertExists($event->image_path);
});
