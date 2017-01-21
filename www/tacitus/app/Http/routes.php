<?php

/** @var \Illuminate\Routing\Router $router */

Route::auth();

$routesLookup = function ($ns, $path) use ($router) {
    $path = realpath(__DIR__ . $path);
    if (!file_exists($path)) return;
    $dir = new DirectoryIterator($path);
    foreach ($dir as $file) {
        /** @var SplFileInfo $file */
        $fileName = $file->getBasename('.php');
        if ($file->isFile() && $fileName{0} != '.' && $file->getExtension() == 'php') {
            $class = $ns . $fileName;
            if (class_exists($class) && method_exists($class, 'registerRoutes')) {
                forward_static_call([$class, 'registerRoutes'], $router);
            }
        }
    }
};

$routesLookup('\App\Http\Controllers\\', '/Controllers/');

Route::get('/not-available', ['as' => 'not-available', function () {
    return view('errors.feature_not_available');
}]);


Route::get('/not-available', ['as' => 'not-available', function () {
    return view('errors.feature_not_available');
}]);
