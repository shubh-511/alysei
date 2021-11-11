<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Marketplace\Entities\MarketplaceBrandLabel;
use Modules\Marketplace\Entities\MarketplaceProductGallery;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\User\Entities\User;

class MarketplaceProduct extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'marketplace_product_id';
    protected $fillable = [];

    public function labels()
    {
        return $this->belongsTo(MarketplaceBrandLabel::class, 'brand_label_id','marketplace_brand_label_id');
    }

    public function product_gallery()
    {
        return $this->hasMany(MarketplaceProductGallery::class, 'marketplace_product_id','marketplace_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }
}

