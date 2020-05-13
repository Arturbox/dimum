<!doctype html>
<html lang="{{ config('app.locale') }}" dir="{{ __('voyager::generic.is_rtl') == 'true' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Դիմորդ 2020</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
    <!-- Bootstrap core CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <!-- Material Design Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.16.0/css/mdb.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{asset('css/dimord.css')}}">
    <!-- JQuery -->


</head>
<body class="voyager @if(isset($dataType) && isset($dataType->slug)){{ $dataType->slug }}@endif">
<div class="container side-body">

    <div class="card">
        <div class="card-header">
            <span>*** Խնդրում ենք ծանոթանալ կից ուղեցույցին և լրացնել ընդունելության հայտը</span>
            <div class="language-selector float-right d-inline-block">
                <div class="btn-group btn-group-sm" role="group" data-toggle="buttons">
                    @foreach(config('voyager.multilingual.locales') as $lang)
                        <label class="btn btn-primary{{ ($lang === config('voyager.multilingual.default')) ? " active" : "" }}">
                            <input type="radio" name="i18n_selector" id="{{$lang}}" autocomplete="off"{{ ($lang === config('voyager.multilingual.default')) ? ' checked="checked"' : '' }}> {{ strtoupper($lang) }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card-body">
            <a href="javascript:void(0)" class="text-dark py-4 px-2"><i class="fas fa-file-pdf"></i> Ուղեցույց </a>
        </div>

    </div>
    <h1 class="text-center">Դիմում հայտ</h1>
    <div class="card mb-3">
        <div class="card-body">
            <a href="javascript:void(0)" class="text-dark"><i class="far fa-file-word"></i> Դիմումի օրինակ – ներբեռնել </a>
        </div>

    </div>
    <form method="post" action="{{  route('voyager.'.$dataType->slug.'.store') }}" enctype="multipart/form-data" class="form-edit-add">
        @csrf

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @foreach($dataTypeRows->chunk(2) as $dataRows)
                <div class="form-row">
                    @foreach($dataRows as $row)

                        <!-- GET THE DISPLAY OPTIONS -->
                            @php
                                $display_options = $row->details->display ?? NULL;
                                if ($dataTypeContent->{$row->field.'_add'}) {
                                    $dataTypeContent->{$row->field} = $dataTypeContent->{$row->field.'_add'};
                                }
                            @endphp
                            @if (isset($row->details->legend) && isset($row->details->legend->text))
                                <legend class="text-{{ $row->details->legend->align ?? 'center' }}" style="background-color: {{ $row->details->legend->bgcolor ?? '#f0f0f0' }};padding: 5px;">{{ $row->details->legend->text }}</legend>
                            @endif
                            <div class="form-group @if($row->type == 'hidden') hidden @endif col-md-{{ $display_options->width ?? 12 }} {{ $errors->has($row->field) ? 'has-error' : '' }}" >
                                {{ $row->slugify }}
                                @include('voyager::multilingual.input-hidden-bread-edit-add')
                                @if($row->type == 'date')
                                    <input type="text" class="form-control datepicker1" name="{{ $row->field }}"
                                           value="@if(isset($dataTypeContent->{$row->field})){{ \Carbon\Carbon::parse(old($row->field, $dataTypeContent->{$row->field}))->format('Y-m-d') }}@else{{old($row->field)}}@endif">
                                @elseif($row->type == 'text')
                                    <input @if($row->required == 1) required @endif type="text" class="form-control" name="{{ $row->field }}"
                                           @if(isset($row->details->display->id)){{ "id=".$row->details->display->id }}@endif

                                           value="{{ old($row->field, $dataTypeContent->{$row->field} ?? $options->default ?? '') }}">
                                @elseif($row->type == 'file')
                                    <input @if($row->required == 1 && !isset($dataTypeContent->{$row->field})) required @endif type="file" name="{{ $row->field }}[]" @if(isset($row->details->display->id)){{ "id=".$row->details->display->id }}@endif>
                                @elseif($row->type == 'select_dropdown')
                                    <label for="@if(isset($display_options->id)){{$display_options->id}}@endif">{{ $row->getTranslatedAttribute('display_name') }}</label>
                                    @include('voyager::formfields.select_dropdown', ['options' => $row->details])
                                @elseif(isset($row->details->view))
                                    @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'action' => 'add'])
                                @elseif ($row->type == 'relationship')
                                    @php
                                        $dataParams = [];
                                        $filter = collect([]);
                                        $row->filter = null;
                                        $row->recursiveDataFilters($dataFilters);
                                        $filter = $row->filter;
                                         $dataParams = [
                                                'dataId'=>false,
                                                'dataParentId'=>false,
                                                'hasChild'=>false,
                                                'parentIds'=>false
                                            ];
                                        if($filter){
                                            $dataTypeFilter = $filter->getDataType()->first();
                                            $dataParams['dataId'] = $filter->id;
                                            $dataParams['dataParentId'] = $filter->parent_id;
                                            $dataParams['hasChild'] = $filter->children->count();
                                            if ($dataParentFilter = $filter->parentId()->first()){
                                                $dataTypeParentFilter = $dataParentFilter->getDataType()->first();
                                                $filterDataTypeAndContent = $dataTypeFilter->getDataTypeAndContent($dataTypeFilter->slug);
                                                $filterParentDataTypeAndContent = $dataTypeParentFilter->getDataTypeAndContent($dataTypeParentFilter->slug);
                                                $relationData = [];
                                                foreach ($filterDataTypeAndContent->DataTypeContent as $Id){
                                                    $relationData[$Id->id] = \App\Models\Voyager\DataFilter::getRelationQuery(
                                                    $filterParentDataTypeAndContent->DataType->slug,
                                                    (object)['tables'=>[$filterDataTypeAndContent->DataType->slug=>$Id->id]],
                                                    clone $filterParentDataTypeAndContent->DataQuery
                                                    )
                                                    ->get()->pluck('id')->toArray();
                                                }
                                                $dataParams['parentIds'] = $relationData;
                                            }
                                        }
                                    @endphp
                                    <label for="@if(isset($display_options->id)){{$display_options->id}}@endif">{{ $row->getTranslatedAttribute('display_name') }}</label>
                                    @include('voyager::formfields.relationship', ['options' => $row->details,'dataParams' => $dataParams])
                                @else
                                    {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                                @endif

                                @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                    {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                @endforeach
                                @if ($errors->has($row->field))
                                    @foreach ($errors->get($row->field) as $error)
                                        <span class="help-block">{{ $error }}</span>
                                    @endforeach
                                @endif
                                @if($row->type != 'radio_btn' && $row->type != 'relationship' && $row->type != 'select_dropdown')
                                <label for="@if(isset($display_options->id)){{$display_options->id}}@endif">{{ $row->getTranslatedAttribute('display_name') }}</label>
                                @endif

                                <div class="line"></div>
                            </div>
                    @endforeach
                </div>

                @endforeach


        <div class="panel-footer text-center py-3">
            @section('submit-buttons')
                <button type="submit" class="btn btn-primary w-25 save">{{ __('voyager::generic.submit') }} &nbsp; <i class="fab fa-telegram"></i></button>
            @stop
            @yield('submit-buttons')
        </div>

    </form>






    <iframe id="form_target" name="form_target" style="display:none"></iframe>
    <form id="my_form" action="{{ route('voyager.upload') }}" target="form_target" method="post"
          enctype="multipart/form-data" style="width:0;height:0;overflow:hidden">
        <input name="image" id="upload_file" type="file"
               onchange="$('#my_form').submit();this.value='';">
        <input type="hidden" name="type_slug" id="type_slug" value="{{ $dataType->slug }}">
        {{ csrf_field() }}
    </form>


</div>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<!-- Bootstrap tooltips -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.4/umd/popper.min.js"></script>
<!-- Bootstrap core JavaScript -->
<script type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.4.1/js/bootstrap.min.js"></script>
<!-- MDB core JavaScript -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdbootstrap/4.16.0/js/mdb.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.hy.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script>
    function checkValue(element) {
        // check if the input has any value (if we've typed into it)
        if ($(element).val())
            $(element).addClass('has-value');
        else
            $(element).removeClass('has-value');
    }

    $(document).ready(function () {
        $('.datepicker1').datepicker({
            language: 'hy',
            // autoclose: true,
            format: 'mm-dd-yyyy'
        });
        $('.toggleswitch').bootstrapToggle({
            on: 'Այո',
            off: 'Ոչ',
            size: 'normal',
            offstyle: 'danger'
        });
        $('body').find('input:file').removeAttr('multiple').addClass('custom-file-input');
        $('body').find('input:file').next('label').addClass('custom-file-label');
        $('body').find('input:file').next().next('div.line').remove()
        // Run on page load
        $('.form-control').each(function () {
            checkValue(this);
        })
        // Run on input exit
        $('.form-control').blur(function () {
            checkValue(this);
        });
        $("select").select2({
            // placeholder: "Pick states",
            theme: "material"
        });

        $(".select2-selection__arrow")
            .addClass("material-icons")
            .html("<i class='fas fa-angle-down'></i>");
    });
    $(document).on('click', '.upload-field', function () {
        var file = $(this).parent().parent().parent().find('.input-file');
        file.trigger('click');
    });
    $(document).on('change', '.custom-file-input', function (e) {
        var fileName = e.target.files[0].name;
        $(this).next('.custom-file-label').html(fileName);
        $(this).next().val($(this).val().replace(/C:\\fakepath\\/i, ''));
    });


    $(document).ready(function () {


        $('.relationship_select').not(':first').parent().removeClass('col-md-12').addClass('col-md-6');


        $('.relationship_select').change( function() {
            var group = $(this).val();
            var selected = $(this).find('option:selected').val();
            var dataId = $(this).attr("data-id");
            $(document).find('span.select2-container').removeClass('select2-container--default').addClass('select2-container--material select2-container--below')

            $(".relationship_select").each(function () {
                if ($(this).attr("data-parent") == dataId) {
                    if (group) {
                        $(this).select2({
                            matcher: function (term, text, option) {
                                var storedString = $(text.element).data("arr");
                                var dataValues = [];

                                if(storedString) {
                                    if(storedString.toString().includes(",")) {
                                        dataValues = storedString.split(',');
                                    } else {
                                        dataValues = [storedString.toString()];
                                    }
                                }

                                for (var i = 0; i < dataValues.length; i++) {
                                    if (selected == dataValues[i]) {
                                        return text;
                                    }
                                }

                                return false;
                            }
                        }).trigger('change');
                    }
                }
            });
        });
    });




</script>

{{--<script type="text/javascript" src="{{ voyager_asset('js/multilingual.js') }}"></script>--}}
{{--<script>--}}
{{--    $(document).ready(function (){--}}
{{--        $('.side-body').multilingual({"editing": true});--}}
{{--    });--}}
{{--</script>--}}


</body>
</html>