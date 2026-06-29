<?php

namespace App\Http\Controllers;

use App\Models\ClubMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MembershipController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->can('admin.access')) {
            return redirect()->route('admin.dashboard');
        }

        $memberships = ClubMember::query()
            ->where('user_id', $request->user()->id)
            ->with(['club:id,name,membership_fee', 'clubRole:id,name'])
            ->orderByDesc('created_at')
            ->get();

        if ($memberships->isEmpty()) {
            return redirect()->route('home');
        }

        return Inertia::render('public/membership', [
            'memberships' => $memberships->map(fn (ClubMember $member): array => [
                'id' => $member->id,
                'club' => $member->club->name,
                'role' => $member->clubRole?->name,
                'status' => $member->status->value,
                'membership_fee' => $member->club->membership_fee,
                'fee_paid' => $member->fee_paid_at !== null,
                'applied_at' => $member->created_at?->format('M j, Y'),
            ]),
        ]);
    }
}
