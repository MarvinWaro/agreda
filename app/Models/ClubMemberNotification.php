<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $club_member_id
 * @property NotificationChannel $channel
 * @property NotificationStatus $status
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ClubMember $clubMember
 */
class ClubMemberNotification extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'club_member_id',
        'channel',
        'status',
        'sent_at',
    ];

    /**
     * @return BelongsTo<ClubMember, $this>
     */
    public function clubMember(): BelongsTo
    {
        return $this->belongsTo(ClubMember::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'status' => NotificationStatus::class,
            'sent_at' => 'datetime',
        ];
    }
}
