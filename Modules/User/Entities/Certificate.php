<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class Certificate extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    
}
