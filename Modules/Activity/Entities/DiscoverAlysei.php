<?php

namespace Modules\Activity\Entities;
use Modules\User\Entities\User;
use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class DiscoverAlysei extends Model
{
	protected $table = 'discover_alysei';
	protected $primaryKey = 'discover_alysei';

    public function attachment()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

}
