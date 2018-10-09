<div class="modal-header panel-info">
    <button type="button" class="close" aria-label="Close" data-dismiss="modal">
        <span aria-hidden="true" class="white-text">&times;</span>
    </button>
    <h4 class="modal-title modal-title white-text w-100 font-weight-bold py-2">Edit Galaxy Account</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="panel-body">
                <form class="form-horizontal galaxy-form" role="form" method="POST" action="#">
                    {{ csrf_field() }}
                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        {!! Form::label('name', 'Name', ['class' => 'col-md-4 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('name', $credential->name , ['class' => 'form-control']) !!}
                            @if ($errors->has('name'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group{{ $errors->has('hostname') ? ' has-error' : '' }}">
                        {!! Form::label('hostname', 'Hostname', ['class' => 'col-md-4 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('hostname', $credential->hostname, ['class' => 'form-control']) !!}
                            @if ($errors->has('hostname'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('hostname') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group{{ $errors->has('port') ? ' has-error' : '' }}">
                        {!! Form::label('port', 'Port number', ['class' => 'col-md-4 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::number('port', $credential->port , ['class' => 'form-control']) !!}
                            @if ($errors->has('port'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('port') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group{{ $errors->has('api_key') ? ' has-error' : '' }}">
                        {!! Form::label('api_key', 'API Key', ['class' => 'col-md-4 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::password('api_key', ['class' => 'form-control']) !!}
                            @if ($errors->has('api_key'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('api_key') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group{{ $errors->has('api_key_confirmation') ? ' has-error' : '' }}">
                        {!! Form::label('api_key_confirmation', 'Confirm Api Key', ['class' => 'col-md-4 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::password('api_key_confirmation', ['class' => 'form-control']) !!}
                            @if ($errors->has('api_key_confirmation'))
                                <span class="help-block">
                                        <strong>{{ $errors->first('api_key_confirmation') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('use_https', 'Use HTTPS', ['class' => 'col-md-4 form-check-label text-right']) !!}
                        <div class="col-md-6">
                            {!! Form::checkbox('use_https','value',$credential->use_https,['class' => 'form-check-input'])!!}
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<div class="modal-footer">
    <a href="Javascript:;" class="btn btn-primary" data-dismiss="modal" onclick="$('.galaxy-form').submit();">
        <i class="fa fa-btn fa-pencil" aria-hidden="true"></i> Save credential
    </a>
</div>

