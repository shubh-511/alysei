<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id','id');
    }
}
