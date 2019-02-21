@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Tutorial
            </h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
        <div class="col-md-7">
            <p>TACITuS (Transcriptomic Data Collector, Integrator, and Selector) is a web portal that simplifies the process of collection, pre-processing,
                selection, and integration of transcriptomics data. Therefore, users can collect data from major sources, such as NCBI GEO or ArrayExpress,
                and integrate them with their own data into a standardized format, facilitating subsequent analyses. Our software is freely available and
                distributed through a GPL v3 licence. TACITuS implements five major functionalities: (i) data import, (ii) data selection, (iii) identifier
                mapping, (iv) data integration, and (v) Galaxy Export, which are shown later.</p>

            <a name="import"></a>
            <h2>Importing data</h2>

            <p>
                Data can be imported through the <strong>Dataset</strong> panel. There the user will be able to select a data source (NCBI GEO, ArrayExpress,
                or custom). When a public source is selected (NCBI GEO or ArrayExpress) the accession number of the dataset he wishes to import has to be
                provided. For a custom dataset the user must also provide a name, a metadata file and the expression file. Metadata file should be uploaded as
                a tab-separated value file where each row corresponds to a sample and each column is a covariate. Headers for each covariate should be provided.
                Sample identifiers have to be positioned in the first column of the file. Expression file should be formatted as a tab-separated value file
                where
                each row corresponds to a probe and each column to a sample. Probe identifiers have to be positioned in the first column of the file, and
                Sample identifiers need to be positioned in the first row.
            </p>
            <p>
                As soon as all required data are provided the user can decide whether the imported dataset should be public or not and submit the request.
                Then, a record is added in our processing queue and a progress log is displayed in the <strong>Jobs</strong> panel.
            </p>

            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/O6idyFO_Sms?rel=0" frameborder="0"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>

            <a name="select"></a>
            <h2>Selecting data</h2>

            <p>
                For each dataset listed in the <strong>Dataset</strong> panel, the user can press
                <a href="Javascript:;" class="btn btn-xs btn-primary"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Make Selection</a>
                to start the selection process. A panel will appear where the user is required to specify the selection name and which samples should be
                included.
                Samples can be selected through the metadata table where all covariates for each sample are displayed. On the bottom of the table a search field
                for each covariate is provided. Samples can be selected by clicking on a row in the table. Bulk selection and deselection of samples is also
                provied.
                Select All/Deselect All allows selection/deselection of all samples in all pages of the displayed table. Select Shown/Deselect Shown
                allows selection/deselection of all samples in the displayed page of the table.
            </p>

            <p>
                By pressing <a href="Javascript:;" class="btn btn-success"><i class="fa fa-floppy-o" aria-hidden="true"></i> Submit selection</a>
                the user submits his request. Once sent, a record is added in our processing queue and a progress log
                is displayed in the <strong>Jobs</strong> panel. Results can be downloaded in TSV or CSV format.
            </p>

            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/LRa0fkeEb6Y?rel=0" frameborder="0"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>

            <a name="map"></a>
            <h2>Mapping datasets</h2>

            <p>
                To facilitate the data integration process with other platforms, TACITuS supports mapping from probe identifiers to standardized one
                (e.g Entrez Id or ENSEMBL Id) for each selection. The functionality is activated through the <strong>Selections</strong> panel by clicking on
                <a href="Javascript:;" class="btn btn-xs btn-primary"><i class="fa fa-globe" aria-hidden="true"></i> Map Identifiers</a>.
                Once the <strong>Map Identifiers</strong> panel is open, the user will be able to select a platform (the field is automatically detected for
                NCBI GEO dataset) and a destination identifier. Destination identifiers depends on the specific experimental platform. By pressing
                <a href="Javascript:;" class="btn btn-success"><i class="fa fa-floppy-o"></i> Submit</a>, the user submits his request. Once sent, a record is
                added in our processing queue and a progress log is displayed in the <strong>Jobs</strong> panel.
                At the end, the user may download the mapped data in TSV or CSV format.
            </p>

            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/8QSTQyJZg9o?rel=0" frameborder="0"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>

            <a name="integration"></a>
            <h2>Integrating datasets</h2>

            <p>
                TACITuS implements several integration procedures which combines two or more selections into a single dataset. The system exploits the
                following techniques:</p>
            <ul>
                <li><strong>Sims et al. 2008</strong> transforms each dataset through mean-centering applying a technique like z-score normalization.</li>
                <li><strong>COMBAT</strong> exploits an Empirical Bayesian model to estimate the mean and variance of each gene in each dataset, correcting data
                    for batch effects.
                </li>
                <li><strong>Gene Standardization</strong> is the simplest mathematical transformation to make datasets comparable. For each gene, the expression
                    value is corrected by subtracting the mean and dividing by the standard deviation.
                </li>
                <li><strong>XPN</strong> finds blocks of genes and samples showing similar expression patterns. The average of these blocks will be used to
                    shift and scale the data.
                </li>
            </ul>

            <p>
                The Integration procedure is activated by clicking on <a href="Javascript:;" class="btn btn-success">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i> Request integration</a> in the <strong>Integrator</strong> panel. The user can specify
                which datasets to integrate, the combination method, and a supplementary identifier mapping. Integration algorithms can also be disabled if the
                user needs to combine expression matrices without altering the values. Once the request has been completed, a job is added to our jobs queue,
                and processed as soon as the resources are available. The results can be downloaded from the <strong>Integrator</strong> panel both in TSV or
                CSV format.
            </p>

            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/Y0cI3eS4cm4?rel=0" frameborder="0"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>

            <a name="upload"></a>
            <h2>Uploading to Galaxy</h2>

            <p>
                TACITuS is equipped with a module for uploading data and metadata to the Galaxy platform. First, the user should register to a galaxy server.
                Then, he provides the credential in the <strong>Galaxy Account</strong> panel (name, host name, port, and API key). As soon as these data are
                entered the upload module will be enabled.</p>

            <p>
                To upload a dataset, the user has to click on <a href="Javascript:;" class="btn btn-xs btn-primary">
                    <i class="fa fa-upload" aria-hidden="true"></i> Upload to Galaxy</a> present in each panel of the website (Selections, Mapped Selections,
                Integrations). A page will open and therefore the user can select a galaxy server. By clicking on <a href="Javascript:;" class="btn btn-info">
                    <i class="fa fa-upload"></i> Upload</a> a background upload job will start, and the user will be notified as soon as the process is
                completed.
            </p>

            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/UU3Al4X8htQ?rel=0" frameborder="0"
                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
        <div class="col-md-5">
            <ul class="nav nav-pills nav-stacked">
                <li role="presentation"><a href="#import">Importing data</a></li>
                <li role="presentation"><a href="#select">Selecting data</a></li>
                <li role="presentation"><a href="#map">Mapping datasets</a></li>
                <li role="presentation"><a href="#integration">Integrating datasets</a></li>
                <li role="presentation"><a href="#upload">Uploading to Galaxy</a></li>
            </ul>
        </div>
    </div>
@endsection
