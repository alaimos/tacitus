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

class CSVFileRenderer implements RendererInterface
{

    /**
     * Returns an array which contains the name and a description of the current importer
     *
     * @return array
     */
    public function getImporterDescription()
    {
        return ['CSV', 'CSV File'];
    }

    /**
     * Returns a view for the rendering of the custom form controls
     *
     * @return \Illuminate\View\View
     */
    public function renderForm()
    {
        return view('platforms.import.renderers.csvfile');
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
            'title'      => 'required|max:255',
            'organism'   => 'required|max:255',
            'csvFile'    => 'required|file',
            'separator'  => 'required',
            'comment'    => 'required',
            'identifier' => 'required|integer',
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
        $file = $request->file('csvFile');
        if (!$file->isValid()) {
            throw new RendererException('Uploaded csv file is invalid.');
        }
        $uploadedFile = $file->move($jobData->getJobDirectory());
        return [
            'title'      => $request->get('title'),
            'organism'   => $request->get('organism'),
            'csvFile'    => $uploadedFile->getRealPath(),
            'separator'  => $request->get('separator', ','),
            'comment'    => $request->get('comment', '#'),
            'identifier' => $request->get('identifier', 1),
        ];
    }
}