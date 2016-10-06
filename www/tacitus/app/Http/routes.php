<?php

use App\Utils\Permissions;

Route::get('/', 'HomeController@index');

Route::auth();

Route::group(['as'         => 'user::',
              'prefix'     => 'user',
              'middleware' => ['permission:' . \App\Utils\Permissions::USER_PANELS]],
    function (\Illuminate\Routing\Router $router) {
        $router->get('/alerts', ['as' => 'alerts', 'uses' => 'UserController@alerts']);
        $router->get('/list', ['as' => 'list', 'uses' => 'UserController@listUsers']);
        $router->any('/list/data', ['as' => 'list-data', 'uses' => 'UserController@listUsersData']);
        $router->get('/create', ['as' => 'create', 'uses' => 'UserController@createUser']);
        $router->post('/create', ['as' => 'create-post', 'uses' => 'UserController@doCreateUser']);
        $router->get('/delete/{user}', ['as' => 'delete', 'uses' => 'UserController@delete']);
        $router->get('/profile/{user?}', ['as' => 'profile', 'uses' => 'UserController@profile']);
        $router->get('/profile/edit/details/{user?}', ['as' => 'edit-profile', 'uses' => 'UserController@editProfile']);
        $router->post('/profile/edit/details/{user?}',
            ['as' => 'edit-profile-post', 'uses' => 'UserController@doEditProfile']);
        $router->get('/profile/password/change/{user?}',
            ['as' => 'change-password', 'uses' => 'UserController@changePassword']);
        $router->post('/profile/password/change/{user?}',
            ['as' => 'change-password-post', 'uses' => 'UserController@doChangePassword']);
    }
);

Route::get('/datasets', ['as' => 'datasets-lists', 'uses' => 'DatasetController@datasetsList']);
Route::any('/datasets/data', ['as' => 'datasets-lists-data', 'uses' => 'DatasetController@datasetsData']);
Route::get('/datasets/{dataset}/selection', ['as'   => 'datasets-select',
                                             'uses' => 'DatasetController@sampleSelection']);
Route::any('/datasets/{dataset}/selection/data', ['as'   => 'datasets-lists-samples',
                                                  'uses' => 'DatasetController@sampleSelectionData']);
Route::post('/datasets/{dataset}/selection', ['as'   => 'queue-dataset-selection',
                                              'uses' => 'DatasetController@queueSampleSelection']);
Route::get('/datasets/{dataset}/delete', ['as'         => 'datasets-delete',
                                          'uses'       => 'DatasetController@delete',
                                          'middleware' => ['permission:' . Permissions::DELETE_DATASETS]]);

Route::get('/datasets/submission', ['as'         => 'datasets-submission',
                                    'uses'       => 'DatasetController@submission',
                                    'middleware' => ['permission:' . Permissions::SUBMIT_DATASETS]]);

Route::post('/datasets/submission', ['as'         => 'datasets-submission-process',
                                     'uses'       => 'DatasetController@processSubmission',
                                     'middleware' => ['permission:' . Permissions::SUBMIT_DATASETS]]);

Route::get('/jobs', ['as'         => 'jobs-list',
                     'uses'       => 'JobsController@jobsList',
                     'middleware' => ['permission:' . Permissions::VIEW_JOBS]]);
Route::any('/jobs/data', ['as'         => 'jobs-lists-data',
                          'uses'       => 'JobsController@jobsData',
                          'middleware' => ['permission:' . Permissions::VIEW_JOBS]]);
Route::any('/jobs/{job}/view', ['as'         => 'jobs-view',
                                'uses'       => 'JobsController@viewJob',
                                'middleware' => ['permission:' . Permissions::VIEW_JOBS]]);
Route::get('/jobs/{job}/delete', ['as'         => 'jobs-delete',
                                  'uses'       => 'JobsController@delete',
                                  'middleware' => ['permission:' . Permissions::VIEW_JOBS]]);

