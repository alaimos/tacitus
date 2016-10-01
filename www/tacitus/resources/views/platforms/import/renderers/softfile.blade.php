<div class="form-group{{ $errors->has('softFile') ? ' has-error' : '' }}">
    {!! Form::label('softFile', 'SOFT File', ['class' => 'col-md-3 control-label']) !!}
    <div class="col-md-9">
        {!! Form::file('softFile', ['class' => 'form-control']) !!}
        <span class="help-block">
            Specify the SOFT file to import
        </span>
        @if ($errors->has('softFile'))
            <span class="help-block">
                <strong>{{ $errors->first('softFile') }}</strong>
            </span>
        @endif
    </div>
</div>
