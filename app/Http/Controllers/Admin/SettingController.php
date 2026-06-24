<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    /**
     * Editable settings and the group each belongs to.
     */
    private const GROUPS = [
        'contact_phone' => 'contact',
        'contact_email' => 'contact',
        'facebook_url' => 'contact',
        'map_embed_url' => 'contact',
        'address' => 'contact',
        'opening_hours' => 'general',
    ];

    public function edit(): Response
    {
        return Inertia::render('admin/settings', [
            'settings' => collect(array_keys(self::GROUPS))
                ->mapWithKeys(fn (string $key): array => [$key => Setting::get($key)])
                ->all(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated() as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => self::GROUPS[$key] ?? null],
            );
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Settings saved.',
        ]);
    }
}
