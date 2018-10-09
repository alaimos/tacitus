@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                {{$user->name ?:'User'}}'s profile
                @if ($isAdmin)
                    <span class="pull-right">
                        <a href="{{ route('user::list') }}" class="btn btn-info">
                            <i class="fa fa-arrow-left fa-fw" aria-hidden="true"></i> Go to Users list
                        </a>
                    </span>
                @endif
            </h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
        <div class="col-lg-12 alert-container">
            @include('sun::flash')
        </div>
    </div>
    <!-- /.row -->

    <div class="row">
        <div class="col-lg-3 col-md-6 col-lg-offset-1">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-comments fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">{{$count = $statistics['datasets']['all']}}</div>
                            <div>{{ str_plural('Dataset', $count) }}</div>
                        </div>
                    </div>
                </div>
                <div class="list-group">
                    <div class="list-group-item">
                        <i class="fa fa-spinner faa-spin animated fa-fw"></i>
                        Pending: <span class="label label-default">{{$statistics['datasets']['pending']}}</span>
                    </div>
                    <div class="list-group-item">
                        <i class="fa fa-check-circle fa-fw"></i>
                        Ready: <span class="label label-success">{{$statistics['datasets']['ready']}}</span>
                    </div>
                    <div class="list-group-item">
                        <i class="fa fa-exclamation-circle fa-fw"></i>
                        Failed: <span class="label label-danger">{{$statistics['datasets']['failed']}}</span>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-green">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-table fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">{{$count = $statistics['selections']['all']}}</div>
                            <div>{{ str_plural('Selection', $count) }}</div>
                        </div>
                    </div>
                </div>
                <div class="list-group">
                    <div class="list-group-item">
                        <i class="fa fa-spinner faa-spin animated fa-fw"></i>
                        Pending: <span class="label label-default">{{$statistics['selections']['pending']}}</span>
                    </div>
                    <div class="list-group-item">
                        <i class="fa fa-check-circle fa-fw"></i>
                        Ready: <span class="label label-success">{{$statistics['selections']['ready']}}</span>
                    </div>
                    <div class="list-group-item">
                        <i class="fa fa-exclamation-circle fa-fw"></i>
                        Failed: <span class="label label-danger">{{$statistics['selections']['failed']}}</span>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-md-offset-3 col-lg-offset-0">
            <div class="panel panel-yellow">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-cog fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">{{$count = $statistics['jobs']['all']}}</div>
                            <div>{{ str_plural('Job', $count) }}</div>
                        </div>
                    </div>
                </div>
                <div class="list-group">
                    <div class="list-group-item">
                        <i class="fa fa-pause fa-fw"></i>
                        Queued: <span class="label label-default">{{$statistics['jobs']['queued']}}</span>
                    </div>
                    <div class="list-group-item">
                        <i class="fa fa-spinner faa-spin animated fa-fw"></i>
                        Processing: <span class="label label-default">{{$statistics['jobs']['processing']}}</span>
                    </div>
                    <div class="list-group-item">
                        <i class="fa fa-check-circle fa-fw"></i>
                        Completed: <span class="label label-success">{{$statistics['jobs']['completed']}}</span>
                    </div>
                    <div class="list-group-item">
                        <i class="fa fa-exclamation-circle fa-fw"></i>
                        Failed: <span class="label label-danger">{{$statistics['jobs']['failed']}}</span>
                    </div>
                </div>
            </div>
        </div>
        {{--<div class="col-lg-3 col-md-6">
            <div class="panel panel-red">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-support fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">13</div>
                            <div>Support Tickets!</div>
                        </div>
                    </div>
                </div>
                <a href="#">
                    <div class="panel-footer">
                        <span class="pull-left">View Details</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>--}}
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
                <div class="panel-group">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <i class="fa fa-user fa-fw"></i>
                            Account details
                        </div>
                        <div class="panel-body">
                            <dl class="dl-horizontal">
                                <dt>Name</dt>
                                <dd>{{$user->name}}</dd>
                                <dt>Affiliation</dt>
                                <dd>{{$user->affiliation}}</dd>
                                <dt>E-Mail</dt>
                                <dd>{{$user->email}}</dd>
                                <dt>Registered</dt>
                                <dd>{{$user->created_at->diffForHumans()}}</dd>
                            </dl>
                        </div>
                        <div class="panel-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    @if ($isCurrent)
                                        <a href="{{ route('user::change-password') }}" role="button" class="btn btn-success">
                                            <i class="fa fa-key fa-fw"></i>Change password
                                        </a>
                                    @elseif(!$isCurrent && $isAdmin)
                                        <a href="{{ route('user::change-password', $user) }}"
                                           role="button" class="btn btn-success">
                                            <i class="fa fa-key fa-fw"></i>Change password
                                        </a>
                                    @endif
                                </div>
                                <div class="col-md-6 text-right">
                                    @if ($isCurrent)
                                        <a href="{{ route('user::edit-profile') }}" role="button" class="btn btn-success">
                                            <i class="fa fa-user fa-fw"></i> Edit Profile
                                        </a>
                                    @elseif(!$isCurrent && $isAdmin)
                                        <a href="{{ route('user::edit-profile', $user) }}" role="button"
                                           class="btn btn-success">
                                            <i class="fa fa-user fa-fw"></i> Edit Profile
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <br><br>
                    <div class="panel panel-info">
                        <a name="galaxy_table"></a>
                        <div class="panel-heading">
                            <i class="fa fa-folder-open"></i>
                            Galaxy details
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12 alert-container">
                                    @include('sun::flash')
                                </div>
                            </div>
                            <!-- /.row -->
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-condensed table-responsive table-hover table-striped no-wrap"
                                           id="galaxy-records-table">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Hostname</th>
                                            <th>Port</th>
                                            <th>Uses https</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    @if ($isCurrent)
                                        <a href="{{route('add-credential',['user' => $user])}}" role="button" class="btn btn-success">
                                            <i class="fa fa-plus-circle"></i>
                                            Add credentials
                                        </a>
                                    @elseif(!$isCurrent && $isAdmin)
                                        <a href="{{route('add-credential',['user' => $user])}}" role="button" class="btn btn-success">
                                            <i class="fa fa-plus-circle"></i>
                                            Add credentials
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <!-- /.row -->

    <!-- Modal -->
    <div id="galaxy_edit_modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-md-12">
            <!-- Modal content-->
            <div class="modal-content">
            </div>
        </div>
    </div>

