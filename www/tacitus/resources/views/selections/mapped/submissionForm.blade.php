@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Map identifiers for {{ $selection->name }}
                <span class="pull-right">
                    <a href="{{ route('selections-lists') }}" class="btn btn-info">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back
                    </a>
                </span>
            </h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <div class="panel panel-default">
                <div class="panel-body">
                    {!! Form::open(['class' => 'form-horizontal', 'method' => 'POST', 'route' => ['mapped-selections-do-submit', $selection]]) !!}
                    <div class="form-group{{ $errors->has('platform') ? ' has-error' : '' }}">
                        {!! Form::label('platform', 'Platform', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            <select name="platform" id="platform" class="form-control platform-select">
                                @if($selection->platform !== null)
                                    <option value="{{ $selection->platform->id }}"
                                            selected="selected">{{ $selection->platform->title }} - {{
                                            $selection->platform->organism }}</option>
                                @endif
                            </select>
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
                            {!! Form::select('mapping', [], null, ['class' => 'form-control mappings-select', 'disabled' => true]) !!}
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
    });
</script>
@endpush