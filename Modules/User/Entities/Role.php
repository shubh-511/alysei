<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name','type','slug'];
}
