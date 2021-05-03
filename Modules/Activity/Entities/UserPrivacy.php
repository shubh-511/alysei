<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class UserPrivacy extends Model
{
	protected $primaryKey = 'privacy_id';
	protected $table = 'user_privacies';
    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }
}
