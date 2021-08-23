<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class Recipe extends Model
{
    protected $PrimaryKey = 'recipe_id';

    public function image()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }

    public function meal()
    {
        return $this->belongsTo(RecipeMeal::class, 'meal_id','recipe_meal_id');
    }

    public function course()
    {
        return $this->belongsTo(RecipeMeal::class, 'meal_id','recipe_meal_id');
    }

    public function region()
    {
        return $this->belongsTo(RecipeRegion::class, 'region_id','recipe_region_id');
    }
   

    
}
