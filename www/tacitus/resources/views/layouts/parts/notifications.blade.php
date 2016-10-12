@if(!Auth::guest())
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
            <i class="fa fa-bell fa-fw"></i>
            @if(Auth::user()->countNotificationsNotRead())
                <span class="badge badge-important badge-unread-alerts"
                      data-url="{{ route('user::poll-alerts') }}">
                    {{Auth::user()->countNotificationsNotRead()}}
                </span>
            @else
                <span class="badge badge-important badge-unread-alerts hidden"
                      data-url="{{ route('user::poll-alerts') }}">
                    0
                </span>
            @endif
            <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-alerts">
            <li>
                <div class="text-left" style="padding-left: 20px;">
                    <strong>Recent Alerts</strong>
                    <span class="pull-right">
                        <small>
                            <a href="Javascript:;" class="mark-as-read-link"
                               data-url="{{ route('user::mark-alerts') }}">
                                Mark all as read
                            </a>
                        </small>
                    </span>
                </div>
            </li>
            <li class="divider recent-alerts-header-divider"></li>
            @foreach(Auth::user()->getNotificationsNotRead(5) as $notification)
                <li class="user-notification-container">
                    <a href="Javascript:;" class="user-notification-link"
                       data-url="{{ route('user::mark-alerts', [$notification->id]) }}">
                        <div>
                            {!! $notification->getNotifyBodyAttribute() !!}
                            <span class="pull-right text-muted small">
                                <em>{{ $notification->created_at->diffForHumans() }}</em>
                            </span>
                        </div>
                    </a>
                </li>
                <li class="divider user-notification-divider"></li>
            @endforeach
            <li class="no-alerts @if(Auth::user()->countNotificationsNotRead() > 0) hidden @endif">
                <a href="Javascript:;">
                    <div>
                        <i class="fa fa-comment fa-fw"></i> No unread alerts!
                    </div>
                </a>
            </li>
            <li class="no-alerts divider @if(Auth::user()->countNotificationsNotRead() > 0) hidden @endif"></li>
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