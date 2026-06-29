<?php

namespace App\Models;

use App\Enums\ClubMemberStatus;
use App\Enums\Sex;
use Database\Factories\ClubMemberFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $club_id
 * @property int|null $club_role_id
 * @property int|null $user_id
 * @property string $full_name
 * @property int $age
 * @property Sex $sex
 * @property string $occupation
 * @property string $address
 * @property string $phone
 * @property string|null $notes
 * @property ClubMemberStatus $status
 * @property Carbon|null $reviewed_at
 * @property Carbon|null $fee_paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Club $club
 * @property-read ClubRole|null $clubRole
 * @property-read User|null $user
 * @property-read Collection<int, ClubMemberNotification> $notifications
 */
class ClubMember extends Model
{
    /** @use HasFactory<ClubMemberFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'club_id',
        'club_role_id',
        'user_id',
        'full_name',
        'age',
        'sex',
        'occupation',
        'address',
        'phone',
        'notes',
        'status',
        'reviewed_at',
        'fee_paid_at',
    ];

    /**
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * @return BelongsTo<ClubRole, $this>
     */
    public function clubRole(): BelongsTo
    {
        return $this->belongsTo(ClubRole::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ClubMemberNotification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(ClubMemberNotification::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sex' => Sex::class,
            'status' => ClubMemberStatus::class,
            'reviewed_at' => 'datetime',
            'fee_paid_at' => 'datetime',
        ];
    }
}
