var gulp = require("gulp");
var bower = require("gulp-bower");
var elixir = require('laravel-elixir');

gulp.task('bower', function () {
    return bower();
});

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

var bower_base = 'bower_components/',
    vendors = '../../../' + bower_base;

elixir(function (mix) {

    //Images
    mix.copy(bower_base + 'jquery-ui/themes/base/images', 'public/images');
    mix.copy(bower_base + 'datatables/media/images', 'public/images');
    //C3 Library
    mix.copy(bower_base + 'c3/c3.min.css', 'public/css');
    mix.copy(bower_base + 'c3/c3.min.js', 'public/js');
    //D3 Library
    mix.copy(bower_base + 'd3/d3.min.js', 'public/js');
    //JSZip Library
    mix.copy(bower_base + 'jszip/dist/jszip.min.js', 'public/js');
    //PDFMAKE Library
    mix.copy(bower_base + 'pdfmake-dist/build/pdfmake.min.js', 'public/js');
    mix.copy(bower_base + 'pdfmake-dist/build/pdfmake.min.js.map', 'public/js')
    mix.copy(bower_base + 'pdfmake-dist/build/vfs_fonts.js', 'public/js')
    //FontAwesome Fonts
    mix.copy(bower_base + 'font-awesome/fonts', 'public/fonts');

    //CSS Libraries
    mix.styles([
        vendors + 'font-awesome/css/font-awesome.css',
        vendors + 'font-awesome-animation/dist/font-awesome-animation.css',
        vendors + 'jquery-ui/themes/base/jquery-ui.css',
        vendors + 'tether/dist/css/tether.css',
        vendors + 'bootstrap/dist/css/bootstrap.css',
        //vendors + 'datatables/media/css/jquery.dataTables.css',
        vendors + 'datatables/media/css/dataTables.bootstrap.css',
        vendors + 'metisMenu/dist/metisMenu.css',
        vendors + 'select2/dist/css/select2.css',
        'theme.css',
        'timeline.css'
    ], 'public/css/base.css');

    //JS Libraries for IE6
    mix.scripts([
        vendors + 'html5shiv/dist/html5shiv.js',
        vendors + 'Respond/dest/respond.src.js'
    ], 'public/js/ie6.js');

    //Common JS Libraries
    mix.scripts([
        vendors + 'jquery/dist/jquery.js',
        vendors + 'jquery-ui/jquery-ui.js',
        vendors + 'tether/dist/js/tether.js',
        vendors + 'bootstrap/dist/js/bootstrap.js',
        vendors + 'datatables/media/js/jquery.dataTables.js',
        vendors + 'datatables/media/js/dataTables.bootstrap.js',
        vendors + 'datatables-responsive/js/dataTables.responsive.js',
        vendors + 'datatables-responsive/js/responsive.bootstrap.js',
        vendors + 'datatables-select/js/dataTables.select.js',
        vendors + 'datatables-fixedcolumns/js/dataTables.fixedColumns.js',
        vendors + 'datatables-buttons/js/dataTables.buttons.js',
        vendors + 'datatables-buttons/js/buttons.html5.js',
        vendors + 'datatables-buttons/js/buttons.print.js',
        vendors + 'datatables-buttons/js/buttons.colVis.js',
        vendors + 'datatables-buttons/js/buttons.bootstrap.js',
        vendors + 'select2/dist/js/select2.full.js',
        vendors + 'moment/moment.js',
        vendors + 'metisMenu/dist/metisMenu.js'
    ], 'public/js/base.js');

    mix.scriptsIn('resources/assets/js', 'public/js/app.js');

    mix.sass([
        //vendors + 'datatables-responsive/css/responsive.dataTables.scss',
        vendors + 'datatables-responsive/css/responsive.bootstrap.scss',
        //vendors + 'datatables-select/css/select.dataTables.scss',
        vendors + 'datatables-select/css/select.bootstrap.scss',
        //vendors + 'datatables-fixedcolumns/css/fixedColumns.dataTables.scss',
        vendors + 'datatables-fixedcolumns/css/fixedColumns.bootstrap.scss',
        //vendors + 'datatables-buttons/css/buttons.dataTables.scss',
        vendors + 'datatables-buttons/css/buttons.bootstrap.scss',
        'app.scss'
    ], 'public/css/app.css');

});