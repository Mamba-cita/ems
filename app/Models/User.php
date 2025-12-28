<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'phone', 'username', 'email', 'password_hash', 'role', 'balance', 'profile_pic', 'bio', 'is_verified', 'youtube_channel_id', 'is_online'
    ];

    protected $hidden = ['password_hash'];

    // Helper to set password using bcrypt
    public function setPassword(string $password): void
    {
        $this->password_hash = password_hash($password, PASSWORD_BCRYPT);
    }

    public function checkPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }
}
