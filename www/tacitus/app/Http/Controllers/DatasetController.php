<?php

namespace App\Http\Controllers;

use App\Jobs\Factory as JobFactory;
use App\Models\Dataset;
use App\Models\Source;
use App\Utils\Permissions;
use App\Models\Job as JobData;
use Auth;
use Datatables;
use Flash;
use Illuminate\Http\Request;

class DatasetController extends Controller
{

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
        return view('datasets.submissionForm', [
            'sources' => Source::all()->pluck('display_name', 'name'),
        ]);
    }

    /**
     * Process dataset submission
     *
     * @param Request $request
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
                ]
            ]);
            $jobData->user()->associate(Auth::user());
            $jobData->save();
            $job = JobFactory::getQueueJob($jobData);
            $this->dispatch($job);
            Flash::success('Job successfully submitted. Please check the Jobs panel in order to check the ' .
                           'status of your request.');
        } catch (\Exception $e) {
            Flash::error('Error occurred while submitting job: ' . $e->getMessage());
        }
        return redirect()->route('datasets-lists');
    }

}
