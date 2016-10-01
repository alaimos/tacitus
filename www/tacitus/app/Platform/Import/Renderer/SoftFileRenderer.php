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

class SoftFileRenderer implements RendererInterface
{

    /**
     * Returns an array which contains the name and a description of the current importer
     *
     * @return array
     */
    public function getImporterDescription()
    {
        return ['SoftFile', 'SOFT File'];
    }

    /**
     * Returns a view for the rendering of the custom form controls
     *
     * @return \Illuminate\View\View
     */
    public function renderForm()
    {
        return view('platforms.import.renderers.softfile');
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
            'softFile' => 'required|file',
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
        $file = $request->file('softFile');
        if (!$file->isValid()) {
            throw new RendererException('Uploaded SOFT file is invalid.');
        }
        $uploadedFile = $file->move($jobData->getJobDirectory());
        return [
            'softFile' => $uploadedFile->getRealPath(),
        ];
    }
}