<?php

namespace App\Models;

use App\Enums\QueueStatusEnum;
use App\Enums\ScrapingStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'token',
        'name',
        'pagination_item_selector',
        'product_item_selector',
        'include_subcategories',
        'subcategory_selector',
        'status',
        'queue_status',
        'last_scraping_date',
    ];

    protected $hidden = [
        'token',
    ];

    protected function casts(): array
    {
        return [
            'status' => ScrapingStatusEnum::class,
            'queue_status' => QueueStatusEnum::class,
            'last_scraping_date' => 'datetime'
        ];
    }

    public function scopeOwner(Builder $query): Builder
    {
        if (auth()->user()->isSuperAdmin()) {
            return $query;
        }

        return $query->where('user_id', auth()->id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function siteCategories(): HasMany
    {
        return $this->hasMany(SiteCategory::class);
    }

    public function siteScrapingRules(): HasMany
    {
        return $this->hasMany(SiteScrapingRule::class);
    }
}
