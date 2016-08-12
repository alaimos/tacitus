<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Http\Controllers;

use App\Models\Job as JobData;
use Auth;
use Datatables;
use Illuminate\Http\Request;

class JobsController extends Controller
{

    /**
     * Prepare the list of jobs
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function jobsList(Request $request)
    {
        return view('jobs.list');
    }

    /**
     * Process datatables ajax request for the list of jobs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function jobsData(Request $request)
    {
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(JobData::listJobs());
        $table->editColumn('job_type', function (JobData $jobData) {
            return ucwords(str_replace(['-', '_'], ' ', $jobData->job_type));
        })->editColumn('status', function (JobData $jobData) {
            $text = '';
            switch ($jobData->status) {
                case JobData::QUEUED:
                    $text = '<i class="fa fa-pause" aria-hidden="true"></i> ';
                    break;
                case JobData::PROCESSING:
                    $text = '<i class="fa fa-spinner faa-spin animated" aria-hidden="true"></i> ';
                    break;
                case JobData::FAILED:
                    $text = '<i class="fa fa-exclamation-circle" aria-hidden="true"></i> ';
                    break;
                case JobData::COMPLETED:
                    $text = '<i class="fa fa-check-circle" aria-hidden="true"></i> ';
                    break;
            }
            return $text . ucfirst($jobData->status);
        })->addColumn('view', function (JobData $jobData) {
            if ($jobData->status == JobData::QUEUED) {
                return '';
            }
            return view('jobs.list_action_column', [
                'jobData' => $jobData
            ])->render();
        });
        return $table->make(true);
    }

    /**
     * Return a job data
     *
     * @param Request $request
     * @param JobData $job
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewJob(Request $request, JobData $job)
    {
        return response()->json($job->toArray());
    }
}
