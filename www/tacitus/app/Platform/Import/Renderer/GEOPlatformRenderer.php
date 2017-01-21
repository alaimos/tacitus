<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Platform\Import\Renderer;

use App\Models\Job as JobData;
use Illuminate\Http\Request;

class GEOPlatformRenderer implements RendererInterface
{

    /**
     * Returns an array which contains the name and a description of the current importer
     *
     * @return array
     */
    public function getImporterDescription()
    {
        return ['GEOPlatform', 'GEO Platform'];
    }

    /**
     * Returns a view for the rendering of the custom form controls
     *
     * @return \Illuminate\View\View
     */
    public function renderForm()
    {
        return view('platforms.import.renderers.geoplatform');
    }

    /**
     * Run some actions before validation on a Request.
     *
     * @param Request $request
     *
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
            'accessionNumber' => ['required', 'regex:/^GPL([0-9]+)$/i'],
        ];
    }

    /**
     * Run some actions after validation and returns an array of configuration values for the importer object.
     *
     * @param Request $request
     * @param JobData $jobData
     *
     * @return array
     * @throws \App\Platform\Import\Renderer\Exception\RendererException
     */
    public function afterValidation(Request $request, JobData $jobData)
    {
        return [
            'accessionNumber'   => $request->get('accessionNumber'),
            'downloadDirectory' => $jobData->getJobDirectory(),
        ];
    }
}