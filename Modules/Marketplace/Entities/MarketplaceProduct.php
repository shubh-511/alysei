<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Marketplace\Entities\MarketplaceBrandLabel;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceProduct extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'marketplace_product_id';
    protected $fillable = [];

    public function labels()
    {
        return $this->belongsTo(MarketplaceBrandLabel::class, 'brand_label_id','marketplace_brand_label_id');
    }
}

