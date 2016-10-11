@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Request integration
                <span class="pull-right">
                    <a href="{{ route('integrations-lists') }}" class="btn btn-info">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back
                    </a>
                </span>
            </h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
        <div class="col-md-offset-1 col-md-10">
            <div class="panel panel-default">
                <div class="panel-body">
                    {!! Form::open(['class' => 'form-horizontal', 'method' => 'POST', 'route' => 'integration-do-submit']) !!}
                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        {!! Form::label('name', 'Name', ['class' => 'col-md-3 control-label'])!!}
                        <div class="col-md-9">
                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                            <span class="help-block">
                                Input the name of your integration job.
                            </span>
                            @if ($errors->has('name'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 col-md-offset-3">
                            <fieldset>
                                <legend>
                                    <a role="button" data-toggle="collapse" href="#dataChooser"
                                       aria-expanded="true" aria-controls="dataChooser">
                                        1. Choose data to integrate
                                    </a>
                                    <span class="pull-right">
                                        <a role="button" data-toggle="collapse" href="#dataChooser"
                                           aria-expanded="true" aria-controls="dataChooser">
                                            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                                        </a>
                                    </span>
                                </legend>
                                <div id="dataChooser" class="collapse in">
                                    <div class="form-group{{ $errors->has('selections') ? ' has-error' : '' }}">
                                        {!! Form::label('selections[]', 'Selections', ['class' => 'col-md-3 control-label'])!!}
                                        <div class="col-md-9">
                                            {!! Form::select('selections[]', [], null, ['class' => 'form-control selections-select', 'multiple' => true]) !!}
                                            <span class="help-block">
                                                Choose one or more sample selections.
                                            </span>
                                            @if ($errors->has('selections'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('selections') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group{{ $errors->has('mapped_selections') ? ' has-error' : '' }}">
                                        {!! Form::label('mapped_selections[]', 'Mapped Selections', ['class' => 'col-md-3 control-label'])!!}
                                        <div class="col-md-9">
                                            {!! Form::select('mapped_selections[]', [], null, ['class' => 'form-control mapped-selections-select', 'multiple' => true]) !!}
                                            <span class="help-block">
                                                Choose one or more sample selections which have been previously mapped.
                                            </span>
                                            @if ($errors->has('mapped_selections'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('mapped_selections') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 col-md-offset-3">
                            <fieldset>
                                <legend>
                                    <a role="button" data-toggle="collapse" href="#integrationMethodOptions"
                                       aria-expanded="true" aria-controls="integrationMethodOptions">
                                        2. Chose integration method
                                    </a>
                                    <span class="pull-right">
                                        <a role="button" data-toggle="collapse" href="#integrationMethodOptions"
                                           aria-expanded="true" aria-controls="integrationMethodOptions">
                                            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                                        </a>
                                    </span>
                                </legend>
                                <div id="integrationMethodOptions" class="collapse in">
                                    <div class="form-group{{ $errors->has('method') ? ' has-error' : '' }}">
                                        {!! Form::label('method', 'Method', ['class' => 'col-md-3 control-label'])!!}
                                        <div class="col-md-9">
                                            {!! Form::select('method', $methods, null, ['class' => 'form-control']) !!}
                                            <span class="help-block">
                                                Choose an integration method
                                            </span>
                                            @if ($errors->has('method'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('method') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group{{ $errors->has('digits') ? ' has-error' : '' }}">
                                        {!! Form::label('digits', 'Decimal Digits', ['class' => 'col-md-3 control-label'])!!}
                                        <div class="col-md-9">
                                            {!! Form::number('digits', 12, ['class' => 'form-control']) !!}
                                            <span class="help-block">
                                                The number of decimal digits written to the output file
                                            </span>
                                            @if ($errors->has('digits'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('digits') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group{{ $errors->has('na_strings') ? ' has-error' : '' }}">
                                        {!! Form::label('na_strings', 'Missing Values', ['class' => 'col-md-3 control-label'])!!}
                                        <div class="col-md-9">
                                            {!! Form::text('na_strings', 'NULL,NA', ['class' => 'form-control']) !!}
                                            <span class="help-block">
                                                A comma separated list of strings that will be interpreted as
                                                missing data.
                                            </span>
                                            @if ($errors->has('na_strings'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('na_strings') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-9 col-md-offset-3">
                            <fieldset>
                                <legend>
                                    <a role="button" data-toggle="collapse" href="#identifiersMapping"
                                       aria-expanded="true" aria-controls="identifiersMapping">
                                        3. Post-integration Identifiers Mapping
                                    </a>
                                    <span class="pull-right">
                                        <a role="button" data-toggle="collapse" href="#identifiersMapping"
                                           aria-expanded="true" aria-controls="identifiersMapping">
                                            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                                        </a>
                                    </span>
                                </legend>
                                <div id="identifiersMapping" class="collapse in">
                                    <div class="form-group{{ $errors->has('enable_post_mapping') ? ' has-error' : '' }}">
                                        <div class="col-md-offset-3 col-md-9">
                                            <div class="checkbox">
                                                <label>
                                                    {!! Form::checkbox('enable_post_mapping', 1, null, ['class' => 'mapping-enabler']) !!}
                                                    Map identifiers after the data have been integrated?
                                                </label>
                                            </div>
                                            @if ($errors->has('enable_post_mapping'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('enable_post_mapping') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group{{ $errors->has('platform') ? ' has-error' : '' }}">
                                        {!! Form::label('platform', 'Platform', ['class' => 'col-md-3 control-label'])!!}
                                        <div class="col-md-9">
                                            {!! Form::select('platform', [], null, ['class' => 'form-control platform-select disabler-check-select enabler-check-select', 'disabled' => true]) !!}
                                            <span class="help-block">
                                                Choose the type of platform.
                                            </span>
                                            @if ($errors->has('platform'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('platform') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group{{ $errors->has('mapping') ? ' has-error' : '' }}">
                                        {!! Form::label('mapping', 'Map to', ['class' => 'col-md-3 control-label']) !!}
                                        <div class="col-md-9">
                                            {!! Form::select('mapping', [], null, ['class' => 'form-control mappings-select disabler-check-select', 'disabled' => true]) !!}
                                            <span class="loading hidden">
                                                <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                                                <span class="sr-only">Loading...</span>
                                            </span>
                                            <span class="help-block">
                                                Choose what will be used to map identifiers.
                                            </span>
                                            @if ($errors->has('mapping'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('mapping') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-offset-3 col-md-9">
                            {!! Form::button('<i class="fa fa-floppy-o"></i> Submit', ['type' => 'submit', 'class' => 'btn btn-success']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<style type="text/css">
    .select2-result-repository {
        padding-top: 4px;
        padding-bottom: 3px;
    }

    .select2-result-repository__title {
        color: black;
        font-weight: bold;
        word-wrap: break-word;
        line-height: 1.1;
        margin-bottom: 4px;
    }

    .select2-result-repository__description {
        font-size: 13px;
        color: #777;
        margin-top: 4px;
    }
</style>
<script>
    $(function () {
        var platformSelect = $('.platform-select');
        platformSelect.select2({
            ajax: {
                url: "{{ route('platforms-list-json') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 30) < data.total
                        }
                    }
                },
                cache: true
            },
            escapeMarkup: function (markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 1,
            templateResult: function (repo) {
                if (repo.loading) return repo.text;
                //return repo.title + ' - ' + repo.organism;

                var markup = "<div class='select2-result-repository clearfix'>" +
                        "<div class='select2-result-repository__meta'>" +
                        "<div class='select2-result-repository__title'>" + repo.title + "</div>";
                if (repo.organism) {
                    markup += "<div class='select2-result-repository__description'>" + repo.organism + "</div>";
                }
                markup += "</div></div>";
                return markup;
            }, // omitted for brevity, see the source of this page
            templateSelection: function (selection) {
                return (selection.title + ' - ' + selection.organism) || selection.text;
            }
        });
        platformSelect.change(function () {
            var mappingsSelect = $('.mappings-select'), platformId = platformSelect.val();
            if (platformId) {
                mappingsSelect.parent().find('.loading').removeClass('hidden');
                $.ajax({
                    dataType: 'json',
                    method: 'POST',
                    url: '{{ url('/platforms') }}/' + platformId + '/mappings',
                    success: function (data) {
                        if (data.ok) {
                            mappingsSelect.removeAttr('disabled');
                            var html = '<option value=""> -- Select an option -- </option>';
                            for (var x in data.data) {
                                html += '<option value="' + x + '">' + data.data[x] + '</option>';
                            }
                            mappingsSelect.html(html);
                        } else {
                        }
                        mappingsSelect.parent().find('.loading').addClass('hidden');
                    },
                    error: function () {
                        mappingsSelect.parent().find('.loading').addClass('hidden');
                    }
                });
            } else {
                mappingsSelect.attr('disabled', 'disabled');
            }
        }).change();
        $('.mapping-enabler').change(function () {
            if (this.checked) {
                $('.enabler-check-select').removeAttr('disabled').change();
            } else {
                $('.disabler-check-select').removeAttr('disabled').attr('disabled', 'disabled');
            }
        }).change();
        $('.selections-select').select2({
            ajax: {
                url: "{{ route('selections-lists-json') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 30) < data.total
                        }
                    }
                },
                cache: true
            },
            escapeMarkup: function (markup) {
                return markup;
            },
            minimumInputLength: 1,
            templateResult: function (repo) {
                if (repo.loading) return repo.text;
                return repo.name;
            },
            templateSelection: function (selection) {
                return selection.name || selection.text;
            }
        });
        $('.mapped-selections-select').select2({
            ajax: {
                url: "{{ route('mapped-selections-lists-json') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 30) < data.total
                        }
                    }
                },
                cache: true
            },
            escapeMarkup: function (markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 1,
            templateResult: function (repo) {
                if (repo.loading) return repo.text;
                var markup = "<div class='select2-result-repository clearfix'>" +
                        "<div class='select2-result-repository__meta'>" +
                        "<div class='select2-result-repository__title'>" + repo.name +
                        " - Mapped to " + repo.mapping + "</div>";
                if (repo.organism) {
                    markup += "<div class='select2-result-repository__description'>" + repo.organism + "</div>";
                }
                markup += "</div></div>";
                return markup;
            }, // omitted for brevity, see the source of this page
            templateSelection: function (selection) {
                return (selection.name + ' - Mapped to ' + selection.mapping) || selection.text;
            }
        });
        $('#dataChooser').parent().find('a').click();
        $('#integrationMethodOptions').parent().find('a').click();
        $('#identifiersMapping').parent().find('a').click();
    });
</script>
@endpush