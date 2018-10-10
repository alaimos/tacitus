@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Upload to Galaxy platform
                <span class="pull-right">
                    <a href="{{ route('selections-lists') }}" class="btn btn-info">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back
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
    <div class="col-lg-8 col-md-offset-2">
        <div class="panel panel-info">
            <a name="galaxy_table"></a>
            <div class="panel-heading">
                <i class="fa fa-folder-open"></i>
                Data setting for uploading
            </div>
            <div class="panel-body">
                <!-- /.row -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <h4><b><i>history id:</i></b></h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h5> &emsp;&ensp; {{ $selection->name }} </h5>
                            </div>
                        </div><br>
                        <div class="row">
                            <div class="col-md-12">
                                <h4><b><i>galaxy accounts:</i></b></h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-condensed table-responsive table-hover table-striped no-wrap"
                                       id="galaxy-select-table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Hostname</th>
                                        <th>Port</th>
                                        <th>Uses https</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="upl-foot" class="panel-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <a id="sel-galx-butt" href="#" role="button"
                           class="btn btn-info disabled">
                           <i class="fa fa-upload"></i> Upload
                        </a>
                    </div>
                </div>
            </div>
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
        var simple_checkbox = function (data, type, full, meta) {
            var is_checked = data == true ? "checked" : "";
            return '<input type="checkbox" class="checkbox" ' + is_checked + ' disabled/>';
        };
        $(function () {
            var t = $('#galaxy-select-table').dataTable({
                pageLength: 5,
                /*lengthMenu: [5, 10, 25, 50, 100],*/
                responsive: true,
                processing: true,
                serverSide: true,
                searching : false,
                info:       false,
                lengthChange:false,
                ajax: {
                    url: '{{ route('galaxyCredential-selection')}}',
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
                        "className": "text-center"
                    }
                ],
                columns: [
                    {data: 'DT_Row_Index', name: 'index'},
                    {data: 'name', name: 'name'},
                    {data: 'hostname', name: 'hostname'},
                    {data: 'port', name: 'port'},
                    {data: 'use_https', render: simple_checkbox}
                ],
                language: {
                    processing: '<i class="fa fa-spinner faa-spin fa-3x fa-fw animated"></i><span class="sr-only">Loading...</span>'

                }
            });

            $('#galaxy-select-table').on( 'click', 'tr', function () {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                    $('#sel-galx-butt').addClass('disabled');
                    console.log('if');
                }
                else {
                    t.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');
                    $('#sel-galx-butt').removeClass('disabled');
                }
            });

        });
    </script>
@endpush
