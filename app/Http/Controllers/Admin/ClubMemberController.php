<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClubMemberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateClubMemberRequest;
use App\Jobs\NotifyApplicantOfDecision;
use App\Models\Club;
use App\Models\ClubMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClubMemberController extends Controller
{
    public function index(Request $request): Response
    {
        $clubId = $request->integer('club_id') ?: null;
        $status = $request->string('status')->toString() ?: null;

        $members = ClubMember::query()
            ->with(['club:id,name,membership_fee', 'clubRole:id,name'])
            ->when($clubId, fn ($query, $value) => $query->where('club_id', $value))
            ->when($status, fn ($query, $value) => $query->where('status', $value))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ClubMember $member): array => [
                'id' => $member->id,
                'club' => $member->club->name,
                'club_id' => $member->club_id,
                'club_role_id' => $member->club_role_id,
                'club_role' => $member->clubRole?->name,
                'full_name' => $member->full_name,
                'age' => $member->age,
                'sex' => $member->sex->label(),
                'occupation' => $member->occupation,
                'address' => $member->address,
                'phone' => $member->phone,
                'notes' => $member->notes,
                'status' => $member->status->value,
                'created_at' => $member->created_at?->format('M j, Y'),
                'fee_paid' => $member->fee_paid_at !== null,
            ]);

        return Inertia::render('admin/club-members', [
            'members' => $members,
            'clubs' => Club::query()
                ->with(['roles' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(['id', 'name', 'membership_fee'])
                ->map(fn (Club $club): array => [
                    'id' => $club->id,
                    'name' => $club->name,
                    'membership_fee' => $club->membership_fee,
                    'roles' => $club->roles
                        ->map(fn ($role): array => ['id' => $role->id, 'name' => $role->name])
                        ->all(),
                ]),
            'statuses' => array_map(
                fn (ClubMemberStatus $case): array => ['value' => $case->value, 'label' => $case->label()],
                ClubMemberStatus::cases(),
            ),
            'filters' => [
                'club_id' => $clubId,
                'status' => $status,
            ],
        ]);
    }

    public function approve(ClubMember $member): RedirectResponse
    {
        return $this->transition($member, ClubMemberStatus::Approved, 'approved');
    }

    public function decline(ClubMember $member): RedirectResponse
    {
        return $this->transition($member, ClubMemberStatus::Declined, 'declined');
    }

    public function update(UpdateClubMemberRequest $request, ClubMember $member): RedirectResponse
    {
        $changes = [];

        if ($request->has('club_role_id')) {
            $changes['club_role_id'] = $request->validated('club_role_id');
        }

        if ($request->has('fee_paid')) {
            $changes['fee_paid_at'] = $request->boolean('fee_paid')
                ? ($member->fee_paid_at ?? now())
                : null;
        }

        $member->update($changes);

        return back()->with('toast', ['type' => 'success', 'message' => 'Member updated.']);
    }

    private function transition(ClubMember $member, ClubMemberStatus $to, string $verb): RedirectResponse
    {
        if ($member->status !== ClubMemberStatus::Pending) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => "Application #{$member->id} is no longer pending.",
            ]);
        }

        $member->update(['status' => $to, 'reviewed_at' => now()]);

        if ($to === ClubMemberStatus::Approved && $member->club_role_id === null) {
            $defaultRole = $member->club->roles()->where('is_default', true)->first();

            if ($defaultRole !== null) {
                $member->update(['club_role_id' => $defaultRole->id]);
            }
        }

        // Notify the applicant back (queued; stubbed Facebook delivery).
        NotifyApplicantOfDecision::dispatch($member);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Application #{$member->id} {$verb}.",
        ]);
    }
}
