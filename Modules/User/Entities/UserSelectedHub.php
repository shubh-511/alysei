<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Image;
class UserSelectedHub extends Model
{

	protected $table = "user_selected_hubs";

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

}
