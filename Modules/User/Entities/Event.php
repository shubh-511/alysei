<?php

namespace Modules\User\Entities;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Attachment;
use Cviebrock\EloquentSluggable\Sluggable;

class Event extends Model
{
    use SoftDeletes;
    use Sluggable;
    protected $primaryKey = 'event_id';

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'event_name'
            ]
        ];
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

}
