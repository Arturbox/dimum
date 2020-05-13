<?php

namespace App\Models\Voyager;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use TCG\Voyager\Facades\Voyager;
use \App\Models\Voyager\DataFilter;
use TCG\Voyager\Models\DataTableRows;

class DataType extends \TCG\Voyager\Models\DataType
{
    /**
     * Smart table GET FULL DATATYPE DATACONTENT DATAQUERY
     *
     * @param $slug | table slug
     */
    public function getDataTypeAndContent($slug){
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        $dataQuery = strlen($dataType->model_name) != 0 ? app($dataType->model_name) : DB::table($dataType->name);
        $dataTypeContent = $dataQuery->get();

        if (is_bread_translatable(app($dataType->model_name))) {
            $dataTypeContent->load('translations');
        }
        return (object)[
            'DataType'=>$dataType?$dataType:false,
            'DataTypeContent'=>$dataTypeContent?$dataTypeContent:collect([]),
            'DataQuery'=>$dataQuery
        ];
    }


}
