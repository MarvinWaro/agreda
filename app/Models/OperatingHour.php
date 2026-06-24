<?php

namespace App\Models;

use Database\Factories\OperatingHourFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $court_id
 * @property int $day_of_week
 * @property string $open_time
 * @property string $close_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Court $court
 */
class OperatingHour extends Model
{
    /** @use HasFactory<OperatingHourFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'court_id',
        'day_of_week',
        'open_time',
        'close_time',
    ];

    /**
     * @return BelongsTo<Court, $this>
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
        ];
    }
}
