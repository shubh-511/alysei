<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Marketplace\Entities\MarketplaceStoreGallery;

class MarketplaceStore extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'marketplace_store_id';
    protected $fillable = [];

    public function logo_id()
    {
        return $this->belongsTo(Attachment::class, 'logo_id','id');
    }

    public function store_gallery()
    {
        return $this->hasMany(MarketplaceStoreGallery::class, 'marketplace_store_id','marketplace_store_id');
    }
}

