@if(isset($options->model) && isset($options->type))

    @if(class_exists($options->model))

        @php $relationshipField = $row->field; @endphp

        @if($options->type == 'belongsTo')

            @if(isset($view) && ($view == 'browse' || $view == 'read'))

                @php
                    $relationshipData = (isset($data)) ? $data : $dataTypeContent;
                    $model = app($options->model);
                    $query = $model::where($options->key,$relationshipData->{$options->column})->first();
                    if ($query)
                        $query = $query->translate(App()->getLocale());
                @endphp

                @if(isset($query))
                    <p>{{ $query->{$options->label} }}</p>
                @else
                    <p>{{ __('voyager::generic.no_results') }}</p>
                @endif

            @else

                <select class="form-control select2
                        @if(isset($dataParams))
                            {{ ( $dataParams['dataParentId']>0 || $dataParams['hasChild']>0 ?'relationship_select':'') }}
                        @endif" name="{{ $options->column }}"

                        @if(isset($dataParams))
                            data-id="{{  $dataParams['dataId'] }}"
                            data-parent="{{ $dataParams['dataParentId'] }}"
                        @endif
                >
                    @php
                        $model = app($options->model);
                        $query = $model::all();
                        if ($query)
                            $query = $query->translate(App()->getLocale());
                    @endphp

                    @if(!$row->required)
                        <option value="">{{__('voyager::generic.none')}}</option>
                    @endif

                    @foreach($query as $relationshipData)
                        <option @if(isset($dataParams) && $dataParams['parentIds'])  data-arr="{{ implode(',',$dataParams['parentIds'][$relationshipData->{$options->key}]) }}" @endif value="{{ $relationshipData->{$options->key} }}" @if($dataTypeContent->{$options->column} == $relationshipData->{$options->key}){{ 'selected="selected"' }}@endif>{{ $relationshipData->{$options->label} }}</option>
                    @endforeach
                </select>

            @endif

        @elseif($options->type == 'hasOne')

            @php

                $relationshipData = (isset($data)) ? $data : $dataTypeContent;

                $model = app($options->model);
                $query = $model::where($options->column, '=', $relationshipData->id)->first();
                if ($query)
                        $query = $query->translate(App()->getLocale());
            @endphp

            @if(isset($query))
                <p>{{ $query->{$options->label} }}</p>
            @else
                <p>{{ __('voyager::generic.no_results') }}</p>
            @endif

        @elseif($options->type == 'hasMany')

            @if(isset($view) && ($view == 'browse' || $view == 'read'))

                @php
                    $relationshipData = (isset($data)) ? $data : $dataTypeContent;
                    $model = app($options->model);

            		$selected_values = $model::where($options->column, '=', $relationshipData->id)->get()->map(function ($item, $key) use ($options) {
                        $item = $item->translate(App()->getLocale());
            			return $item->{$options->label};
            		})->all();
                @endphp

                @if($view == 'browse')
                    @php
                        $string_values = implode(", ", $selected_values);
                        if(mb_strlen($string_values) > 25){ $string_values = mb_substr($string_values, 0, 25) . '...'; }
                    @endphp
                    @if(empty($selected_values))
                        <p>{{ __('voyager::generic.no_results') }}</p>
                    @else
                        <p>{{ $string_values }}</p>
                    @endif
                @else
                    @if(empty($selected_values))
                        <p>{{ __('voyager::generic.no_results') }}</p>
                    @else
                        <ul>
                            @foreach($selected_values as $selected_value)
                                <li>{{ $selected_value }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif

            @else

                @php
                    $model = app($options->model);
                    $query = $model::where($options->column, '=', $dataTypeContent->id)->get();
                    if ($query)
                        $query = $query->translate(App()->getLocale());
                @endphp

                @if(isset($query))
                    <ul>
                        @foreach($query as $query_res)
                            <li>{{ $query_res->{$options->label} }}</li>
                        @endforeach
                    </ul>

                @else
                    <p>{{ __('voyager::generic.no_results') }}</p>
                @endif

            @endif

        @elseif($options->type == 'belongsToMany')

            @if(isset($view) && ($view == 'browse' || $view == 'read'))

                @php
                    $relationshipData = (isset($data)) ? $data : $dataTypeContent;

                    $selected_values = isset($relationshipData) ? $relationshipData->belongsToMany($options->model, $options->pivot_table)->get()->map(function ($item, $key) use ($options) {
            			$item = $item->translate(App()->getLocale());
            			return $item->{$options->label};
            		})->all() : array();
                @endphp

                @if($view == 'browse')
                    @php
                        $string_values = implode(", ", $selected_values);
                        if(mb_strlen($string_values) > 25){ $string_values = mb_substr($string_values, 0, 25) . '...'; }
                    @endphp
                    @if(empty($selected_values))
                        <p>{{ __('voyager::generic.no_results') }}</p>
                    @else
                        <p>{{ $string_values }}</p>
                    @endif
                @else
                    @if(empty($selected_values))
                        <p>{{ __('voyager::generic.no_results') }}</p>
                    @else
                        <ul>
                            @foreach($selected_values as $selected_value)
                                <li>{{ $selected_value }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif

            @else
                <select
                    class=" @if(isset($dataParams)) {{ ( $dataParams['dataParentId']>0 || $dataParams['hasChild']>0 ?'relationship_select':'') }} @endif form-control @if(isset($options->taggable) && $options->taggable == 'on') select2-taggable @else select2 @endif"
                    name="{{ $relationshipField }}[]" multiple
                    @if(isset($dataParams))
                    data-id="{{ $dataParams['dataId'] }}"
                    data-parent="{{ $dataParams['dataParentId'] }}"
                    @endif
                    @if(isset($options->taggable) && $options->taggable == 'on')
                        data-route="{{ route('voyager.'.\Illuminate\Support\Str::slug($options->table).'.store') }}"
                        data-label="{{$options->label}}"
                        data-error-message="{{__('voyager::bread.error_tagging')}}"
                    @endif
                >

                        @php
                            $selected_values = isset($dataTypeContent) ? $dataTypeContent->belongsToMany($options->model, $options->pivot_table)->get()->map(function ($item, $key) use ($options) {
                                return $item->{$options->key};
                            })->all() : array();
                            $relationshipOptions = app($options->model)->all();
                            if ($relationshipOptions)
                                $relationshipOptions = $relationshipOptions->translate(App()->getLocale());
                        @endphp

                        @if(!$row->required)
                            <option value="" disabled>{{__('voyager::generic.none')}}</option>
                        @endif

                        @foreach($relationshipOptions as $relationshipOption)
                            <option value="{{ $relationshipOption->{$options->key} }}" @if(in_array($relationshipOption->{$options->key}, $selected_values)){{ 'selected="selected"' }}@endif>{{ $relationshipOption->{$options->label} }}</option>
                        @endforeach

                </select>

            @endif

        @endif

    @else

        cannot make relationship because {{ $options->model }} does not exist.

    @endif

@endif
