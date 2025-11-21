<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
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

    /**
     * Logika Gerbang Akses Panel
     * Menggunakan Permission Shield untuk menentukan akses.
     */
    public function canAccessPanel(Panel $panel): bool
    {

        // 1. Super Admin boleh segalanya
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // 2. IZINKAN login jika user punya akses ke Admin ATAU Armada.
        // Kita return true disini agar user bisa lolos login dulu.
        // Nanti Middleware 'RedirectToCorrectPanel' yang akan memindahkan
        // user ke panel yang tepat jika dia salah alamat.

        return $this->can('access_panel_admin') || $this->can('access_panel_armada');
    }
}
