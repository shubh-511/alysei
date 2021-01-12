<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['email','password','first_name','last_name','name','role_id'];
}