<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevokedToken extends Model
{
    protected $table = 'revoked_tokens';

    protected $fillable = ['jti', 'expires_at'];
    public $timestamps = true;
}
