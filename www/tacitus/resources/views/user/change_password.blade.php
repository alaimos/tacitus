@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Change Password
                <span class="pull-right">
                    <a href="{{ route('user::profile', ((!$isCurrent && $isAdmin) ? $user : [])) }}"
                       class="btn btn-info">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back
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
                          action="{{ route('user::change-password-post', ((!$isCurrent && $isAdmin) ? $user : [])) }}">
                        {{ csrf_field() }}
                        @if ($isCurrent)
                            <div class="form-group{{ $errors->has('old-password') ? ' has-error' : '' }}">
                                <label for="old-password" class="col-md-4 control-label">Old Password</label>

                                <div class="col-md-6">
                                    <input id="old-password" type="password" class="form-control" name="old-password">

                                    @if ($errors->has('old-password'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('old-password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">New Password</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label for="password-confirm" class="col-md-4 control-label">Confirm Password</label>
                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control"
                                       name="password_confirmation">

                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-btn fa-refresh"></i> Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
