<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int $court_id
 * @property int $sport_id
 * @property string $guest_name
 * @property string $guest_phone
 * @property Carbon $booking_date
 * @property string $start_time
 * @property string $end_time
 * @property BookingStatus $status
 * @property string|null $total_price
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Court $court
 * @property-read Sport $sport
 * @property-read User|null $user
 * @property-read Collection<int, BookingNotification> $notifications
 */
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'court_id',
        'sport_id',
        'guest_name',
        'guest_phone',
        'booking_date',
        'start_time',
        'end_time',
        'status',
        'total_price',
        'notes',
    ];

    /**
     * @return BelongsTo<Court, $this>
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * @return BelongsTo<Sport, $this>
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<BookingNotification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(BookingNotification::class);
    }

    /**
     * Bookings that currently occupy their slot (pending or confirmed).
     *
     * @param  Builder<Booking>  $query
     */
    public function scopeOccupying(Builder $query): void
    {
        $query->whereIn('status', BookingStatus::occupyingValues());
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'status' => BookingStatus::class,
            'total_price' => 'decimal:2',
        ];
    }
}