@endsection

@push('head-scripts')
    <script src="{{ url('js/pdfmake.min.js') }}"></script>
    <script src="{{ url('js/vfs_fonts.js') }}"></script>
    <script src="{{ url('js/jszip.min.js') }}"></script>
@endpush
@push('scripts')
    <script>
        var simple_checkbox = function ( data, type, full, meta ) {
            var is_checked = data == true ? "checked" : "";
            return '<input type="checkbox" class="checkbox" ' + is_checked + ' disabled/>';
        }
        $(function () {
            $('#galaxy-records-table').dataTable({
                pageLength: 5,
                lengthMenu: [ 5, 10, 25, 50, 100 ],
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    url: '{{ route('galaxy-list-data',['user' => $user] )}}',
                    method: 'POST'
                },
                columnDefs: [
                    {
                        "searchable": false,
                        "orderable": false,
                        "targets": 0
                    },
                    {
                        "targets": 4,
                        "className": "text-center",
                    }
                ],
                columns: [
                    /*{data: 'id', name: 'id'},*/
                    {data: 'DT_Row_Index', name: 'index'},
                    {data: 'name', name: 'name'},
                    {data: 'hostname', name: 'hostname'},
                    {data: 'port', name: 'port'},
                    {data: 'use_https', render: simple_checkbox},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                language: {
                    processing: '<i class="fa fa-spinner faa-spin fa-3x fa-fw animated"></i><span class="sr-only">Loading...</span>'

                }
            });

        });

    </script>
@endpush
