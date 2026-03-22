<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $fillable = [
        'title',
        'description',
        'room_name',
        'guest_token',
        'status',
        'user_id',
    ];

    /**
     * Dono da sala (Host).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}