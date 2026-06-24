<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/pricing', [
            'sports' => Sport::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'rate_offpeak', 'rate_peak']),
        ]);
    }
}
