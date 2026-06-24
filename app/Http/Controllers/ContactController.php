<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('public/contact', [
            'contact' => [
                'phone' => Setting::get('contact_phone'),
                'email' => Setting::get('contact_email'),
                'facebook_url' => Setting::get('facebook_url'),
                'map_embed_url' => Setting::get('map_embed_url'),
                'address' => Setting::get('address'),
            ],
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // v1: messages are not persisted — log and acknowledge.
        Log::info('Contact message', $validated);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Thanks! We'll get back to you soon.",
        ]);
    }
}
