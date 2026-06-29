<?php

namespace App\Models;

use Database\Factories\ClubRoleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $club_id
 * @property string $name
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Club $club
 * @property-read Collection<int, ClubMember> $members
 */
class ClubRole extends Model
{
    /** @use HasFactory<ClubRoleFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'club_id',
        'name',
        'is_default',
    ];

    /**
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * @return HasMany<ClubMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ClubMember::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }
}
