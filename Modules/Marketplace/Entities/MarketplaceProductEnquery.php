<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Modules\User\Entities\User;

class MarketplaceProductEnquery extends Model
{
    protected $primaryKey = 'marketplace_product_enquery_id';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }
}

