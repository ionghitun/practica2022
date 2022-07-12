<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 *
 */
class Category extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $visible = [
        'id', 'name', 'parent_id'
    ];

    /**
     * @return void
     */
    protected static function booted(): void
    {
        /** @var Category $category */
        static::deleting(function ($category) {
            if ($category->childs) {
                foreach ($category->childs as $child) {
                    $child->delete();
                }
            }
        });
    }

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function childs(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
