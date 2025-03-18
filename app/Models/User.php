<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\DefaultRoleEnum;
use App\Enums\UserStatusEnum;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatusEnum::class,
            'role' => DefaultRoleEnum::class,
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Yeni kullanıcı oluşturulduğunda statüsünü ACTIVE yap
        static::creating(function (User $user) {
            if (!isset($user->status)) {
                $user->status = UserStatusEnum::ACTIVE;
            }
        });
    }

    /**
     * Şifre mutator: Laravel'in varsayılan hash mekanizmasını kullanır
     */
    public function setPasswordAttribute($value)
    {
        // Şifre zaten hash'lenmiş mi kontrol et
        $this->attributes['password'] = (strlen($value) === 60 && preg_match('/^\$2y\$/', $value))
            ? $value
            : bcrypt($value);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasPermissionTo(string $permission): bool
    {
        return $this->roles()->whereJsonContains('permissions', $permission)->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function isSuperAdmin(): bool
    {
        if (auth()->check()) {
            return auth()->user()->role === DefaultRoleEnum::SUPER_ADMIN;
        }

        return false;
    }

    public function isAdmin(): bool
    {
        if (auth()->check()) {
            return auth()->user()->role === DefaultRoleEnum::SUPER_ADMIN || 
                   auth()->user()->role === DefaultRoleEnum::ADMIN || 
                   $this->hasRole('admin');
        }

        return false;
    }
}
