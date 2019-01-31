@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Integrator
                <span class="pull-right">
                    <a href="{{ route('integration-submit') }}" class="btn btn-success">
                        <i class="fa fa-plus-circle" aria-hidden="true"></i> Request integration
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
            <table class="table table-condensed table-responsive table-hover table-striped no-wrap"
                   id="integrations-table">
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
            <span class="pull-right">
                <a href="{{ route('integration-submit') }}" class="btn btn-success">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i> Request integration
                </a>
            </span>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="modal fade" id="confirm-modal" tabindex="-1" role="dialog" aria-labelledby="confirm-modal-label">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="confirm-modal-label">Change file format</h4>
                        </div>
                        <div class="modal-body">
                            <p>TACITuS uses tab-separated files.<br>&nbsp;<br><b>Do you wish to change the field separator before the download?</b></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default btn-confirm-modal-no">No</button>
                            <button type="button" class="btn btn-primary btn-confirm-modal-yes">Yes</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="separator-modal" tabindex="-1" role="dialog" aria-labelledby="separator-modal-label">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="separator-modal-label">Choose field separator</h4>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="form-group">
                                    <input type="text" class="form-control txt-separator-modal-separator" style="font-weight: bold; font-size: 16px;" value=","
                                           maxlength="1">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary btn-separator-modal-download">Download</button>
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
        var t = $('#integrations-table'), downloadUrl = null;
        var confirmModal = $('#confirm-modal'), separatorModal = $('#separator-modal');
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
                url: '{{ route('integrations-lists-data') }}',
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
        confirmModal.find('.btn-confirm-modal-no').click(function () {
            confirmModal.modal('hide');
            location.href = downloadUrl;
        });
        confirmModal.find('.btn-confirm-modal-yes').click(function () {
            confirmModal.modal('hide');
            separatorModal.modal('show');
        });
        separatorModal.find('.btn-separator-modal-download').click(function () {
            var sep = separatorModal.find('.txt-separator-modal-separator').val();
            separatorModal.modal('hide');
            location.href = downloadUrl + '?new-separator=' + encodeURI(sep);
        });
    });
</script>
@endpush