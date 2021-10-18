<?php

namespace Modules\Recipe\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use App\Attachment;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeSavedTool extends Model
{
    use SoftDeletes;
    protected $PrimaryKey = 'recipe_saved_tool_id';

    public function tool()
    {
        return $this->belongsTo(RecipeTool::class, 'tool_id','recipe_tool_id');
    }
   

    
}
