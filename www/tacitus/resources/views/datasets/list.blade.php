@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Datasets
                @if(user_can(\App\Utils\Permissions::SUBMIT_DATASETS))
                    <span class="pull-right">
                        <a href="{{ route('datasets-submission') }}" class="btn btn-success">
                            <i class="fa fa-plus-circle" aria-hidden="true"></i> Submit dataset
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
        <div class="col-lg-12">
            {{--  table table-condensed table-responsive --}}
            <table class="table table-condensed table-responsive table-hover table-striped no-wrap" id="datasets-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Original Id</th>
                    <th>Source</th>
                    <th>Title</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            @if(user_can(\App\Utils\Permissions::SUBMIT_DATASETS))
                <span class="pull-right">
                    <a href="{{ route('datasets-submission') }}" class="btn btn-success">
                        <i class="fa fa-plus-circle" aria-hidden="true"></i> Submit dataset
                    </a>
                </span>
            @endif
        </div>
        <!-- /.col-lg-12 -->
    </div>

@endsection
@push('head-scripts')
<script src="{{ url('js/pdfmake.min.js') }}"></script>
<script src="{{ url('js/vfs_fonts.js') }}"></script>
<script src="{{ url('js/jszip.min.js') }}"></script>
@endpush
@push('scripts')
<script>
    $(function () {
        $('#datasets-table').dataTable({
            dom: "<'row'<'col-sm-3'l><'col-sm-6 text-center'B><'col-sm-3'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            responsive: true,
            processing: true,
            serverSide: true,
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'print'
            ],
            ajax: {
                url: '{{ route('datasets-lists-data') }}',
                method: 'POST'
            },
            columns: [
                {data: 'id', name: 'datasets.id'},
                {data: 'original_id', name: 'datasets.original_id'},
                {data: 'display_name', name: 'sources.display_name'},
                {data: 'title', name: 'datasets.title'},
                {data: 'created_at', name: 'datasets.created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            columnDefs: [
                {responsivePriority: 1, targets: 0},
                {responsivePriority: 3, targets: 1},
                {responsivePriority: 2, targets: 2},
                {responsivePriority: 1, targets: 3},
                {responsivePriority: 3, targets: 4},
                {responsivePriority: 2, targets: 5}
            ],
            language: {
                processing: '<i class="fa fa-spinner faa-spin fa-3x fa-fw animated"></i><span class="sr-only">Loading...</span>'

            }
        });
    });
</script>
@endpush