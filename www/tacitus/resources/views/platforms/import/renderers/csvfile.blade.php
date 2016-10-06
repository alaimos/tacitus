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
<div class="form-group{{ $errors->has('csvFile') ? ' has-error' : '' }}">
    {!! Form::label('csvFile', 'CSV File', ['class' => 'col-md-3 control-label']) !!}
    <div class="col-md-9">
        {!! Form::file('csvFile', ['class' => 'form-control']) !!}
        <span class="help-block">
            Specify the file to import
        </span>
        @if ($errors->has('csvFile'))
            <span class="help-block">
                <strong>{{ $errors->first('csvFile') }}</strong>
            </span>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-md-9 col-md-offset-3">
        <fieldset>
            <legend>
                <a role="button" data-toggle="collapse" href="#collapseAdvancedOptions" aria-expanded="false"
                   aria-controls="collapseAdvancedOptions">
                    Advanced Options
                </a>
                <span class="pull-right">
                    <a role="button" data-toggle="collapse" href="#collapseAdvancedOptions" aria-expanded="false"
                       aria-controls="collapseAdvancedOptions">
                        <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                    </a>
                </span>
            </legend>
            <div class="collapse" id="collapseAdvancedOptions">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group{{ $errors->has('separator') ? ' has-error' : '' }}">
                            {!! Form::label('separator', 'Separator', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-9">
                                {!! Form::text('separator', ',', ['class' => 'form-control']) !!}
                                @if ($errors->has('separator'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('separator') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group{{ $errors->has('comment') ? ' has-error' : '' }}">
                            {!! Form::label('comment', 'Comment Symbol', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-9">
                                {!! Form::text('comment', '#', ['class' => 'form-control']) !!}
                                @if ($errors->has('quote'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('comment') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group{{ $errors->has('identifier') ? ' has-error' : '' }}">
                            {!! Form::label('identifier', 'Identifier Field', ['class' => 'col-md-3 control-label']) !!}
                            <div class="col-md-9">
                                {!! Form::text('identifier', '1', ['class' => 'form-control']) !!}
                                @if ($errors->has('identifier'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('identifier') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</div>