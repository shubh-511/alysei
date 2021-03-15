<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Modules\User\Entities\Role;
use App\Attachment;

class User extends Authenticatable
{
	use Notifiable, HasApiTokens;
	
	protected $primaryKey = 'user_id';

    protected $fillable = ['email','password','first_name','last_name','name','otp','role_id',"timezone","locale","account_enabled"];

    public function roles(){
        return $this->belongsTo(Role::class, 'role_id','role_id')->select(array('role_id', 'name', 'slug', 'display_name'));
    }

    public function profile_image()
    {
        return $this->belongsTo(Attachment::class, 'avatar_id','id');
    }
    public function cover_image()
    {
        return $this->belongsTo(Attachment::class, 'cover_id','id');
    }
}
