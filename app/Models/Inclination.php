<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use TCG\Voyager\Traits\Translatable;

class Inclination extends Model
{
    use SoftDeletes;
    use Translatable;


    public $translatable = ["name",];

    public function parentId()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

}
