<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $table = 'refresh_tokens';

    protected $fillable = ['user_id', 'token', 'user_agent', 'ip', 'revoked', 'expires_at'];

    public $timestamps = true;

    protected $casts = [
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
