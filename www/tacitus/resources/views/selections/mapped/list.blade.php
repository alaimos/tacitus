@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Mapped Selections
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
            <table class="table table-condensed table-responsive table-hover table-striped no-wrap"
                   id="selections-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Mapped to</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
                </thead>
            </table>
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
    $(function () {
        $('#selections-table').dataTable({
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
                url: '{{ route('mapped-selections-lists-data') }}',
                method: 'POST'
            },
            columns: [
                {data: 'id', name: 'mapped_sample_selections.id'},
                {data: 'name', name: 'sample_selections.name'},
                {data: 'mapping', name: 'platform_mappings.name'},
                {data: 'created_at', name: 'mapped_sample_selections.created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            language: {
                processing: '<i class="fa fa-spinner faa-spin fa-3x fa-fw animated"></i><span class="sr-only">Loading...</span>'

            }
        });
    });
</script>
@endpush