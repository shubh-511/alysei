<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $primaryKey = 'notification_id';

    public function from_user()
    {
        return $this->belongsTo(User::class, 'from','user_id');
    }

    public function to_user()
    {
        return $this->belongsTo(User::class, 'to','user_id');
    }
}
