<div class="form-group{{ $errors->has('accessionNumber') ? ' has-error' : '' }}">
    {!! Form::label('accessionNumber', 'Accession Number', ['class' => 'col-md-3 control-label']) !!}
    <div class="col-md-9">
        {!! Form::text('accessionNumber', null, ['class' => 'form-control']) !!}
        <span class="help-block">
            Specify the accession number of GEO platform
        </span>
        @if ($errors->has('accessionNumber'))
            <span class="help-block">
                <strong>{{ $errors->first('accessionNumber') }}</strong>
            </span>
        @endif
    </div>
</div>
