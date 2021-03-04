<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Image;

class UserTempHub extends Model
{

	protected $table = "temp_hubs";

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

}
