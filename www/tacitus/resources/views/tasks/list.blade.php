@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Tasks
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
        <div class="col-lg-12">
            {{--  table table-condensed table-responsive --}}
            <table class="table table-condensed table-responsive table-hover table-striped no-wrap" id="tasks-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <div id="log-viewer-dialog" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-file-text" aria-hidden="true"></i> Log Viewer</h4>
                </div>
                <div class="modal-body">
                    <pre>

                    </pre>
                </div>
                <div class="modal-footer">
                    <span class="pull-left">
                        <button type="button" class="btn btn-primary live-log-button" data-toggle="button"
                                aria-pressed="false" autocomplete="off">
                            <i class="fa fa-play fa-fw"></i> Live logs
                        </button>
                        <span class="updating"></span>
                    </span>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fa fa-times-circle fa-fw"></i> Close
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

@endsection

@push('scripts')
<script>
    $(function () {
        var dialog = $('#log-viewer-dialog'),
                updatingIcon = dialog.find('.updating'),
                liveButton = dialog.find('.live-log-button'), currentId, timer;
        dialog.on('hidden.bs.modal', function () {
            if (liveButton.hasClass('active')) {
                liveButton.button('toggle');
            }
            updatingIcon.html('');
            currentId = null;
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        });
        liveButton.on('click', function () {
            if (!liveButton.hasClass('active')) {
                updatingIcon.html('<i class="fa fa-cog fa-spin  fa-fw"></i><span class="sr-only">Updating...</span>');
                timer = setInterval(function () {
                    if (!currentId) return;
                    $.ajax({
                        dataType: 'json',
                        method: 'GET',
                        url: '{{ url('/tasks') }}/' + currentId + '/view',
                        success: function (data) {
                            dialog.find('.modal-body').find('pre').html(data.log);
                        }
                    });
                }, 10000);
            } else {
                updatingIcon.html('');
                clearInterval(timer);
                timer = null;
            }
        });
        var tbl = $('#tasks-table'),
                viewLog = function (id) {
                    currentId = id;
                    $.ajax({
                        dataType: 'json',
                        method: 'GET',
                        url: '{{ url('/tasks') }}/' + id + '/view',
                        success: function (data) {
                            dialog.find('.modal-body').find('pre').html(data.log);
                            dialog.modal('show');
                            $('i.loading-task').remove();
                        }
                    });
                };
        tbl.dataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('tasks-lists-data') }}',
                method: 'POST'
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'description', name: 'description'},
                {data: 'status', name: 'status'},
                {data: 'created_at', name: 'created_at'},
                {data: 'updated_at', name: 'updated_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            columnDefs: [
                {responsivePriority: 1, targets: 0},
                {responsivePriority: 1, targets: 1},
                {responsivePriority: 1, targets: 2},
                {responsivePriority: 3, targets: 3},
                {responsivePriority: 3, targets: 4},
                {responsivePriority: 2, targets: 5}
            ],
            language: {
                processing: '<i class="fa fa-spinner faa-spin fa-3x fa-fw animated"></i><span class="sr-only">Loading...</span>'

            }
        });
        tbl.on('click', 'a.btn-view-task', function () {
            var t = $(this), id = t.data('id');
            t.parent().append('&nbsp;&nbsp;<i class="fa fa-spinner fa-pulse fa-fw loading-task"></i>');
            viewLog(id);
        })


    });
</script>
@endpush