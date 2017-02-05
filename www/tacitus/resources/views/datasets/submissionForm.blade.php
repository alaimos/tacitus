@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Dataset submission
                <span class="pull-right">
                    <a href="{{ route('datasets-lists') }}" class="btn btn-info">
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
                    {!! Form::open(['class' => 'form-horizontal', 'method' => 'POST', 'route' => 'datasets-submission-process', 'enctype' => 'multipart/form-data']) !!}
                    <div class="form-group{{ $errors->has('source_type') ? ' has-error' : '' }}">
                        {!! Form::label('source_type', 'Source', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            {!! Form::select('source_type', $sources, null, ['class' => 'form-control source-type']) !!}
                            <span class="help-block">
                                Chose a data source.</span>
                            @if ($errors->has('source_type'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('source_type') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group{{ $errors->has('accession') ? ' has-error' : '' }}">
                        {!! Form::label('accession', 'Accession Number', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            {!! Form::text('accession', null, ['class' => 'form-control']) !!}
                            <span class="help-block">
                                Specify the accession number used by the data source.
                            </span>
                            @if ($errors->has('accession'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('accession') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="import-form-content"></div>
                    <div class="form-group{{ $errors->has('private') ? ' has-error' : '' }}">
                        <div class="col-md-offset-3 col-md-9">
                            <div class="checkbox">
                                <label>
                                    {!! Form::checkbox('private', 1, null, []) !!}
                                    Is this dataset available only on your account?
                                </label>
                            </div>
                            @if ($errors->has('private'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('private') }}</strong>
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
<script>
    $(function () {
        var container = $('.import-form-content');
        $('.source-type').change(function () {
            container.html('<div class="text-center"><i class="fa fa-spinner faa-spin fa-3x fa-fw ' +
                'animated"></i><span class="sr-only">Loading...</span></div>');
            var sourceType = $(this).val();
            $.ajax({
                dataType: 'json',
                method: 'POST',
                url: '{{ route('datasets-submission-form') }}',
                data: {
                    'source_type': sourceType,
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