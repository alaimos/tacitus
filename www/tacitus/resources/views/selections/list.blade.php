@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Selections
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
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="modal fade" id="confirm-modal" tabindex="-1" role="dialog" aria-labelledby="confirm-modal-label">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="confirm-modal-label">Choose file format</h4>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary btn-lg btn-block btn-confirm-modal-tsv">TSV</button>
                                    <button type="button" class="btn btn-success btn-lg btn-block btn-confirm-modal-csv">CSV</button>
                                </div>
                                <fieldset class="display-on-csv">
                                    <legend>Choose separator and click &quot;Download&quot; to continue</legend>
                                    <div class="form-group">
                                        <label for="txt-confirm-modal-separator">Separator character</label>
                                        <input type="text" class="form-control txt-confirm-modal-separator"
                                               id="txt-confirm-modal-separator"
                                               style="font-weight: bold; font-size: 16px;" value=","
                                               maxlength="1">
                                    </div>
                                    <div class="text-center">
                                        <button type="button" class="btn btn-primary btn-confirm-modal-download-csv">
                                            <i class="fa fa-fw fa-download"></i> Download
                                        </button>
                                    </div>
                                </fieldset>
                            </form>
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
        $(function () {
            var t = $('#selections-table'), downloadUrl = null;
            var confirmModal = $('#confirm-modal'), dCSV = $('.display-on-csv');
            dCSV.hide();
            t.dataTable({
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
                    url: '{{ route('selections-lists-data') }}',
                    method: 'POST'
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                language: {
                    processing: '<i class="fa fa-spinner faa-spin fa-3x fa-fw animated"></i><span class="sr-only">Loading...</span>'
                }
            });
            t.on('click', '.download-button', function (e) {
                downloadUrl = $(this).attr('href');
                confirmModal.modal('show');
                e.stopPropagation();
                e.preventDefault();
            });
            confirmModal.find('.btn-confirm-modal-tsv').click(function (e) {
                e.stopPropagation();
                e.preventDefault();
                confirmModal.modal('hide');
                location.href = downloadUrl;
            });
            confirmModal.find('.btn-confirm-modal-csv').click(function (e) {
                e.stopPropagation();
                e.preventDefault();
                dCSV.slideDown();
            });
            confirmModal.find('.btn-confirm-modal-download-csv').click(function (e) {
                e.stopPropagation();
                e.preventDefault();
                var sep = confirmModal.find('.txt-confirm-modal-separator').val();
                dCSV.hide();
                confirmModal.modal('hide');
                location.href = downloadUrl + '?new-separator=' + encodeURI(sep);
            });
        });
    </script>
@endpush