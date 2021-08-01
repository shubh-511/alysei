<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class Trip extends Model
{
    protected $primaryKey = 'trip_id';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

    public function intensity()
    {
        return $this->belongsTo(Intensity::class, 'intensity','intensity_id');
    }

    public function adventure()
    {
        return $this->belongsTo(AdventureType::class, 'adventure_type','adventure_type_id');
    }

    
}