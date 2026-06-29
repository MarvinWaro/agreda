<?php

namespace App\Http\Controllers;

use App\Enums\ClubMemberStatus;
use App\Http\Requests\StoreClubMembershipRequest;
use App\Jobs\NotifyClubOfApplication;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ClubMembershipController extends Controller
{
    public function create(Club $club): Response
    {
        return Inertia::render('public/club-join', [
            'club' => [
                'name' => $club->name,
                'slug' => $club->slug,
                'description' => $club->description,
                'membership_fee' => $club->membership_fee,
            ],
        ]);
    }

    public function store(StoreClubMembershipRequest $request, Club $club): RedirectResponse
    {
        $data = $request->validated();

        $member = DB::transaction(function () use ($club, $data): ClubMember {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            return $club->members()->create([
                ...Arr::except($data, ['email', 'password', 'password_confirmation']),
                'user_id' => $user->id,
                'status' => ClubMemberStatus::Pending,
            ]);
        });

        // Queued so the applicant's submit isn't blocked on Facebook delivery.
        NotifyClubOfApplication::dispatch($member);

        return redirect()->route('club.join.done', $member)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Account created — log in anytime to check your application status.',
            ]);
    }

    public function done(ClubMember $member): Response
    {
        $member->load('club');

        return Inertia::render('public/club-join-confirmation', [
            'member' => [
                'full_name' => $member->full_name,
                'club' => $member->club->name,
                'status' => $member->status->value,
            ],
        ]);
    }
}
