<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
class FeaturedListing extends Model
{
	protected $fillable = ["title","description","image_id","listing_url","featured_listing_type_id","created_at","updated_at","user_id"];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function image()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
}
