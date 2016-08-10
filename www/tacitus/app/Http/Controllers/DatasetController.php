<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Http\Requests;
use Auth;
use Datatables;

class DatasetController extends Controller
{

    public function datasetsList()
    {

        return view('datasets.list');
        //$user = Auth::user();
        //dd(Dataset::getReadyDatasets($user)->toSql());
    }

    /**
     * Process datatables ajax request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function datasetsData()
    {
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(Dataset::getReadyDatasets(Auth::user()));
        $table->editColumn('source_id', function (Dataset $dataset) {
            return $dataset->source->display_name;
        })->addColumn('action', function (Dataset $dataset) {
            return '';
        });
        return $table->make(true);
    }

}
