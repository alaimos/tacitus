<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();

Route::get('/home', 'HomeController@index');

Route::group(['as'         => 'user::',
              'prefix'     => 'user',
              'middleware' => ['permission:' . \App\Utils\Permissions::USER_PANELS]],
    function (\Illuminate\Routing\Router $router) {
        $router->get('/alerts', ['as' => 'alerts', 'uses' => 'UserController@alerts']);
    }
);

Route::get('/datasets', ['as' => 'datasets-lists', 'uses' => 'DatasetController@datasetsList']);
Route::any('/datasets/data', ['as' => 'datasets-lists-data', 'uses' => 'DatasetController@datasetsData']);
Route::get('/datasets/{dataset}/selection', ['as'         => 'datasets-select',
                                             'uses'       => 'DatasetController@sampleSelection',
                                             'middleware' => ['permission:' . \App\Utils\Permissions::SELECT_FROM_DATASETS]]);
Route::get('/datasets/{dataset}/delete', ['as'         => 'datasets-delete',
                                          'uses'       => 'DatasetController@delete',
                                          'middleware' => ['permission:' . \App\Utils\Permissions::DELETE_DATASETS]]);

Route::get('/datasets/submission', ['as'         => 'datasets-submission',
                                    'uses'       => 'DatasetController@submission',
                                    'middleware' => ['permission:' . \App\Utils\Permissions::SUBMIT_DATASETS]]);

Route::post('/datasets/submission', ['as'         => 'datasets-submission-process',
                                     'uses'       => 'DatasetController@processSubmission',
                                     'middleware' => ['permission:' . \App\Utils\Permissions::SUBMIT_DATASETS]]);

Route::get('/jobs', ['as'         => 'jobs-list',
                     'uses'       => 'JobsController@jobsList',
                     'middleware' => ['permission:' . \App\Utils\Permissions::VIEW_JOBS]]);
Route::any('/jobs/data', ['as'         => 'jobs-lists-data',
                          'uses'       => 'JobsController@jobsData',
                          'middleware' => ['permission:' . \App\Utils\Permissions::VIEW_JOBS]]);
Route::any('/jobs/{job}/view', ['as'         => 'jobs-view',
                                'uses'       => 'JobsController@viewJob',
                                'middleware' => ['permission:' . \App\Utils\Permissions::VIEW_JOBS]]);

/*
Route::controller('datatables', 'DatatablesController', [
    'anyData'  => 'datatables.data',
    'getIndex' => 'datatables',
]);
 */
