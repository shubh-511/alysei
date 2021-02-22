<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class FeaturedListing extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function image()
    {
        return $this->belongsTo(Image::class, 'img_id','id');
    }
}
