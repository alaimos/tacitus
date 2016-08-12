<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

use App\Http\Requests;

class UserController extends Controller
{
    public function alerts()
    {
        return view('user.alerts', [
            'notifications' => Auth::user()->getNotificationsNotRead()
        ]);
    }
}
