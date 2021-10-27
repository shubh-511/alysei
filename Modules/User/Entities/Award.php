<?php

namespace Modules\User\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class Award extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'award_id';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }

    public function medal()
    {
        return $this->belongsTo(Medal::class, 'medal_id','medal_id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

}
