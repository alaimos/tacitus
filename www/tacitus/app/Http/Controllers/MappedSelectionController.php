<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Http\Controllers;

use App\Models\SampleSelection;
use App\Utils\Permissions;
use Illuminate\Http\Request;

use App\Http\Requests;

class MappedSelectionController extends Controller
{

    /**
     * @param Request         $request
     * @param SampleSelection $selection
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

}
