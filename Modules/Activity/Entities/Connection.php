<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

class Connection extends Model
{
	protected $primaryKey = 'connection_id';
    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }
}
