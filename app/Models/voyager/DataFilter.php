<?php

namespace App\Models\Voyager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Traits\Translatable;
use Illuminate\Support\Facades\Request;

class DataFilter extends Model
{
    use Translatable;

    protected $table = 'data_filters';

    protected $translatable = ['display_name'];

    protected $guarded = [];

    public $timestamps = false;

    public function rowBefore()
    {
        $previous = self::where('data_type_id', '=', $this->data_type_id)->where('order', '=', ($this->order - 1))->first();
        if (isset($previous->id)) {
            return $previous->section;
        }

        return '__first__';
    }

    public function relationshipField()
    {
        return @$this->details->column;
    }

    /**
     * Check if this field is the current filter.
     *
     * @return bool True if this is the current filter, false otherwise
     */
    public function isCurrentSortField($orderBy)
    {
        return $orderBy == $this->section;
    }

    public function lastFilter()
    {
        if ($result = $this->hasMany(Voyager::modelClass('DataFilter'), 'parent_id')->orderBy('order', 'DESC')->first())
            return $result->order;
        return false;
    }

    public function children()
    {
        return $this->hasMany(Voyager::modelClass('DataFilter'), 'parent_id')
            ->with('children');
    }

    public function parentId()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function getDataType(){
        return $this->belongsTo(Voyager::modelClass('DataType'), 'data_type_id');
    }

    /**
     * Build the URL to sort data type by this field.
     *
     * @return string Built URL
     */
    public function sortByUrl($orderBy, $sortOrder)
    {
        $params = [];
        $isDesc = $sortOrder != 'asc';
        if ($this->isCurrentSortField($orderBy) && $isDesc) {
            $params['sort_order'] = 'asc';
        } else {
            $params['sort_order'] = 'desc';
        }
        $params['order_by'] = $this->section;

        return url()->current().'?'.http_build_query($params);
    }

    public function setDetailsAttribute($value)
    {
        $this->attributes['details'] = json_encode($value);
    }

    public function getDetailsAttribute($value)
    {
        return json_decode(!empty($value) ? $value : '{}');
    }

    public static function getRelationData($slug,$relationSlugs,$dataTypeContent)
    {
        $dataType = Voyager::model('DataType')->where('slug',$slug)->first();
        foreach ($relationSlugs->tables as $k => $value){
            $dataTypeRelation = Voyager::model('DataType')->where('slug',$k)->first();

            $field = $dataType->rows->where('type','relationship')->where('details.table',$k)->first();

            if (!$field) continue;

            if ($field->details->type == "belongsToMany"){
                foreach ($dataTypeContent as $key => &$data){
                    if ($data->belongsToMany($field->details->model,$field->details->pivot_table)->first()){
                        $relationKey = $data->belongsToMany($field->details->model,$field->details->pivot_table)->first()->pivot->getRelatedKey();
                        if (!$data->belongsToMany($field->details->model,$field->details->pivot_table)->where($relationKey,'=',$value)->get()->count()) {
                            unset($dataTypeContent[$key]);
                        }
                    } else {
                        unset($dataTypeContent[$key]);
                    }
                }
            }
            elseif ($field->details->type == "belongsTo"){
                foreach ($dataTypeContent as $key => &$data){
                    if ($data->{$field->details->column} != $value){
                        unset($dataTypeContent[$key]);
                    }
                }
            }
            elseif ($field->details->type == "hasOne"){
                foreach ($dataTypeContent as $key => &$data){
                    if (!$data->hasOne($field->details->model,$field->details->column)->where($field->getKeyName(),$value)->get()->count()){
                        unset($dataTypeContent[$key]);
                    }
                }
            }
            elseif ($field->details->type == "hasMany"){
                foreach ($dataTypeContent as $key => &$data){
                    if (!$data->hasMany($field->details->model,$field->details->column)->where($field->getKeyName(),$value)->get()->count()){
                        unset($dataTypeContent[$key]);
                    }
                }
            }
        }
        return $dataTypeContent;
    }

