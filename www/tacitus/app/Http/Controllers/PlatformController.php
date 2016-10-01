<?php

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Jobs\Factory as JobFactory;
use App\Models\Job as JobData;
use App\Platform\Import\Factory\PlatformImportFactory;
use App\Utils\Permissions;
use Auth;
use Datatables;
use DaveJamesMiller\Breadcrumbs\Exception;
use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Http\Request;

use Flash;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

class PlatformController extends Controller
{

    /**
     * Prepare the list of selections
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function platformsList()
    {
        if (!user_can(Permissions::USE_TOOLS)) {
            abort(403);
        }
        return view('platforms.list');
    }

    /**
     * Process datatables ajax request for the list of selections.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function platformsListData()
    {
        if (!user_can(Permissions::USE_TOOLS)) {
            abort(403);
        }
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(Platform::listPlatforms());
        $table->addColumn('action', function ($platform) {
            return view('platforms.list_action_column', [
                'platform' => $platform
            ])->render();
        });
        return $table->make(true);
    }

    /**
     * Platform submission form
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function submission(Request $request)
    {
        $factory = new PlatformImportFactory();
        return view('platforms.submissionForm', [
            'importers' => $factory->getImportersList(),
            'input'     => $request->old()
        ]);
    }

    /**
     * Generate an error bag from a request
     *
     * @param Request $request
     * @return ViewErrorBag
     */
    protected function makeErrorBag(Request $request)
    {
        $errors = json_decode($request->get('errors', '{}'), true);
        $bag = new ViewErrorBag();
        foreach ($errors as $key => $messages) {
            $bag->put($key, new MessageBag($messages));
        }
        return $bag;
    }

    /**
     * Fill old input into current session
     *
     * @param Request $request
     */
    protected function fillOldInput(Request $request)
    {
        $oldInput = json_decode($request->get('input', '{}'), true);
        $request->session()->set('_old_input', $oldInput);
    }

    /**
     * Remove old input from current session
     *
     * @param Request $request
     */
    protected function removeOldInput(Request $request)
    {
        if ($request->session()->has('_old_input')) {
            $request->session()->remove('_old_input');
        }
    }


    /**
     * Renders the submission for for a specific importer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse.
     */
    public function submissionForm(Request $request)
    {
        $factory = new PlatformImportFactory();
        $ok = true;
        try {
            $renderer = $factory->getRenderer($request->get('importer_type'));
            $this->fillOldInput($request);
            $content = $renderer->renderForm()->with('errors', $this->makeErrorBag($request))->render();
            $this->removeOldInput($request);
        } catch (Exception $e) {
            $ok = false;
            $content = $e->getMessage();
        }
        return response()->json([
            'ok'      => $ok,
            'content' => $content,
        ]);
    }

    /**
     * Process platform submission
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processSubmission(Request $request)
    {
        $this->validate($request, [
            'importer_type' => 'required',
            'private'       => 'sometimes|boolean',
        ]);
        $importerType = $request->get('importer_type');
        $renderer = null;
        try {
            $factory = new PlatformImportFactory();
            $renderer = $factory->getRenderer($importerType);
            $renderer->beforeValidation($request);
        } catch (\Exception $e) {
            Flash::error('Error occurred while submitting job: ' . $e->getMessage());
        }
        $this->validate($request, $renderer->validationRules());
        try {
            $jobData = new JobData([
                'job_type' => 'import_platform',
                'status'   => JobData::QUEUED,
                'job_data' => [
                    'importer_type'   => $importerType,
                    'importer_config' => [],
                ],
                'log'      => ''
            ]);
            $jobData->user()->associate(Auth::user());
            $jobData->save();
            $config = $renderer->afterValidation($request, $jobData);
            $config['private'] = boolval($request->get('private', false));
            $tmp = $jobData->job_data;
            $tmp['importer_config'] = $config;
            $jobData->job_data = $tmp;
            $jobData->save();
            $job = JobFactory::getQueueJob($jobData);
            $this->dispatch($job);
            Flash::success('Your import request has been submitted. Please check the Jobs panel in order to verify ' .
                           'its status.');
        } catch (\Exception $e) {
            Flash::error('Error occurred while submitting job: ' . $e->getMessage());
        }
        return redirect()->route('platforms-lists');
    }

    /**
     * Delete a platform
     *
     * @param Platform $platform
     * @return mixed
     */
    public function delete(Platform $platform)
    {
        if (!user_can(Permissions::USE_TOOLS)) {
            abort(403);
        }
        if (!$platform || !$platform->exists) {
            abort(404, 'Unable to find the selection.');
        }
        if (!$platform->canDelete()) {
            abort(401, 'You are not allowed to delete this selection.');
        }
        $platform->delete();
        Flash::success('Selection deleted successfully.');
        return back();
    }

    /**
     * Shows platform content
     *
     * @param Request  $request
     * @param Platform $platform
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function viewPlatform(Request $request, Platform $platform)
    {
        if (!$platform || !$platform->exists) {
            abort(404, 'Unable to find the platform.');
        }
        if (!$platform->canUse()) {
            abort(401, 'You are not allowed to use this platform');
        }
        return view('platforms.view', [
            'platform' => $platform,
        ]);

    }

    /**
     * Process datatables ajax request for the list of platform mappings
     *
     * @param Request  $request
     * @param Platform $platform
     * @return \Illuminate\Http\JsonResponse
     */
    public function platformData(Request $request, Platform $platform)
    {
        if (!$platform || !$platform->exists) {
            abort(404, 'Unable to find the dataset.');
        }
        if (!$platform->canUse()) {
            abort(401, 'You are not allowed to use this dataset');
        }
        /** @var \Yajra\Datatables\Engines\CollectionEngine $table */
        $table = Datatables::of($platform->getMappingsCollection());
        return $table->make(true);
    }

}
