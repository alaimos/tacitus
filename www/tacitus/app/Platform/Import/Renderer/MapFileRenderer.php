<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Platform\Import\Renderer;

use App\Models\Job as JobData;
use App\Platform\Import\Renderer\Exception\RendererException;
use Illuminate\Http\Request;

class MapFileRenderer implements RendererInterface
{

    /**
     * Returns a view for the rendering of the custom form controls
     *
     * @return \Illuminate\View\View
     */
    public function renderForm()
    {
        return view('platforms.import.renderers.mapfile');
    }

    /**
     * Run some actions before validation on a Request.
     *
     * @param Request $request
     * @return void
     * @throws \App\Platform\Import\Renderer\Exception\RendererException
     */
    public function beforeValidation(Request $request)
    {
    }

    /**
     * Returns a list of validation rules
     *
     * @return array
     */
    public function validationRules()
    {
        return [
            'title'    => 'required|max:255',
            'organism' => 'required|max:255',
            'mapFile'  => 'required|file',
        ];
    }

    /**
     * Run some actions after validation and returns an array of configuration values for the importer object.
     *
     * @param Request $request
     * @param JobData $jobData
     * @return array
     * @throws \App\Platform\Import\Renderer\Exception\RendererException
     */
    public function afterValidation(Request $request, JobData $jobData)
    {
        $file = $request->file('mapFile');
        if (!$file->isValid()) {
            throw new RendererException('Uploaded map file is invalid.');
        }
        $uploadedFile = $file->move($jobData->getJobDirectory());
        return [
            'title'    => $request->get('title'),
            'organism' => $request->get('organism'),
            'mapFile'  => $uploadedFile->getRealPath(),
        ];
    }
}