    public static function getRelationQuery($slug,$relationSlugs,$query)
    {
        $dataType = Voyager::model('DataType')->where('slug',$slug)->first();
        foreach ($relationSlugs->tables as $k => $value){
            $dataTypeRelation = Voyager::model('DataType')->where('slug',$k)->first();

            $field = $dataType->rows->where('type','relationship')->where('details.table',$k)->first();
            if (!$field) continue;

            if ($field->details->type == "belongsToMany"){
                $pivotBuilder = app($dataType->model_name)->belongsToMany($field->details->model,$field->details->pivot_table);
                $query = $query
                    ->leftjoin($pivotBuilder->getTable(),$pivotBuilder->getQualifiedForeignPivotKeyName(), '=', $pivotBuilder->getQualifiedParentKeyName())
                    ->leftjoin($field->details->table,"{$field->details->table}."."{$pivotBuilder->getRelatedKeyName()}"  , '=',$pivotBuilder->getQualifiedRelatedPivotKeyName())
                    ->when($value, function ($query, $value) use ($field,$pivotBuilder) {
                        if (is_array($value))
                            return $query->whereIn("{$field->details->table}."."{$pivotBuilder->getRelatedKeyName()}",$value);
                        else
                            return $query->where("{$field->details->table}."."{$pivotBuilder->getRelatedKeyName()}",$value);
                    })
                    ->select($dataType->name.'.*');
            }
            elseif ($field->details->type == "belongsTo"){
                $query = $query
                    ->when($value, function ($query, $value) use ($field) {
                        if (is_array($value))
                            return $query->whereIn($field->details->column,$value);
                        else
                            return $query->where($field->details->column,$value);
                    });
            }
            elseif ($field->details->type == "hasOne"){
                $relationBuilder = app($dataType->model_name)->hasOne($field->details->model,$field->details->column);
                $query = $query
                ->leftjoin($relationBuilder->getRelated()->getTable(),$relationBuilder->getQualifiedForeignKeyName(), '=', "{$relationBuilder->getParent()->getTable()}."."{$relationBuilder->getLocalKeyName()}" )
                ->when($value, function ($query, $value) use ($relationBuilder) {
                    if (is_array($value))
                        return $query->whereIn($relationBuilder->getQualifiedForeignKeyName(),$value);
                    else
                        return $query->where($relationBuilder->getQualifiedForeignKeyName(),$value);
                })
                ->select($dataType->name.'.*');

            }
            elseif ($field->details->type == "hasMany"){
                $relationBuilder = app($dataType->model_name)->hasOne($field->details->model,$field->details->column);
                $query = $query
                    ->leftJoin($relationBuilder->getRelated()->getTable(),$relationBuilder->getQualifiedForeignKeyName(),'=',"{$relationBuilder->getParent()->getTable()}."."{$relationBuilder->getLocalKeyName()}")
                    ->when($value, function ($query, $value) use ($relationBuilder) {
                        if (is_array($value))
                            return $query->whereIn($relationBuilder->getRelated()->getTable().'.id',$value);
                        else
                            return $query->where($relationBuilder->getRelated()->getTable().'.id',$value);
                    })
                    ->select($dataType->name.'.*');
            }
        }
        return $query;
    }

    public static function filterRedirectDataMultiple($slug,$dataContent){
        $parentSlug = Request::segment(count(Request::segments()));
        if (!$parentFilters = Session::get($parentSlug))
            return $dataContent;

        $currentDataType = Voyager::model('DataType')->where('slug', '=',$slug)->first();
        $parentDataTypeAndContent =  Voyager::model('DataType')->getDataTypeAndContent(key($parentFilters->tables));
        if ($parentDataTypeAndContent->DataType->redirect_filter_fields && is_array($parentDataTypeAndContent->DataType->redirect_filter_fields)
            && count($parentDataTypeAndContent->DataType->redirect_filter_fields)
        ){
            //filter equal fields in parent and bindParent tables
            $filterFields = $parentDataTypeAndContent->DataType->getFiltersIds($parentFilters->tables[key($parentFilters->tables)],$currentDataType);
        }else{
            return $dataContent;
        }

        foreach ($filterFields as $k=>$v){
            $dataContent = $dataContent->where($k,$v);
        }
        return $dataContent;
    }

    public static function filterRedirectData($slug,$fields){
        $parentSlug = Request::segment(count(Request::segments()));
        if (!$parentFilters = Session::get($parentSlug))
            return true;

        $currentDataType = Voyager::model('DataType')->where('slug', '=',$slug)->first();
        $parentDataTypeAndContent =  Voyager::model('DataType')->getDataTypeAndContent(key($parentFilters->tables));
        if ($parentDataTypeAndContent->DataType->redirect_filter_fields && is_array($parentDataTypeAndContent->DataType->redirect_filter_fields)
        && count($parentDataTypeAndContent->DataType->redirect_filter_fields)
        ){
            //filter equal fields in parent and bindParent tables
            $filterFields = $parentDataTypeAndContent->DataType->getFiltersIds($parentFilters->tables[key($parentFilters->tables)],$currentDataType);
        }else{
            return true;
        }
        $true = [];

        foreach ($filterFields as $k=>$v){
            if (array_key_exists($k,$fields) && $fields[$k] == $v)
                $true[] = true;
        }

        if (count($true) == count($filterFields))
            return true;
        return false;
    }
}
