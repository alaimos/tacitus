<?php

namespace App\Http\Controllers;

use App\Models\GalaxyCredential;
use App\Utils\Permissions;
use Datatables;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Requests;

class GalaxyController extends Controller
{

    /**
     * Registers routes handled by this controller
     *
     * @param \Illuminate\Routing\Router $router
     */
    public static function registerRoutes(Router $router)
    {
        $router->any('/galaxy/data/{id?}',
            ['as' => 'galaxy-list-data', 'uses' => 'GalaxyController@selectionsData']);
        $router->get('/galaxy/add/credential/{user?}',
            ['as' => 'add-credential',   'uses' => 'GalaxyController@createCredential']);
        $router->post('/galaxy/add/doCredential/{user?}',
            ['as' => 'add-doCredential', 'uses' => 'GalaxyController@doCreateCredential']);
        $router->get('/galaxy/{credential}/delete',
            ['as' => 'credential-delete','uses' => 'GalaxyController@destroy']);

    }


    /**
     * Process datatables ajax request for the list of user galaxy credential.
     *
     * @param int $id
     * Note: if $id is null, then the current user's id is selected into GalaxyCredentials class
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectionsData($id = null)
    {
        if (!user_can(Permissions::VIEW_SELECTIONS)) {
            abort(403);
        }
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(GalaxyCredential::listCredentials($id)->select('id','name','hostname','port','use_https'));
        $table->addColumn('action', function (GalaxyCredential $credential) {
            return view('galaxy.add_galaxy_cred_column',
                [
                    'credential' => $credential
                ]);
        });
        return $table->make(true);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param User $user
     *
     * @return \Illuminate\Http\Response
     */
    public function createCredential(User $user)
    {

        /*add the corresponding permission*/
        return view('galaxy.add_galaxy_credential',
            [
                'user'      => $user,
                'isCurrent' => current_user()->id == $user->id,
                'isAdmin'   => user_can(Permissions::ADMINISTER)
            ]);
    }

    /**
     * Save new credential
     *
     * @param Request $request
     * @param User $user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doCreateCredential(Request $request , User $user = null)
    {
        $this->validate($request, [
            'name'        => 'required|max:255',
            'hostname'    => 'required|max:255',
            'port'        => 'required|numeric|max:65535',
            'api_key'     => 'required|confirmed'
            ]);

        if (!$user->exists) {
            $user = current_user();
        }
        $galaxy_credential = new GalaxyCredential([
             'name'        => $request->get('name'),
             'hostname'    => $request->get('hostname'),
             'port'        => $request->get('port'),
             'use_https'   => $request->get('use_https') === 'true'? true:false,
             'user_id'     => $user->id
         ]);
        $galaxy_credential->setApiKeyAttribute($request->get('api_key'));
        $galaxy_credential->save();

        Flash::success('Credentials created successfully!');
        return redirect()->route('user::profile');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  GalaxyCredential $credential
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Exception
     */
    public function destroy(GalaxyCredential $credential)
    {
        $currentUser = current_user();
        $isCurrent   = $currentUser->id == $credential->user_id;
        $isAdmin     = user_can(Permissions::ADMINISTER);

        if(!$isCurrent && !$isAdmin){
            abort(500, 'You are not allowed to delete this credential');
        }

        $credential->delete();
        Flash::success('User deleted successfully!');
        return redirect()->route('user::profile');

    }
}
