<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use TCG\Voyager\Traits\Translatable;

class Application extends Model
{
    use SoftDeletes;
    use Translatable;


    public $translatable = ['name_app', 'surname_app', 'lastname_app', 'birthplace_app', 'citizenshi_app', 'address_app', 'home_app', 'phone_app', 'mail_app', 'name_parent', 'surname_parent', 'lastname_parent', 'birthplace_parent', 'citizenshi_parent', 'address_parent', 'work_parent', 'phone_parent', 'mail_parent', 'text', 'second_name_app', 'station', 'massage'];

    public function parentId()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

}
