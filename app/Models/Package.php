<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'site_count',
        'url_count',
        'scan_frequency',
        'processor_count',
        'memory',
        'is_active',
        'price',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'site_count' => 'integer',
        'url_count' => 'integer',
        'processor_count' => 'integer',
        'price' => 'decimal:2',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)
            ->withPivot(['start_date', 'expire_date', 'is_active', 'price_paid', 'notes'])
            ->withTimestamps();
    }

    public function activeCustomers(): BelongsToMany
    {
        return $this->customers()->wherePivot('is_active', true);
    }

    public function currentCustomers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
