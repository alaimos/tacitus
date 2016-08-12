@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Sample Selection
                <span class="pull-right">
                    <a href="{{ route('datasets-lists') }}" class="btn btn-info">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back
                    </a>
                    <a href="Javascript:;" class="btn btn-success btn-do-select">
                        <i class="fa fa-floppy-o" aria-hidden="true"></i> Submit selection
                    </a>
                </span>
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
            <form method="post" action="{{ route('queue-dataset-selection', ['dataset' => $dataset]) }}"
                  id="sample-selection-form">
                {!! csrf_field() !!}
                <div class="form-group">
                    <label for="selectionName" class="control-label">Name</label>
                    <input type="text" class="form-control" id="selectionName" name="selectionName"
                           placeholder="Specify a name for this selection. If no name is specified, one will be automatically generated.">
                </div>
                <div class="form-group">
                    <label for="datasets-table" class="control-label">Samples</label>
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-12 text-center" style="padding-bottom: 10px;">
                                <div class="btn-group" role="group" aria-label="Selection Toolbar">
                                    <button type="button" class="btn btn-default btn-select-all">Select All</button>
                                    <button type="button" class="btn btn-default btn-select-shown">Select Shown</button>
                                    <button type="button" class="btn btn-default btn-deselect-shown">Deselect Shown</button>
                                    <button type="button" class="btn btn-default btn-deselect-all">Deselect All</button>
                                </div>
                            </div>
                        </div>
                        <table class="table table-condensed table-hover table-striped no-wrap" id="datasets-table">
                            <thead>
                            <tr>
                                <th class="always-visible">#</th>
                                <th class="always-visible">Sample</th>
                                @foreach($dataset->metadataIndex as $metadata)
                                    <th>{{$metadata->name}}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th data-disable-search="1">#</th>
                                <th data-disable-search="1">Sample</th>
                                @foreach($dataset->metadataIndex as $metadata)
                                    <th>{{$metadata->name}}</th>
                                @endforeach
                            </tr>
                            </tfoot>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 text-center">
                                <div class="btn-group" role="group" aria-label="Selection Toolbar">
                                    <button type="button" class="btn btn-default btn-select-all">Select All</button>
                                    <button type="button" class="btn btn-default btn-select-shown">Select Shown</button>
                                    <button type="button" class="btn btn-default btn-deselect-shown">Deselect Shown</button>
                                    <button type="button" class="btn btn-default btn-deselect-all">Deselect All</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
                <a href="{{ route('datasets-lists') }}" class="btn btn-info">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back
                </a>&nbsp;&nbsp;
                <a href="Javascript:;" class="btn btn-success btn-do-select">
                    <i class="fa fa-floppy-o" aria-hidden="true"></i> Submit selection
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
        var form = $('#sample-selection-form');
        var t = $('#datasets-table');
        t.find('tfoot th').each(function () {
            var f = $(this), title = f.text(), disable = f.data('disable-search');
            if (!disable) {
                f.html('<input type="text" class="form-control input-sm" placeholder="Search ' + title + '" />');
            }
        });
        t.on('select.dt', function (e, dt, type, indexes) {
            if (type == 'row') {
                dt.rows(indexes).data().pluck('key').each(function (key) {
                    var cls = 'selection-' + key, field = 'input.' + cls;
                    if (form.find(field).size() == 0) {
                        form.append('<input type="hidden" name="samples[]" value="' + key + '" class="' + cls + '">');
                    }
                });
            }
        }).on('deselect.dt', function (e, dt, type, indexes) {
            if (type == 'row') {
                dt.rows(indexes).data().pluck('key').each(function (key) {
                    form.find('input.selection-' + key).remove()
                });
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
                url: '{{ route('datasets-lists-samples', ['dataset' => $dataset]) }}',
                method: 'POST'
            },
            fixedColumns: {
                leftColumns: 2
            },
            columns: [
                {data: 'id', name: 'id', searchable: false},
                {data: 'name', name: 'name'},
                /*<?php $i = 0; ?>@foreach($dataset->metadataIndex as $metadata)<?php $i++ ?>*/
                {data: '{{ snake_case($metadata->name) }}', name: '{{ snake_case($metadata->name) }}'}
                @if($i < $dataset->metadataIndex->count()),@endif
                /*@endforeach*/
            ],
            language: {
                processing: '<i class="fa fa-spinner faa-spin fa-3x fa-fw animated"></i><span class="sr-only">Loading...</span>'

            },
            select: {
                style: 'multi',
                info: true,
                items: 'row'
            },
            initComplete: function () {
                this.api().columns().every(function () {
                    var column = this;
                    $(column.footer()).find('input').on('change', function () {
                        column.search($(this).val(), false, false, true).draw();
                    });
                });
            },
            rowCallback: function (row, data, index) {
                $(row).data('key', data.key);
            }
        });
        $('.btn-select-all').on('click', function() {
            if (dataTable.api().rows({"filter":"applied"}).count()) {
                dataTable.api().rows({"filter":"applied"}).select();
            } else {
                dataTable.api().rows().select();
            }
        });
        $('.btn-deselect-all').on('click', function() {
            if (dataTable.api().rows({"filter":"applied"}).count()) {
                dataTable.api().rows({"filter":"applied"}).deselect();
            } else {
                dataTable.api().rows().deselect();
            }
        });
        $('.btn-select-shown').on('click', function() {
            dataTable.api().rows('tr').select();
        });
        $('.btn-deselect-shown').on('click', function() {
            dataTable.api().rows('tr').deselect();
        });
        $('.btn-do-select').on('click', function() {
            form.submit();
        })
    });
</script>
@endpush