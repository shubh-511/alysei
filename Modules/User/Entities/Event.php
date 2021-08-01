<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Image;

class Event extends Model
{
    protected $primaryKey = 'event_id';
    /*public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }*/

}
