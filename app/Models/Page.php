<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $body
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Page extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'slug',
        'title',
        'body',
    ];

    /**
     * Use the slug for implicit route-model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
