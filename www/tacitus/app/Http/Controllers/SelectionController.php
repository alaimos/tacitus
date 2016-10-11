<?php

namespace App\Http\Controllers;

use App\Models\SampleSelection;
use App\Utils\Permissions;
use Datatables;
use Flash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class SelectionController extends Controller
{

    /**
     * Registers routes handled by this controller
     *
     * @param \Illuminate\Routing\Router $router
     */
    public static function registerRoutes(Router $router)
    {
        $router->get('/selections', ['as' => 'selections-lists', 'uses' => 'SelectionController@selectionsList']);
        $router->any('/selections/data',
            ['as' => 'selections-lists-data', 'uses' => 'SelectionController@selectionsData']);
        $router->any('/selections/list',
            ['as' => 'selections-lists-json', 'uses' => 'SelectionController@listSelectionsJson']);
        $router->get('/selections/{selection}/download/{type}',
            ['as' => 'selections-download', 'uses' => 'SelectionController@download']);
        $router->get('/selections/{selection}/delete',
            ['as' => 'selections-delete', 'uses' => 'SelectionController@delete']);
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
                'selection' => $selection
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
        $q = $request->get('q');
        $perPage = (int)$request->get('perPage', 30);
        $query = SampleSelection::listSelections();
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
     * @param SampleSelection $selection
     * @param string          $type
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(SampleSelection $selection, $type)
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
     * Delete a selection
     *
     * @param SampleSelection $selection
     *
     * @return mixed
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
}
