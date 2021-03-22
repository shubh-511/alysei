<?php

namespace Modules\Activity\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use Modules\Activity\Entities\ActivityActionType;
use Modules\Activity\Entities\ActivityAttachment;
use Modules\Activity\Entities\ActivityAttachmentLink;

class ActivityAction extends Model
{
	protected $primaryKey = 'activity_action_id';
    protected $fillable = [];

    public function attachments()
    {
        return $this->hasMany(ActivityAttachment::class, 'action_id','activity_action_id');
    }

    public function subject_id()
    {
        return $this->belongsTo(User::class, 'subject_id','user_id');
    }
}
