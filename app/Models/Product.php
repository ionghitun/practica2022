<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 *
 */
class Product extends Model
{
    use HasFactory;

    const ACTIVE = 1;
    const INACTIVE = 0;

    /**
     * @var string[]
     */
    protected $appends = [
        'image_url'
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at', 'image'
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'category_id',
        'description',
        'quantity',
        'price',
        'status'
    ];

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return Storage::url($this->image);  //$product->image_url
        }

        return null;
    }
}
