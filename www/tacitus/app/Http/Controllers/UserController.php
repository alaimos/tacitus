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
use Datatables;
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
     * Get the correct user model
     *
     * @param User|null $user
     * @return array|void
     */
    protected function getUser(User $user = null)
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
        if ($user === null) {
            return abort(500);
        }
        return [$user, $currentUser];
    }

    /**
     * Show user profile
     *
     * @param User|null $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function profile(User $user = null)
    {
        list($user, $currentUser) = $this->getUser($user);
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
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function changePassword(User $user = null)
    {
        list($user, $currentUser) = $this->getUser($user);
        return view('user.change_password', [
            'user'      => $user,
            'isCurrent' => $currentUser->id == $user->id,
            'isAdmin'   => user_can(Permissions::ADMINISTER)
        ]);
    }

    /**
     * Changes the user password
     *
     * @param Request $request
     * @param User    $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doChangePassword(Request $request, User $user = null)
    {
        list($user, $currentUser) = $this->getUser($user);
        $isAdmin = user_can(Permissions::ADMINISTER);
        $rules = [
            'old-password' => 'required|old_password',
            'password'     => 'required|confirmed|different:old-password|min:6',
        ];
        if ($isAdmin && $user->id != $currentUser->id) {
            unset($rules['old-password']);
        }
        $this->validate($request, $rules);
        $user->password = bcrypt($request->get('password'));
        $user->save();
        Flash::success('Password changed successfully!');
        return redirect()->route('user::profile', ($user->id == $currentUser->id) ? [] : $user);
    }

    /**
     * Show user profile
     *
     * @param User|null $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function editProfile(User $user = null)
    {
        list($user, $currentUser) = $this->getUser($user);
        return view('user.edit_profile', [
            'user'      => $user,
            'isCurrent' => $currentUser->id == $user->id,
            'isAdmin'   => user_can(Permissions::ADMINISTER)
        ]);
    }

    /**
     * Edit user profile
     *
     * @param Request   $request
     * @param User|null $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function doEditProfile(Request $request, User $user = null)
    {
        list($user, $currentUser) = $this->getUser($user);
        $this->validate($request, [
            'name'        => 'required|max:255',
            'affiliation' => 'required|max:255',
            'email'       => 'required|email|max:255|unique:users,email,' . $user->id
        ]);
        $user->name = $request->get('name');
        $user->affiliation = $request->get('affiliation');
        $user->email = $request->get('email');
        $user->save();
        Flash::success('Profile saved successfully!');
        return redirect()->route('user::profile', ($user->id == $currentUser->id) ? [] : $user);
    }

    /**
     * Display users list page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function listUsers()
    {
        if (!user_can(Permissions::ADMINISTER)) {
            abort(403);
        }
        return view('user.list');
    }

    /**
     * Process datatables ajax request for the list of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listUsersData(Request $request)
    {
        if (!user_can(Permissions::ADMINISTER)) {
            abort(403);
        }
        /** @var \Yajra\Datatables\Engines\QueryBuilderEngine $table */
        $table = Datatables::of(User::query());
        $table->addColumn('action', function (User $user) {
            return view('user.list_action_column', [
                'user' => $user
            ])->render();
        });
        return $table->make(true);
    }

}
