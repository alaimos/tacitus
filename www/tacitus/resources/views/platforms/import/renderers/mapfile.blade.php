<div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
    {!! Form::label('title', 'Title', ['class' => 'col-md-3 control-label']) !!}
    <div class="col-md-9">
        {!! Form::text('title', null, ['class' => 'form-control']) !!}
        <span class="help-block">
            Specify the title of this platform
        </span>
        @if ($errors->has('title'))
            <span class="help-block">
                <strong>{{ $errors->first('title') }}</strong>
            </span>
        @endif
    </div>
</div>
<div class="form-group{{ $errors->has('organism') ? ' has-error' : '' }}">
    {!! Form::label('organism', 'Organism', ['class' => 'col-md-3 control-label']) !!}
    <div class="col-md-9">
        {!! Form::text('organism', null, ['class' => 'form-control']) !!}
        <span class="help-block">
            Specify the organism of this platform
        </span>
        @if ($errors->has('organism'))
            <span class="help-block">
                <strong>{{ $errors->first('organism') }}</strong>
            </span>
        @endif
    </div>
</div>
<div class="form-group{{ $errors->has('mapFile') ? ' has-error' : '' }}">
    {!! Form::label('mapFile', 'Map File', ['class' => 'col-md-3 control-label']) !!}
    <div class="col-md-9">
        {!! Form::file('mapFile', ['class' => 'form-control']) !!}
        <span class="help-block">
            Specify the file to import
        </span>
        @if ($errors->has('mapFile'))
            <span class="help-block">
                <strong>{{ $errors->first('mapFile') }}</strong>
            </span>
        @endif
    </div>
</div>
