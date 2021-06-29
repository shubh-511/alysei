<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class MarketplaceStore extends Model
{
    protected $primaryKey = 'marketplace_store_id';
    protected $fillable = [];

    public function logo_id()
    {
        return $this->belongsTo(Attachment::class, 'logo_id','id');
    }
}

