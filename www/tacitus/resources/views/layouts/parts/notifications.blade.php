@if(!Auth::guest())
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
            <i class="fa fa-bell fa-fw"></i>
            @if(Auth::user()->countNotificationsNotRead())
                <span class="badge badge-important">{{Auth::user()->countNotificationsNotRead()}}</span>
            @endif
            <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-alerts">
            @forelse(Auth::user()->getNotificationsNotRead(10) as $notification)
                <li>
                    <a href="Javascript:;">
                        <div>
                            {!! $notification->getNotifyBodyAttribute() !!}
                            <span class="pull-right text-muted small">
                                            <em>{{ $notification->created_at->diffForHumans() }}</em>
                                        </span>
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
            @empty
                <li>
                    <a href="Javascript:;">
                        <div>
                            <i class="fa fa-comment fa-fw"></i> No unread alerts!
                        </div>
                    </a>
                </li>
                <li class="divider"></li>
            @endforelse
            <li>
                <a class="text-center" href="{{ route('user::alerts') }}">
                    <strong>See All Alerts</strong>
                    <i class="fa fa-angle-right"></i>
                </a>
            </li>
        </ul>
        <!-- /.dropdown-alerts -->
    </li>
@endif
<!-- /.dropdown -->