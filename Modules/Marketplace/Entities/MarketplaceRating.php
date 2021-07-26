<?php

namespace Modules\Marketplace\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class MarketplaceRating extends Model
{
    protected $primaryKey = 'marketplace_review_ratings';
    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }
}

