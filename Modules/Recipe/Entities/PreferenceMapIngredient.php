<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class PreferenceMapIngredient extends Model
{
    public function ingredient()
    {
        return $this->belongsTo(RecipeIngredient::class, 'ingredient_id','recipe_ingredient_id');
    }

    
}
