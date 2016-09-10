@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">TACITuS
                <span class="hidden-md"> - Transcriptomic Data Collector, Integrator, and Selector</span></h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
        <div class="col-md-9">
            <p class="text-justify">
                TACITuS is a portal, which deals with data pre-processing, selection and, eventually, integration of
                transcriptomic data coming from diverse sources, such as ArrayExpress.
                In order to take advantage of the functionality provided by this application, you will need to register,
                or sign in with a previously activated account.
            </p>
            <p class="text-justify">
                Users have the opportunity to submit new datasets to be analyzed. The system will automatically
                determine the format according to the source, and an automatic parser will make the dataset
                compatible for further analysis.
            </p>
            <p class="text-justify">
                The system is currently in development. Therefore, due to the current shortage of storage space, all
                datasets are saved in the database for <b>365</b> days. All analysis results will be available for <b>a
                week</b> after the completion of each job.
            </p>
        </div>
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart fa-fw" aria-hidden="true"></i> Some statistics
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <span class="badge">{{ $totalUsers }}</span>
                        <i class="fa fa-users" aria-hidden="true"></i>
                        Users
                    </li>
                    <li class="list-group-item">
                        <span class="badge">{{ $totalDatasets }}</span>
                        <i class="fa fa-database" aria-hidden="true"></i>
                        Datasets
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <i class="fa fa-newspaper-o fa-fw"></i> Release Notes
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <b>Version 0.1</b>: Base application structure is now ready. Dataset importer works only with
                        ArrayExpress MTAB datasets. Data Integrator and Id Mapper implementation is still a work in
                        progress.
                    </li>
                </ul>
            </div>
        </div>
    </div>

@endsection
