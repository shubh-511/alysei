<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Image;

class Trip extends Model
{
    protected $primaryKey = 'trip_id';
    /*public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }*/

    
}
