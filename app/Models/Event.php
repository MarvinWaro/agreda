<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $image_path
 * @property Carbon|null $event_date
 * @property bool $is_featured
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Event extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'event_date',
        'is_featured',
    ];

    /**
     * Public URL for the event image, or null when none is set.
     */
    public function imageUrl(): ?string
    {
        return $this->image_path === null
            ? null
            : Storage::disk('public')->url($this->image_path);
    }

    /**
     * @param  Builder<Event>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_featured' => 'boolean',
        ];
    }
}
