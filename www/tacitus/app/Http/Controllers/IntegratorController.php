<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Models\MappedSampleSelection;
use App\Models\SampleSelection;
use App\Utils\Permissions;
use Auth;
use Datatables;
use Flash;
use Illuminate\Http\Request;
use App\Jobs\Factory as JobFactory;
use App\Models\Job as JobData;
use App\Http\Requests;
use Illuminate\Routing\Router;

class IntegratorController extends Controller
{

    /**
     * Registers routes handled by this controller
     *
     * @param \Illuminate\Routing\Router $router
     */
    public static function registerRoutes(Router $router)
    {
        $router->get('/integrations/submit',
            ['as' => 'integration-submit', 'uses' => 'IntegratorController@submitForm']);
        $router->post('/integrations/submit',
            ['as' => 'integration-do-submit', 'uses' => 'IntegratorController@submitIntegrationJob']);
        $router->get('/integrations',
            ['as' => 'integrations-lists', 'uses' => 'IntegratorController@integrationsList']);
        $router->any('/integrations/data',
            ['as' => 'integrations-lists-data', 'uses' => 'IntegratorController@integrationsData']);
        $router->get('/integrations/{integration}/download/{type}',
            ['as' => 'integration-download', 'uses' => 'IntegratorController@download']);
        $router->get('/integrations/{integration}/delete',
            ['as' => 'integration-delete', 'uses' => 'IntegratorController@delete']);

    }

    /**
     * Shows integration submission form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function submitForm()
    {
        if (!user_can(Permissions::INTEGRATE_DATASETS)) {
            abort(403);
        }
        $methods = Integration::getSupportedIntegrationAlgorithms();
        array_walk($methods, function (&$element) {
            $element = $element[0];
        });
        return view('integrator.submissionForm', [
            'methods' => $methods,
        ]);
    }

    public function submitIntegrationJob(Request $request)
    {
        if (!user_can(Permissions::INTEGRATE_DATASETS)) {
            abort(403);
        }
        $this->validate($request, [
            'name'                => 'required|max:255',
            'selections'          => 'required_without:mapped_selections|array',
            'mapped_selections'   => 'required_without:selections|array',
            'method'              => 'required|in:' . implode(',',
                    array_keys(Integration::getSupportedIntegrationAlgorithms())),
            'digits'              => 'required|numeric',
            'na_strings'          => 'required',
            'enable_post_mapping' => 'sometimes|required|boolean',
            'platform'            => 'required_with:enable_post_mapping|exists:platforms,id',
            'mapping'             => 'required_with:enable_post_mapping|exists:platform_mappings,id',
        ]);
        $mappingEnabled = boolval($request->get('enable_post_mapping', false));
        $jobData = new JobData([
            'job_type' => 'integrate_selections',
            'status'   => JobData::QUEUED,
            'job_data' => [
                'name'                => $request->get('name'),
                'selections'          => array_map("intval", (array)$request->get('selections')),
                'mapped_selections'   => array_map("intval", (array)$request->get('mapped_selections')),
                'method'              => $request->get('method'),
                'digits'              => (int)$request->get('digits'),
                'na_strings'          => $request->get('na_strings'),
                'enable_post_mapping' => $mappingEnabled,
                'platform'            => ($mappingEnabled) ? (int)$request->get('platform') : null,
                'mapping'             => ($mappingEnabled) ? (int)$request->get('mapping') : null,
            ],
            'log'      => ''
        ]);
        $jobData->user()->associate(Auth::user());
        $jobData->save();
        $job = JobFactory::getQueueJob($jobData);
        $this->dispatch($job);
        Flash::success('Your request has been submitted. Please check the Jobs panel in order to verify its status.');
        return redirect()->route('integrations-lists');
    }

    /**
     * Prepare the list of integrations
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function integrationsList()
    {
        if (!user_can(Permissions::INTEGRATE_DATASETS)) {
            abort(403);
        }
        return view('integrator.list');
    }

    /**
     * Process datatables ajax request for the list of integrations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function integrationsData()
    {
        if (!user_can(Permissions::INTEGRATE_DATASETS)) {
            abort(403);
        }
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(Integration::listIntegrations());
        $table->addColumn('action', function (Integration $integration) {
            return view('integrator.list_action_column', [
                'integration' => $integration
            ])->render();
        });
        return $table->make(true);
    }

    /**
     * Download a file from an integration
     *
     * @param Integration $integration
     * @param string      $type
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(Integration $integration, $type)
    {
        if (!user_can(Permissions::INTEGRATE_DATASETS)) {
            abort(403);
        }
        if (!$integration || !$integration->exists) {
            abort(404, 'Unable to find the data.');
        }
        if (!$integration->canDownload()) {
            abort(401, 'You are not allowed to download this data.');
        }
        $fileName = null;
        switch (strtolower($type)) {
            case 'metadata':
                $fileName = $integration->getMetadataFilename();
                break;
            case 'data':
                $fileName = $integration->getDataFilename();
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
     * Delete an integration
     *
     * @param Integration $integration
     *
     * @return mixed
     */
    public function delete(Integration $integration)
    {
        if (!user_can(Permissions::INTEGRATE_DATASETS)) {
            abort(403);
        }
        if (!$integration || !$integration->exists) {
            abort(404, 'Unable to find the data.');
        }
        if (!$integration->canDelete()) {
            abort(401, 'You are not allowed to download this data.');
        }
        $integration->delete();
        Flash::success('Integration deleted successfully.');
        return back();
    }
}
