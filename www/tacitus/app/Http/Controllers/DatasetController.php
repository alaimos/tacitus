<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Http\Controllers;

use App\Dataset\Registry\ParserFactoryRegistry;
use App\Jobs\Factory as JobFactory;
use App\Models\Dataset;
use App\Models\Platform;
use App\Models\Source;
use App\Utils\Permissions;
use App\Models\Job as JobData;
use Auth;
use Carbon\Carbon;
use Datatables;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class DatasetController extends Controller
{

    /**
     * Registers routes handled by this controller
     *
     * @param \Illuminate\Routing\Router $router
     */
    public static function registerRoutes(Router $router)
    {
        $router->get('/datasets', ['as' => 'datasets-lists', 'uses' => 'DatasetController@datasetsList']);
        $router->any('/datasets/data', ['as' => 'datasets-lists-data', 'uses' => 'DatasetController@datasetsData']);
        $router->get('/datasets/{dataset}/selection', ['as'   => 'datasets-select',
                                                       'uses' => 'DatasetController@sampleSelection']);
        $router->any('/datasets/{dataset}/selection/data', ['as'   => 'datasets-lists-samples',
                                                            'uses' => 'DatasetController@sampleSelectionData']);
        $router->post('/datasets/{dataset}/selection', ['as'   => 'queue-dataset-selection',
                                                        'uses' => 'DatasetController@queueSampleSelection']);
        $router->get('/datasets/{dataset}/delete', ['as'         => 'datasets-delete',
                                                    'uses'       => 'DatasetController@delete',
                                                    'middleware' => ['permission:' . Permissions::DELETE_DATASETS]]);

        $router->get('/datasets/submission', ['as'         => 'datasets-submission',
                                              'uses'       => 'DatasetController@submission',
                                              'middleware' => ['permission:' . Permissions::SUBMIT_DATASETS]]);

        $router->post('/datasets/submission', ['as'         => 'datasets-submission-process',
                                               'uses'       => 'DatasetController@processSubmission',
                                               'middleware' => ['permission:' . Permissions::SUBMIT_DATASETS]]);
    }

    /**
     * Prepare the list of datasets
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function datasetsList()
    {
        if (!user_can(Permissions::VIEW_DATASETS)) {
            abort(403);
        }
        return view('datasets.list');
    }

    /**
     * Process datatables ajax request for the list of datasets.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function datasetsData()
    {
        if (!user_can(Permissions::VIEW_DATASETS)) {
            abort(403);
        }
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(Dataset::listDatasets());
        $table->addColumn('action', function (Dataset $dataset) {
            return view('datasets.list_action_column', [
                'dataset' => $dataset
            ])->render();
        });
        return $table->make(true);
    }

    /**
     * Import dataset submission form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function submission()
    {
        new ParserFactoryRegistry(); //Init Parser Factory Registry to setup all sources
        return view('datasets.submissionForm', [
            'sources' => Source::all()->pluck('display_name', 'name'),
        ]);
    }

    /**
     * Process dataset submission
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processSubmission(Request $request)
    {
        $this->validate($request, [
            'source_type' => 'required|exists:sources,name',
            'accession'   => 'required|max:255',
            'private'     => 'sometimes|boolean',
        ]);
        try {
            $jobData = new JobData([
                'job_type' => 'import_dataset',
                'status'   => JobData::QUEUED,
                'job_data' => [
                    'original_id' => $request->get('accession'),
                    'source_type' => $request->get('source_type'),
                    'private'     => boolval($request->get('private', false)),
                ],
                'log'      => ''
            ]);
            $jobData->user()->associate(Auth::user());
            $jobData->save();
            $job = JobFactory::getQueueJob($jobData);
            $this->dispatch($job);
            Flash::success('Your request has been submitted. Please check the Jobs panel in order to verify its status.');
        } catch (\Exception $e) {
            Flash::error('Error occurred while submitting job: ' . $e->getMessage());
        }
        return redirect()->route('datasets-lists');
    }

    /**
     * Shows sample selection form
     *
     * @param Request $request
     * @param Dataset $dataset
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sampleSelection(Request $request, Dataset $dataset)
    {
        if (!$dataset || !$dataset->exists) {
            abort(404, 'Unable to find the dataset.');
        }
        if (!$dataset->canSelect()) {
            abort(401, 'You are not allowed to use this dataset');
        }
        return view('datasets.samples.selection', [
            'dataset' => $dataset,
        ]);

    }

    /**
     * Process datatables ajax request for the list of samples
     *
     * @param Request $request
     * @param Dataset $dataset
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sampleSelectionData(Request $request, Dataset $dataset)
    {
        if (!$dataset || !$dataset->exists) {
            abort(404, 'Unable to find the dataset.');
        }
        if (!$dataset->canSelect()) {
            abort(401, 'You are not allowed to use this dataset');
        }
        /** @var \Yajra\Datatables\Engines\CollectionEngine $table */
        $table = Datatables::of($dataset->getMetadataSamplesCollection());
        return $table->make(true);
    }

    /**
     * Prepare and queue sample selection
     *
     * @param Request $request
     * @param Dataset $dataset
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function queueSampleSelection(Request $request, Dataset $dataset)
    {
        if (!$dataset || !$dataset->exists) {
            abort(404, 'Unable to find the dataset.');
        }
        if (!$dataset->canSelect()) {
            abort(401, 'You are not allowed to use this dataset');
        }
        $selectionName = $request->get('selectionName');
        if (empty($selectionName)) {
            $selectionName = 'Selection from ' . $dataset->source->display_name . ' dataset ' .
                $dataset->original_id . ' on ' . Carbon::now()->toDateTimeString();
        }
        $samples = $request->get('samples');
        if (empty($samples)) {
            Flash::error('You must select at least one sample.');
            return redirect()->back();
        }
        if (!is_array($samples)) {
            $samples = (array)$samples;
        }
        $jobData = new JobData([
            'job_type' => 'dataset_selection',
            'status'   => JobData::QUEUED,
            'job_data' => [
                'dataset_id'    => $dataset->id,
                'selectionName' => $selectionName,
                'samples'       => $samples
            ],
            'log'      => ''
        ]);
        $jobData->user()->associate(Auth::user());
        $jobData->save();
        $job = JobFactory::getQueueJob($jobData);
        $this->dispatch($job);
        Flash::success('Your selection request has been submitted. Please check the Jobs panel in order to verify ' .
            'its status.');
        return redirect()->route('datasets-lists');
    }

    /**
     * Delete a dataset
     *
     * @param Dataset $dataset
     *
     * @return mixed
     */
    public function delete(Dataset $dataset)
    {
        if (!user_can(Permissions::DELETE_DATASETS)) {
            abort(403);
        }
        if (!$dataset || !$dataset->exists) {
            abort(404, 'Unable to find the dataset.');
        }
        if (!$dataset->canDelete()) {
            abort(401, 'You are not allowed to delete this dataset.');
        }
        $dataset->delete();
        Flash::success('Your deletion request has been submitted. Please check the Jobs panel in order to verify its ' .
            'status.');
        return back();
    }
}
