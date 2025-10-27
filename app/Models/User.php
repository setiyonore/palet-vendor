<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_changed_at' => 'datetime',
    ];
    protected static function booted()
    {
        static::updating(function ($user) {
            if ($user->isDirty('password')) {
                $user->password_changed_at = now();
            }
        });
    }

    public function setPasswordAttribute($value): void
    {
        if (blank($value)) {
            // abaikan assignment kosong; biarkan password lama
            return;
        }

        $this->attributes['password'] = Hash::make($value);
        $this->attributes['password_changed_at'] = now();
    }
}
