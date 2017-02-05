<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Renderer;

use App\Dataset\Renderer\Exception\RendererException;
use App\Models\Job as JobData;
use Illuminate\Http\Request;

class UserDataRenderer implements RendererInterface
{

    /**
     * Returns a view for the rendering of the custom form controls
     *
     * @return \Illuminate\View\View
     */
    public function renderForm()
    {
        return view('datasets.renderers.userdata');
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
            'title'        => 'required|max:255',
            'metadataFile' => 'required|file',
            'dataFile'     => 'required|file',
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
        $meta = $request->file('metadataFile');
        $data = $request->file('dataFile');
        if (!$meta->isValid()) {
            throw new RendererException('Uploaded metadata file is invalid.');
        }
        if (!$data->isValid()) {
            throw new RendererException('Uploaded data file is invalid.');
        }
        $uploadedMetaFile = $meta->move($jobData->getJobDirectory());
        $uploadedDataFile = $data->move($jobData->getJobDirectory());
        return [
            'title'        => $request->get('title'),
            'metadataFile' => $uploadedMetaFile->getRealPath(),
            'dataFile'     => $uploadedDataFile->getRealPath(),
        ];
    }
}