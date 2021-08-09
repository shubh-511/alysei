<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;

class RecipeSavedTool extends Model
{
    protected $PrimaryKey = 'recipe_saved_tool_id';

    public function image_id()
    {
        return $this->belongsTo(Attachment::class, 'image_id','id');
    }
   

    
}
