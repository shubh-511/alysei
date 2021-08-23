<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class RecipeSavedIngredient extends Model
{
    protected $PrimaryKey = 'recipe_saved_ingredient_id';
    
    public function ingredient()
    {
        return $this->belongsTo(RecipeIngredient::class, 'ingredient_id','recipe_ingredient_id');
    }

    
}
