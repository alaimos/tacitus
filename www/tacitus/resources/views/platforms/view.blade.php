@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                View Platform
                <span class="pull-right">
                    <a href="{{ route('platforms-lists') }}" class="btn btn-info">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back
                    </a>
                </span>
            </h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <table class="table table-condensed table-hover table-striped no-wrap" id="data-table">
                <thead>
                <tr>
                    <th class="always-visible">Probe</th>
                    @foreach($platform->mappingList() as $mapping)
                        <th>{{$mapping}}</th>
                    @endforeach
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th>Probe</th>
                    @foreach($platform->mappingList() as $mapping)
                        <th>{{$mapping}}</th>
                    @endforeach
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            &nbsp;
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <span class="pull-right">
                <a href="{{ route('platforms-lists') }}" class="btn btn-info">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back
                </a>
            </span>
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
        var t = $('#data-table');
        t.find('tfoot th').each(function () {
            var f = $(this), title = f.text(), disable = f.data('disable-search');
            if (!disable) {
                f.html('<input type="text" class="form-control input-sm" placeholder="Search ' + title + '" />');
            }
        });
        var dataTable = t.dataTable({
            dom: "<'row'<'col-sm-3'l><'col-sm-6 text-center'B><'col-sm-3'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            scrollX: true,
            deferRender: true,
            responsive: false,
            processing: true,
            serverSide: false,
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5'
            ],
            ajax: {
                url: '{{ route('platforms-view-data', ['platform' => $platform]) }}',
                method: 'POST'
            },
            fixedColumns: {
                leftColumns: 1
            },
            columns: [
                {data: 'probe', name: 'probe'},
                /*<?php $i = 0; $mappings = $platform->mappingList(); $c = count($mappings) ?>
                @foreach($mappings as $id => $ignore)<?php $i++ ?>*/
                {data: '{{ $id }}', name: '{{ $id }}'}
                @if($i < $c),@endif
                /*@endforeach*/
            ],
            language: {
                processing: '<i class="fa fa-spinner faa-spin fa-3x fa-fw animated"></i><span class="sr-only">Loading...</span>'

            },
            initComplete: function () {
                this.api().columns().every(function () {
                    var column = this;
                    $(column.footer()).find('input').on('change', function () {
                        column.search($(this).val(), false, false, true).draw();
                    });
                });
            }
        });
    });
</script>
@endpush