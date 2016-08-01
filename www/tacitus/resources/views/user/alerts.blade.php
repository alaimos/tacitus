@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Alerts</h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="list-group">
                @forelse($notifications as $notif)
                    <div class="list-group-item">
                        {!! Notifynder::readOne($notif->id)->getNotifyBodyAttribute() !!}
                        <span class="pull-right text-muted small">
                            <em>{{ $notif->created_at->diffForHumans() }}</em>
                        </span>
                    </div>
                @empty
                    <div class="list-group-item">
                        <i class="fa fa-comment fa-fw"></i> No unread alerts!
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
