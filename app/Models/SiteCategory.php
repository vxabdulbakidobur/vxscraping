<?php

namespace App\Models;

use App\Enums\ScrapingStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'url',
        'include_subcategories',
        'subcategory_selector',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ScrapingStatusEnum::class
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
