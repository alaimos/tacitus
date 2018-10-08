@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Galaxy credentials register
                <span class="pull-right">
                    <a href="{{route('user::profile').'#galaxy_table' }}" class="btn btn-info">
                        <i class="fa fa-arrow-left fa-fw" aria-hidden="true"></i> Go Back
                    </a>
                </span>
            </h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST"
                          action="{{ route('add-doCredential', ((!$isCurrent && $isAdmin) ? ['user' => $user] : [])) }}">
                        {{ csrf_field() }}
                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        {!! Form::label('name', 'Name', ['class' => 'col-md-4 control-label']) !!}
                        <div class="col-md-6">
                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
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
                            {!! Form::text('hostname', null, ['class' => 'form-control']) !!}
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
                            {!! Form::number('port', null, ['class' => 'form-control']) !!}
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
                            {!! Form::checkbox('use_https','true',true,['class' => 'form-check-input'])!!}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-btn fa-floppy-o"></i> Create Credential
                            </button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection