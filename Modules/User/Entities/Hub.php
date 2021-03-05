<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;
class Hub extends Model
{
    //protected $fillable = [];
    public function image()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
}
