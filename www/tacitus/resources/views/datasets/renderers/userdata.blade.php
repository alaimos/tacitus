<div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
    {!! Form::label('title', 'Title', ['class' => 'col-md-3 control-label']) !!}
    <div class="col-md-9">
        {!! Form::text('title', null, ['class' => 'form-control']) !!}
        <span class="help-block">
            Specify the title of this dataset
        </span>
        @if ($errors->has('title'))
            <span class="help-block">
                <strong>{{ $errors->first('title') }}</strong>
            </span>
        @endif
    </div>
</div>
<div class="form-group{{ $errors->has('metadataFile') ? ' has-error' : '' }}">
    {!! Form::label('metadataFile', 'Metadata File', ['class' => 'col-md-3 control-label']) !!}
    <div class="col-md-9">
        {!! Form::file('metadataFile', ['class' => 'form-control']) !!}
        <span class="help-block">
            Upload a file where metadata for each sample are stored (a tab-separated text file optionally compressed with gzip or zip)
        </span>
        @if ($errors->has('metadataFile'))
            <span class="help-block">
                <strong>{{ $errors->first('metadataFile') }}</strong>
            </span>
        @endif
    </div>
</div>
<div class="form-group{{ $errors->has('dataFile') ? ' has-error' : '' }}">
    {!! Form::label('dataFile', 'Data File', ['class' => 'col-md-3 control-label']) !!}
    <div class="col-md-9">
        {!! Form::file('dataFile', ['class' => 'form-control']) !!}
        <span class="help-block">
            Upload a file where data for each sample are stored (a tab-separated text file optionally compressed with gzip or zip)
        </span>
        @if ($errors->has('dataFile'))
            <span class="help-block">
                <strong>{{ $errors->first('dataFile') }}</strong>
            </span>
        @endif
    </div>
</div>