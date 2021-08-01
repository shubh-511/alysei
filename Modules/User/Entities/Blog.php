<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Image;

class Blog extends Model
{
    protected $primaryKey = 'blog_id';
    /*public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }*/

}
