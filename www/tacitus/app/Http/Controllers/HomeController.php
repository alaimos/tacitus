<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\Dataset;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('welcome', [
            'totalUsers'    => User::count(),
            'totalDatasets' => Dataset::whereStatus(Dataset::READY)->count(),
        ]);
    }
}
