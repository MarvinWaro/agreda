<?php

use App\Models\Setting;
use App\Models\Sport;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('non-admins cannot access the settings editor', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.settings.edit'))
        ->assertForbidden();
});

test('an admin can view the settings editor', function () {
    Setting::updateOrCreate(['key' => 'contact_phone'], ['value' => '0917', 'group' => 'contact']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.settings.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/settings')
            ->where('settings.contact_phone', '0917'));
});

test('an admin can update settings', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.settings.edit'))
        ->put(route('admin.settings.update'), [
            'contact_phone' => '0999 111 2222',
            'contact_email' => 'hi@agreda.test',
            'facebook_url' => 'https://facebook.com/agreda3',
            'address' => 'New address',
            'opening_hours' => 'Daily 9–9',
            'map_embed_url' => '',
        ])
        ->assertRedirect(route('admin.settings.edit'));

    expect(Setting::get('contact_phone'))->toBe('0999 111 2222')
        ->and(Setting::get('contact_email'))->toBe('hi@agreda.test');
});

test('settings update validates the email', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.settings.edit'))
        ->put(route('admin.settings.update'), ['contact_email' => 'not-an-email'])
        ->assertSessionHasErrors('contact_email');
});

test('a pasted Google Maps iframe is normalised to its embed url', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.settings.edit'))
        ->put(route('admin.settings.update'), [
            'map_embed_url' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12" width="600" height="450"></iframe>',
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHasNoErrors();

    expect(Setting::get('map_embed_url'))->toBe('https://www.google.com/maps/embed?pb=!1m18!1m12');
});

test('a non-embeddable map share link is rejected', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.settings.edit'))
        ->put(route('admin.settings.update'), [
            'map_embed_url' => 'https://maps.app.goo.gl/mxzT6B7bXePzodgm8',
        ])
        ->assertSessionHasErrors('map_embed_url');
});

test('non-admins cannot access the sports admin', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.sports.index'))
        ->assertForbidden();
});

test('an admin can update a sport rate and active flag', function () {
    $sport = Sport::factory()->create([
        'rate_offpeak' => 500,
        'rate_peak' => 800,
        'is_active' => true,
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.sports.index'))
        ->patch(route('admin.sports.update', $sport), [
            'rate_offpeak' => 650,
            'rate_peak' => 950,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.sports.index'));

    $sport->refresh();

    expect($sport->rate_offpeak)->toBe('650.00')
        ->and($sport->rate_peak)->toBe('950.00')
        ->and($sport->is_active)->toBeFalse();
});
