@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Platform submission
                <span class="pull-right">
                    <a href="{{ route('platforms-lists') }}" class="btn btn-info">
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
                    {!! Form::open(['class' => 'form-horizontal', 'method' => 'POST', 'route' => 'platforms-submission-process', 'enctype' => 'multipart/form-data']) !!}
                    <div class="form-group{{ $errors->has('importer_type') ? ' has-error' : '' }}">
                        {!! Form::label('importer_type', 'Import from', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            {!! Form::select('importer_type', $importers, null, ['class' => 'form-control importer-select']) !!}
                            <span class="help-block">
                                Choose the type of source from which the platform will be imported.
                            </span>
                            @if ($errors->has('importer_type'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('importer_type') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group{{ $errors->has('private') ? ' has-error' : '' }}">
                        <div class="col-md-offset-3 col-md-9">
                            <div class="checkbox">
                                <label>
                                    {!! Form::checkbox('private', 1, null, []) !!}
                                    Is this platform private?
                                </label>
                            </div>
                            @if ($errors->has('private'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('private') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="import-form-content">

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
<script>
    $(function () {
        var container = $('.import-form-content');
        $('.importer-select').change(function () {
            container.html('<div class="text-center"><i class="fa fa-spinner faa-spin fa-3x fa-fw ' +
                    'animated"></i><span class="sr-only">Loading...</span></div>');
            var importerType = $(this).val();
            $.ajax({
                dataType: 'json',
                method: 'POST',
                url: '{{ route('platforms-submission-form') }}',
                data: {
                    'importer_type': importerType,
                    'input': '{!! json_encode($input) !!}',
                    'errors': '{!! json_encode($errors->getBags()) !!}'
                },
                success: function (data) {
                    if (data.ok) {
                        container.html(data.content);
                    } else {
                        container.html('');
                        alert(data.content);
                    }
                },
                error: function () {
                    container.html('');
                    alert('Unknown error');
                }
            });
        }).change();
    });
</script>
@endpush