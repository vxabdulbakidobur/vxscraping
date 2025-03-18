<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb';
    protected string $collection;

    public function setSiteProfileCollection($siteId): Product
    {
        $this->collection = 'site_profile_' . $siteId;

        return $this;
    }
}
