<?php

use App\Models\Faq;
use App\Models\Sport;
use Inertia\Testing\AssertableInertia as Assert;

test('public page renders', function (string $routeName, string $component) {
    $this->get(route($routeName))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    'home' => ['home', 'public/home'],
    'about' => ['about', 'public/about'],
    'facilities' => ['facilities', 'public/facilities'],
    'pricing' => ['pricing', 'public/pricing'],
    'faqs' => ['faqs', 'public/faqs'],
    'contact' => ['contact', 'public/contact'],
]);

test('pricing lists active sports with their rates', function () {
    Sport::factory()->create(['name' => 'Basketball', 'rate_offpeak' => 500, 'rate_peak' => 800]);
    Sport::factory()->inactive()->create();

    $this->get(route('pricing'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/pricing')
            ->has('sports', 1)
            ->where('sports.0.name', 'Basketball'));
});

test('faqs are returned in sort order', function () {
    Faq::create(['question' => 'Second', 'answer' => 'A', 'sort_order' => 2]);
    Faq::create(['question' => 'First', 'answer' => 'B', 'sort_order' => 1]);

    $this->get(route('faqs'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('faqs', 2)
            ->where('faqs.0.question', 'First'));
});

test('the contact form acknowledges a valid message', function () {
    $this->from(route('contact'))
        ->post(route('contact.send'), ['name' => 'Ana', 'message' => 'Hello there'])
        ->assertRedirect(route('contact'))
        ->assertSessionHasNoErrors();
});

test('the contact form requires a name and message', function () {
    $this->from(route('contact'))
        ->post(route('contact.send'), [])
        ->assertSessionHasErrors(['name', 'message']);
});
