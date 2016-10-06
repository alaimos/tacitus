@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Map identifiers for {{ $selection->name }}
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
        <div class="col-md-offset-2 col-md-8">
            <div class="panel panel-default">
                <div class="panel-body">
                    {!! Form::open(['class' => 'form-horizontal', 'method' => 'POST', 'route' => ['mapped-selections-do-submit', $selection]]) !!}
                    <div class="form-group{{ $errors->has('platform') ? ' has-error' : '' }}">
                        {!! Form::label('platform', 'Platform', ['class' => 'col-md-3 control-label']) !!}
                        <div class="col-md-9">
                            <select name="platform" id="platform" class="form-control platform-select">
                                @if($selection->platform !== null)
                                    <option value="{{ $selection->platform->id }}"
                                            selected="selected">{{ $selection->platform->title }}</option>
                                @endif
                            </select>
                            <span class="help-block">
                                Choose the type of platform.
                            </span>
                            @if ($errors->has('platform'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('platform') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-offset-3 col-md-9">
                            {!! Form::button('<i class="fa fa-floppy-o"></i> Submit', ['type' => 'submit', 'class' => 'btn btn-success']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(function () {
        $('.platform-select').select2({
            ajax: {
                url: "{{ route('platforms-list-json') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * 30) < data.total
                        }
                    }
                },
                cache: true
            },
            escapeMarkup: function (markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 1,
            templateResult: function (repo) {
                if (repo.loading) return repo.text;

                var markup = "<div class='select2-result-repository clearfix'>" +
                        "<div class='select2-result-repository__avatar'><img src='" + repo.owner.avatar_url + "' /></div>" +
                        "<div class='select2-result-repository__meta'>" +
                        "<div class='select2-result-repository__title'>" + repo.full_name + "</div>";

                if (repo.description) {
                    markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
                }

                markup += "<div class='select2-result-repository__statistics'>" +
                        "<div class='select2-result-repository__forks'><i class='fa fa-flash'></i> " + repo.forks_count + " Forks</div>" +
                        "<div class='select2-result-repository__stargazers'><i class='fa fa-star'></i> " + repo.stargazers_count + " Stars</div>" +
                        "<div class='select2-result-repository__watchers'><i class='fa fa-eye'></i> " + repo.watchers_count + " Watchers</div>" +
                        "</div>" +
                        "</div></div>";

                return markup;
            }, // omitted for brevity, see the source of this page
            templateSelection: function (selection) {
                return selection.title || selection.text;
            }
        });
    });
    /*$(function () {
     var container = $('.import-form-content');
     $('.importer-select').change(function () {
     container.html('<div class="text-center"><i class="fa fa-spinner faa-spin fa-3x fa-fw ' +
     'animated"></i><span class="sr-only">Loading...</span></div>');
     var importerType = $(this).val();
     $.ajax({
     dataType: 'json',
     method: 'POST',
     url: '{{ route('platforms-submission-form') }}',
     data: {
     'importer_type': importerType
     },
     success: function (data) {
     if (data.ok) {
     container.html(data.content);
     } else {
     container.html('');
     alert(data.content);
     }
     },
     error: function () {
     container.html('');
     alert('Unknown error');
     }
     });
     }).change();
     });*/
</script>
@endpush