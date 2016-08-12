@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Jobs
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
            <table class="table table-condensed table-responsive table-hover table-striped no-wrap" id="jobs-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>View</th>
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
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

@endsection

@push('scripts')
<script>
    $(function () {
        var tbl = $('#jobs-table'),
                viewLog = function (id) {
                    $.ajax({
                        dataType: 'json',
                        method: 'GET',
                        url: '{{ url('/jobs') }}/' + id + '/view',
                        success: function (data) {
                            var lv = $('#log-viewer-dialog');
                            lv.find('.modal-body').find('pre').html(data.log);
                            lv.modal('show');
                            $('i.loading-job').remove();
                        }
                    });
                };
        tbl.dataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('jobs-lists-data') }}',
                method: 'POST'
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'job_type', name: 'job_type'},
                {data: 'status', name: 'status'},
                {data: 'created_at', name: 'created_at'},
                {data: 'updated_at', name: 'updated_at'},
                {data: 'view', name: 'view', orderable: false, searchable: false}
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
        tbl.on('click', 'a.btn-view-job', function () {
            var t = $(this), id = t.data('id');
            t.parent().append('&nbsp;&nbsp;<i class="fa fa-spinner fa-pulse fa-fw loading-job"></i>');
            viewLog(id);
        })


    });
</script>
@endpush