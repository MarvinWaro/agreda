<?php

use App\Models\Faq;
use App\Models\Page;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('staff without content.manage cannot manage content', function () {
    $this->actingAs(User::factory()->staff()->create())
        ->get(route('admin.faqs.index'))
        ->assertForbidden();
});

test('an owner can view the FAQ manager', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->get(route('admin.faqs.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/faqs')
            ->has('faqs'));
});

test('an owner can create a FAQ', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.faqs.index'))
        ->post(route('admin.faqs.store'), [
            'question' => 'Is parking free?',
            'answer' => 'Yes, free parking is available.',
            'category' => 'General',
            'sort_order' => 1,
        ])
        ->assertRedirect(route('admin.faqs.index'));

    $this->assertDatabaseHas('faqs', ['question' => 'Is parking free?']);
});

test('an owner can update a FAQ', function () {
    $faq = Faq::create(['question' => 'Q', 'answer' => 'A', 'sort_order' => 0]);

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.faqs.index'))
        ->put(route('admin.faqs.update', $faq), [
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
            'sort_order' => 2,
        ])
        ->assertRedirect(route('admin.faqs.index'));

    expect($faq->fresh()->question)->toBe('Updated question?');
});

test('an owner can delete a FAQ', function () {
    $faq = Faq::create(['question' => 'Q', 'answer' => 'A']);

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.faqs.index'))
        ->delete(route('admin.faqs.destroy', $faq));

    $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
});

test('faq validation requires a question and answer', function () {
    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.faqs.index'))
        ->post(route('admin.faqs.store'), [])
        ->assertSessionHasErrors(['question', 'answer']);
});

test('an owner can edit a page', function () {
    $page = Page::create(['slug' => 'about', 'title' => 'About', 'body' => 'Old body']);

    $this->actingAs(User::factory()->owner()->create())
        ->from(route('admin.pages.index'))
        ->put(route('admin.pages.update', $page), [
            'title' => 'About AGREDA',
            'body' => 'New body.',
        ])
        ->assertRedirect(route('admin.pages.index'));

    expect($page->fresh())
        ->title->toBe('About AGREDA')
        ->body->toBe('New body.');
});
