<?php

namespace App\Http\Controllers;

use App\Http\Response\ConvertFileResponse;
use App\Jobs\Factory as JobFactory;
use App\Models\GalaxyCredential;
use App\Models\Job as JobData;
use App\Models\SampleSelection;
use App\Utils\Permissions;
use Datatables;
use Flash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;

class SelectionController extends Controller
{

    /**
     * Registers routes handled by this controller
     *
     * @param \Illuminate\Routing\Router $router
     */
    public static function registerRoutes(Router $router)
    {
        $router->get('/selections',
                     ['as' => 'selections-lists', 'uses' => 'SelectionController@selectionsList']);
        $router->any('/selections/data',
                     ['as' => 'selections-lists-data', 'uses' => 'SelectionController@selectionsData']);
        $router->any('/selections/list',
                     ['as' => 'selections-lists-json', 'uses' => 'SelectionController@listSelectionsJson']);
        $router->get('/selections/{selection}/download/{type}',
                     ['as' => 'selections-download', 'uses' => 'SelectionController@download']);
        $router->get('/selections/{selection}/delete',
                     ['as' => 'selections-delete', 'uses' => 'SelectionController@delete']);

        $router->get('/selections/{selection}/upload',
                     ['as' => 'selection-upload', 'uses' => 'SelectionController@upload']);
        $router->post('/selections/{selection}/upload',
                      ['as' => 'selection-do-upload', 'uses' => 'SelectionController@doUpload']);
        $router->post('/selections/galaxyCredentials',
                      ['as' => 'galaxyCredential-selection', 'uses' => 'SelectionController@listGalaxyCredential']);
    }

    /**
     * Prepare the list of selections
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function selectionsList()
    {
        if (!user_can(Permissions::VIEW_SELECTIONS)) {
            abort(403);
        }
        return view('selections.list');
    }

    /**
     * Process datatables ajax request for the list of selections.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectionsData()
    {
        if (!user_can(Permissions::VIEW_SELECTIONS)) {
            abort(403);
        }
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(SampleSelection::listSelections());
        $table->addColumn('action', function (SampleSelection $selection) {
            return view('selections.list_action_column', [
                'selection' => $selection,
            ])->render();
        });
        return $table->make(true);
    }

    /**
     * Lists all selections in json format
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listSelectionsJson(Request $request)
    {
        $q       = $request->get('q');
        $perPage = (int)$request->get('perPage', 30);
        $query   = SampleSelection::listSelections();
        if (!empty($q)) {
            $query->where(function (Builder $query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%');
            });
        }
        return $query->paginate($perPage, ['id', 'name']);
    }

    /**
     * Download a file from a selection
     *
     * @param \Illuminate\Http\Request $request
     * @param SampleSelection          $selection
     * @param string                   $type
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Request $request, SampleSelection $selection, $type)
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
        $newSeparator = $request->get('new-separator');
        if (!empty($newSeparator)) {
            $response = new ConvertFileResponse($fileName, 200, [
                'Content-Type' => 'application/octet-stream',
            ], true, 'attachment', false, true, "\t", $newSeparator);
            $name     = basename($fileName, '.tsv') . '.csv';
            return $response->setContentDisposition('attachment', $name, str_replace('%', '', Str::ascii($name)));
        } else {
            return response()->download($fileName, basename($fileName), [
                'Content-Type' => 'application/octet-stream',
            ]);
        }
    }

    /**
     * Delete a selection
     *
     * @param SampleSelection $selection
     *
     * @return mixed
     * @throws \Exception
     */
    public function delete(SampleSelection $selection)
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


    /**
     * Process datatables ajax request for the list of user galaxy credential.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listGalaxyCredential()
    {
        if (!user_can(Permissions::VIEW_SELECTIONS)) {
            abort(403);
        }
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(GalaxyCredential::listCredentials(current_user()->id))->addIndexColumn();
        return $table->make(true);
    }

    /**
     * Show the form for uploading the specified resource.
     *
     * @param  SampleSelection $selection
     *
     * @return \Illuminate\Http\Response
     *
     */

    public function upload(SampleSelection $selection)
    {
        if (!user_can(Permissions::DOWNLOAD_SELECTIONS)) {
            abort(403);
        }
        if (!$selection || !$selection->exists) {
            abort(404, 'Unable to find the selection.');
        }
        if (!$selection->canDownload()) {
            abort(401, 'You are not allowed to upload this selection.');
        }
        return view('selections.upload_selection_onGalaxy',
                    [
                        'selection' => $selection,
                    ]);
    }


    /**
     * Submit the upload job
     *
     * @param  SampleSelection $selection
     *
     * @return \Illuminate\Http\Response
     *
     */
    public function doUpload(Request $request, SampleSelection $selection)
    {
        if (!user_can(Permissions::DOWNLOAD_SELECTIONS)) {
            abort(403);
        }
        if (!$selection || !$selection->exists) {
            abort(404, 'Unable to find the selection.');
        }
        if (!$selection->canDownload()) {
            abort(401, 'You are not allowed to upload this selection.');
        }
        $server = $request->get('galaxy-server');
        if (empty($server)) {
            abort(500, 'You must specify a galaxy server server');
        }
        $credential = GalaxyCredential::whereId($server)->first();
        if (empty($credential) || !$credential->exists) {
            abort(500, 'You must specify a galaxy server server');
        }
        $jobData = new JobData([
                                   'job_type' => 'galaxy_upload_job',
                                   'status'   => JobData::QUEUED,
                                   'job_data' => [
                                       'name'          => $selection->name,
                                       'data_file'     => $selection->getDataFilename(),
                                       'metadata_file' => $selection->getMetadataFilename(),
                                       'credential'    => $credential->id,
                                   ],
                                   'log'      => '',
                               ]);
        $jobData->user()->associate(\Auth::user());
        $jobData->save();
        $job = JobFactory::getQueueJob($jobData);
        $this->dispatch($job);
        Flash::success('Your upload request has been submitted. Please check the Jobs panel to verify its status.');
        return redirect()->route('selections-lists');
    }

}
