<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class PreferenceMapDiet extends Model
{
    public function diet()
    {
        return $this->belongsTo(RecipeDiet::class, 'diet_id','recipe_diet_id');
    }

    
}
