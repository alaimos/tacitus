<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;

abstract class Job
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "onQueue" and "delay" queue helper methods.
    |
    */

    use Queueable;

    /**
     * Send notification message to an user
     *
     * @param User   $to
     * @param string $icon
     * @param string $message
     */
    protected function sendNotification(User $to, $icon, $message)
    {
        $from = User::whereEmail(env('ADMIN_MAIL', 'admin@tacitus'))->first();
        /** @var \Fenos\Notifynder\NotifynderManager $notification */
        $notification = \Notifynder::category('notification');
        $notification->from($from->id)->to($to->id)->url(url('/'))->extra([
            'icon'    => $icon,
            'message' => $message,
        ])->send();
    }

    /**
     * Delete the job
     *
     * @return void
     */
    public abstract function destroy();

}
