<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Image;
class FeaturedListing extends Model
{
	protected $fillable = ["title","description","image_id","featured_listing_type_id","created_at","updated_at","user_id"];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function image()
    {
        return $this->belongsTo(Image::class, 'image_id','id');
    }
}
