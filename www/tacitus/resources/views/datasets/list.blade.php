@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Datasets</h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
        <div class="col-lg-12">
            {{--  table table-condensed table-responsive --}}
            <table class="display responsive no-wrap" id="datasets-table">
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

@endsection

@push('scripts')
<script>
    $(function () {
        $('#datasets-table').dataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: '{{ route('datasets-lists-data') }}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'original_id', name: 'original_id'},
                {data: 'source_id', name: 'source_id'},
                {data: 'title', name: 'title'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            columnDefs: [
                {responsivePriority: 1, targets: 0},
                {responsivePriority: 2, targets: 1},
                {responsivePriority: 1, targets: 2},
                {responsivePriority: 1, targets: 3},
                {responsivePriority: 2, targets: 4},
                {responsivePriority: 1, targets: 5}
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'

            }
        });
    });
</script>
@endpush