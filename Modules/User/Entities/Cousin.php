<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class Cousin extends Model
{
    protected $primaryKey = 'cousin_id';

    /*public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }*/

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

}
