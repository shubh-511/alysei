<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use Modules\User\Entities\Cousin;
use App\Attachment;

class Recipe extends Model
{
    protected $primaryKey = 'recipe_id';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','user_id');
    }

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
        return $this->belongsTo(RecipeCourse::class, 'course_id','recipe_course_id');
    }

    public function cousin()
    {
        return $this->belongsTo(Cousin::class, 'cousin_id','cousin_id');
    }

    public function region()
    {
        return $this->belongsTo(RecipeRegion::class, 'region_id','recipe_region_id');
    }

    public function ingredients()
    {
        return $this->hasMany(RecipeSavedIngredient::class, 'recipe_id','recipe_id');
    }

    public function diet()
    {
        return $this->belongsTo(RecipeDiet::class, 'diet_id','recipe_diet_id');
    }

    public function intolerance()
    {
        return $this->belongsTo(RecipeFoodIntolerance::class, 'intolerance_id','recipe_food_intolerance_id');
    }

    public function cookingskill()
    {
        return $this->belongsTo(RecipeCookingSkill::class, 'cooking_skill_id','recipe_cooking_skill_id');
    }
   

    
}
