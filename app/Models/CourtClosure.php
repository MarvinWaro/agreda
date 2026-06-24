<?php

namespace App\Models;

use Database\Factories\CourtClosureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $court_id
 * @property Carbon $date
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Court $court
 */
class CourtClosure extends Model
{
    /** @use HasFactory<CourtClosureFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'court_id',
        'date',
        'reason',
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
            'date' => 'date',
        ];
    }
}
