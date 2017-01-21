<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Http\Controllers;

use App\Jobs\Factory as JobFactory;
use App\Models\Job as JobData;
use App\Models\MappedSampleSelection;
use App\Models\SampleSelection;
use App\Utils\Permissions;
use Auth;
use Datatables;
use Flash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class MappedSelectionController extends Controller
{

    /**
     * Registers routes handled by this controller
     *
     * @param \Illuminate\Routing\Router $router
     */
    public static function registerRoutes(Router $router)
    {
        $router->get('/selections/{selection}/map',
            ['as' => 'mapped-selections-submit', 'uses' => 'MappedSelectionController@submitMappingForm']);
        $router->post('/selections/{selection}/map',
            ['as' => 'mapped-selections-do-submit', 'uses' => 'MappedSelectionController@submitMapping']);
        $router->get('/selections/mapped',
            ['as' => 'mapped-selections-lists', 'uses' => 'MappedSelectionController@selectionsList']);
        $router->any('/selections/mapped/data',
            ['as' => 'mapped-selections-lists-data', 'uses' => 'MappedSelectionController@selectionsData']);
        $router->any('/selections/mapped/list',
            ['as' => 'mapped-selections-lists-json', 'uses' => 'MappedSelectionController@listSelectionsJson']);
        $router->get('/selections/mapped/{selection}/download/{type}',
            ['as' => 'mapped-selections-download', 'uses' => 'MappedSelectionController@download']);
        $router->get('/selections/mapped/{selection}/delete',
            ['as' => 'mapped-selections-delete', 'uses' => 'MappedSelectionController@delete']);

    }

    /**
     * @param Request         $request
     * @param SampleSelection $selection
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function submitMappingForm(Request $request, SampleSelection $selection)
    {
        if (!user_can(Permissions::USE_TOOLS)) {
            abort(403);
        }
        if (!$selection || !$selection->exists) {
            abort(404, 'Unable to find the selection.');
        }
        if (!$selection->canDownload()) {
            abort(401, 'You are not allowed to use this selection.');
        }
        return view('selections.mapped.submissionForm', [
            'selection' => $selection,
        ]);
    }

    public function submitMapping(Request $request, SampleSelection $selection)
    {
        if (!user_can(Permissions::USE_TOOLS)) {
            abort(403);
        }
        if (!$selection || !$selection->exists) {
            abort(404, 'Unable to find the selection.');
        }
        if (!$selection->canDownload()) {
            abort(401, 'You are not allowed to use this selection.');
        }
        $this->validate($request, [
            'platform' => 'required|exists:platforms,id',
            'mapping'  => 'required|exists:platform_mappings,id',
        ]);
        try {
            $jobData = new JobData([
                'job_type' => 'map_dataset_selection',
                'status'   => JobData::QUEUED,
                'job_data' => [
                    'selection' => $selection->id,
                    'platform'  => $request->get('platform'),
                    'mapping'   => $request->get('mapping'),
                ],
                'log'      => '',
            ]);
            $jobData->user()->associate(Auth::user());
            $jobData->save();
            $job = JobFactory::getQueueJob($jobData);
            $this->dispatch($job);
            Flash::success('Your request has been submitted. Please check the Jobs panel in order to verify its status.');
        } catch (\Exception $e) {
            Flash::error('Error occurred while submitting job: ' . $e->getMessage());
        }
        return redirect()->route('mapped-selections-lists');
    }

    /**
     * Prepare the list of selections
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function selectionsList()
    {
        if (!user_can(Permissions::USE_TOOLS)) {
            abort(403);
        }
        return view('selections.mapped.list');
    }

    /**
     * Lists all mapped selections in json format
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listSelectionsJson(Request $request)
    {
        $q = $request->get('q');
        $perPage = (int)$request->get('perPage', 30);
        $query = MappedSampleSelection::listSelections();
        if (!empty($q)) {
            $query->where(function (Builder $query) use ($q) {
                $query->where('sample_selections.name', 'like', '%' . $q . '%')
                      ->orWhere('platforms.title', 'like', '%' . $q . '%')
                      ->orWhere('platforms.organism', 'like', '%' . $q . '%')
                      ->orWhere('platform_mappings.name', 'like', '%' . $q . '%');
            });
        }
        return $query->paginate($perPage, ['id', 'name', 'platform', 'mapping', 'organism']);
    }

    /**
     * Process datatables ajax request for the list of selections.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectionsData()
    {
        if (!user_can(Permissions::USE_TOOLS)) {
            abort(403);
        }
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(MappedSampleSelection::listSelections());
        $table->addColumn('action', function (MappedSampleSelection $mappedSelection) {
            return view('selections.mapped.list_action_column', [
                'mappedSelection' => $mappedSelection,
            ])->render();
        });
        return $table->make(true);
    }

    /**
     * Download a file from a mapped selection
     *
     * @param MappedSampleSelection $selection
     * @param string                $type
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(MappedSampleSelection $selection, $type)
    {
        if (!user_can(Permissions::DOWNLOAD_SELECTIONS)) {
            abort(403);
        }
        if (!$selection || !$selection->exists) {
            abort(404, 'Unable to find the selection.');
        }
        if (!$selection->canDownload()) {
            abort(401, 'You are not allowed to download this selection.');
        }
        $fileName = null;
        switch (strtolower($type)) {
            case 'metadata':
                $fileName = $selection->getMetadataFilename();
                break;
            case 'data':
                $fileName = $selection->getDataFilename();
                break;
            default:
                abort(500, 'Invalid type specified.');
                break;
        }
        return response()->download($fileName, basename($fileName), [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /**
     * Delete a mapped selection
     *
     * @param MappedSampleSelection $selection
     *
     * @return mixed
     */
    public function delete(MappedSampleSelection $selection)
    {
        if (!user_can(Permissions::REMOVE_SELECTIONS)) {
            abort(403);
        }
        if (!$selection || !$selection->exists) {
            abort(404, 'Unable to find the selection.');
        }
        if (!$selection->canDelete()) {
            abort(401, 'You are not allowed to delete this selection.');
        }
        $selection->delete();
        Flash::success('Selection deleted successfully.');
        return back();
    }
}
