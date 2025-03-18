<?php

namespace App\Models;

use App\Enums\DefaultScrapingRuleTypeEnum;
use App\Enums\ScrapingStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteScrapingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'field',
        'selector',
        'type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => DefaultScrapingRuleTypeEnum::class,
            'status' => ScrapingStatusEnum::class
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