Route::get('/tasks', ['as'         => 'tasks-list',
                      'uses'       => 'TasksController@tasksList',
                      'middleware' => ['permission:' . Permissions::ADMINISTER]]);
Route::any('/tasks/data', ['as'         => 'tasks-lists-data',
                           'uses'       => 'TasksController@tasksData',
                           'middleware' => ['permission:' . Permissions::ADMINISTER]]);
Route::any('/tasks/{task}/view', ['as'         => 'tasks-view',
                                  'uses'       => 'TasksController@viewTask',
                                  'middleware' => ['permission:' . Permissions::ADMINISTER]]);
Route::get('/tasks/{task}/delete', ['as'         => 'tasks-delete',
                                    'uses'       => 'TasksController@delete',
                                    'middleware' => ['permission:' . Permissions::ADMINISTER]]);

Route::get('/selections', ['as' => 'selections-lists', 'uses' => 'SelectionController@selectionsList']);
Route::any('/selections/data', ['as' => 'selections-lists-data', 'uses' => 'SelectionController@selectionsData']);
Route::get('/selections/{selection}/download/{type}',
    ['as' => 'selections-download', 'uses' => 'SelectionController@download']);
Route::get('/selections/{selection}/delete',
    ['as' => 'selections-delete', 'uses' => 'SelectionController@delete']);

Route::get('/platforms', ['as' => 'platforms-lists', 'uses' => 'PlatformController@platformsList']);
Route::any('/platforms/data', ['as' => 'platforms-lists-data', 'uses' => 'PlatformController@platformsListData']);
Route::get('/platforms/submission', ['as' => 'platforms-submission', 'uses' => 'PlatformController@submission']);
Route::post('/platforms/submission',
    ['as' => 'platforms-submission-process', 'uses' => 'PlatformController@processSubmission']);
Route::post('/platforms/submission/form',
    ['as' => 'platforms-submission-form', 'uses' => 'PlatformController@submissionForm']);
Route::get('/platforms/{platform}/delete', ['as' => 'platforms-delete', 'uses' => 'PlatformController@delete']);
Route::get('/platforms/{platform}/view', ['as' => 'platforms-view', 'uses' => 'PlatformController@viewPlatform']);
Route::any('/platforms/{platform}/data', ['as' => 'platforms-view-data', 'uses' => 'PlatformController@platformData']);
Route::any('/platforms/list',
    ['as' => 'platforms-list-json', 'uses' => 'PlatformController@listPlatformsJson']);
Route::any('/platforms/{platform}/mappings',
    ['as' => 'platforms-list-mappings', 'uses' => 'PlatformController@listMappings']);

Route::get('/selections/{selection}/map',
    ['as' => 'mapped-selections-submit', 'uses' => 'MappedSelectionController@submitMappingForm']);
Route::post('/selections/{selection}/map',
    ['as' => 'mapped-selections-do-submit', 'uses' => 'MappedSelectionController@submitMapping']);
Route::get('/selections/mapped',
    ['as' => 'mapped-selections-lists', 'uses' => 'MappedSelectionController@selectionsList']);
Route::any('/selections/mapped/data',
    ['as' => 'mapped-selections-lists-data', 'uses' => 'MappedSelectionController@selectionsData']);
Route::get('/selections/mapped/{selection}/download/{type}',
    ['as' => 'mapped-selections-download', 'uses' => 'MappedSelectionController@download']);
Route::get('/selections/mapped/{selection}/delete',
    ['as' => 'mapped-selections-delete', 'uses' => 'MappedSelectionController@delete']);


Route::get('/not-available', ['as' => 'not-available', function () {
    return view('errors.feature_not_available');
}]);


Route::get('/not-available', ['as' => 'not-available', function () {
    return view('errors.feature_not_available');
}]);
/*
Route::controller('datatables', 'DatatablesController', [
    'anyData'  => 'datatables.data',
    'getIndex' => 'datatables',
]);
 */
