<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class MapHubCity extends Model
{
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id','id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id','id');
    }

    public function hub()
    {
        return $this->belongsTo(Hub::class, 'hub_id','id');
    }
}
