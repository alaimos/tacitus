<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Utils\Permissions;
use Auth;
use Flash;
use Illuminate\Http\Request;

use App\Http\Requests;

class UserController extends Controller
{
    /**
     * Shows user alerts
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function alerts()
    {
        return view('user.alerts', [
            'notifications' => Auth::user()->getNotificationsNotRead()
        ]);
    }

    /**
     * Show user profile
     *
     * @param User|null $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function profile(User $user = null)
    {
        if (!$user->exists) {
            $user = null;
        }
        $currentUser = Auth::user();
        if ($currentUser === null
            || ($user !== null && $currentUser !== null
                && $currentUser->id != $user->id
                && !user_can(Permissions::ADMINISTER))
        ) {
            return abort(403);
        }
        $user = ($user !== null) ? $user : $currentUser;
        return view('user.profile', [
            'user'       => $user,
            'statistics' => $user->statistics(),
            'isCurrent'  => $currentUser->id == $user->id,
            'isAdmin'    => user_can(Permissions::ADMINISTER)
        ]);
    }

    /**
     * Show change password form
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function changePassword()
    {
        if (Auth::user() === null) {
            abort(403);
        }
        return view('user.change_password');
    }

    /**
     * Changes the user password
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doChangePassword(Request $request)
    {
        $user = Auth::user();
        if ($user === null) {
            abort(403);
        }
        $this->validate($request, [
            'old-password' => 'required|old_password',
            'password'     => 'required|confirmed|different:old-password|min:6',
        ]);
        $user->password = bcrypt($request->get('password'));
        $user->save();
        Flash::success('Password changed successfully!');
        return redirect()->route('user::profile');
    }

    /**
     * Show user profile
     *
     * @param User|null $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function editProfile(User $user = null)
    {
        if (!$user->exists) {
            $user = null;
        }
        $currentUser = Auth::user();
        if ($currentUser === null
            || ($user !== null && $currentUser !== null
                && $currentUser->id != $user->id
                && !user_can(Permissions::ADMINISTER))
        ) {
            return abort(403);
        }
        $user = ($user !== null) ? $user : $currentUser;
        return view('user.edit_profile', [
            'user'      => $user,
            'isCurrent' => $currentUser->id == $user->id,
            'isAdmin'   => user_can(Permissions::ADMINISTER)
        ]);
    }

}
