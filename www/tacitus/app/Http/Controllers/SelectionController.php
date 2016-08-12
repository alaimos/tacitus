<?php

namespace App\Http\Controllers;

use App\Models\SampleSelection;
use App\Utils\Permissions;
use Datatables;
use Flash;
use Illuminate\Http\Request;

use App\Http\Requests;

class SelectionController extends Controller
{
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
     * Download a file from a selection
     *
     * @param SampleSelection $selection
     * @param string          $type
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
