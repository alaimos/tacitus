<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Dataset\Renderer;

use App\Models\Job as JobData;
use Illuminate\Http\Request;

interface RendererInterface
{

    /**
     * Returns a view for the rendering of the custom form controls
     *
     * @return \Illuminate\View\View
     */
    public function renderForm();

    /**
     * Run some actions before validation on a Request.
     *
     * @param Request $request
     *
     * @return void
     * @throws \App\Dataset\Renderer\Exception\RendererException
     */
    public function beforeValidation(Request $request);

    /**
     * Returns a list of validation rules
     *
     * @return array
     */
    public function validationRules();

    /**
     * Run some actions after validation and returns an array of parameters for the job
     *
     * @param Request $request
     * @param JobData $jobData
     *
     * @return array
     * @throws \App\Dataset\Renderer\Exception\RendererException
     */
    public function afterValidation(Request $request, JobData $jobData);

}