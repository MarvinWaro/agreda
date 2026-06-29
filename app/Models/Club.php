<?php

namespace App\Models;

use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $sport_id
 * @property string|null $description
 * @property string|null $membership_fee
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sport|null $sport
 * @property-read Collection<int, ClubRole> $roles
 * @property-read Collection<int, ClubMember> $members
 */
class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'sport_id',
        'description',
        'membership_fee',
        'is_active',
    ];

    /**
     * @return BelongsTo<Sport, $this>
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * @return HasMany<ClubRole, $this>
     */
    public function roles(): HasMany
    {
        return $this->hasMany(ClubRole::class);
    }

    /**
     * @return HasMany<ClubMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ClubMember::class);
    }

    /**
     * @param  Builder<Club>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'membership_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
