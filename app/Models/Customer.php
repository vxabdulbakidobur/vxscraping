<?php

namespace App\Models;

use App\Enums\CustomerStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'status',
        'package_id',
        'expire_date',
    ];

    protected function casts()
    {
        return [
            'status' => CustomerStatusEnum::class,
            'expire_date' => 'datetime',
        ];
    }

    public function scopeOwner(Builder $query): Builder
    {
        if (auth()->user()->isSuperAdmin()) {
            return $query;
        }

        return $query->where('user_id', auth()->id());
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class)
            ->withPivot(['start_date', 'expire_date', 'is_active', 'price_paid', 'notes'])
            ->withTimestamps();
    }

    public function activePackages(): BelongsToMany
    {
        return $this->packages()->wherePivot('is_active', true);
    }

    public function hasActivePackage(): bool
    {
        return $this->package_id !== null && $this->expire_date !== null && $this->expire_date->isFuture();
    }
}
