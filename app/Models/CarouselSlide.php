<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string|null $image_path
 * @property string|null $caption
 * @property string|null $link_url
 * @property int $sort_order
 * @property bool $is_visible
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CarouselSlide extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'image_path',
        'caption',
        'link_url',
        'sort_order',
        'is_visible',
    ];

    /**
     * Public URL for the slide image, or null when none is set.
     */
    public function imageUrl(): ?string
    {
        return $this->image_path === null
            ? null
            : Storage::disk('public')->url($this->image_path);
    }

    /**
     * @param  Builder<CarouselSlide>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /**
     * @param  Builder<CarouselSlide>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }
}
