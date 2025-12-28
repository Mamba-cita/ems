<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $fillable = ['user_id', 'token', 'expires_at'];

    public $timestamps = true;

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
