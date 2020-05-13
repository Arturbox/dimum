<?php

namespace App\Models\Voyager;


use TCG\Voyager\Facades\Voyager;

class DataRow extends \TCG\Voyager\Models\DataRow
{

    public function getRelationDataType(){
        return Voyager::model('DataType')->where('name', $this->details->table)->first();
    }

    public function recursiveDataFilters($groupData){
        return $groupData->filter(function ($value){
            if ($value->data_type_id != $this->getRelationDataType()->id)
                return $this->recursiveDataFilters($value->children()->get());
            $this->filter = $value;
            return $value;
        });
    }

    public function getDataRecordsField($record_id){
        return Voyager::model('DataRecord')
            ->where('data_row_id',$this->id)
            ->where('field',$this->field)
            ->where('record_id',$record_id)
            ->first();
    }

}
