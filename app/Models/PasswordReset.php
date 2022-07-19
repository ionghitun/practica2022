<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 *
 */
class PasswordReset extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
