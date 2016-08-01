<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class Notification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification {user} {icon} {message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a notification';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $from = User::whereEmail('admin@tacitus')->first();
        $user = User::whereEmail($this->argument('user'))->first();
        $icon = $this->argument('icon');
        $message = $this->argument('message');

        /** @var \Fenos\Notifynder\NotifynderManager $notification */
        $notification = \Notifynder::category('notification');
        $notification->from($from->id)->to($user->id)->url(url('/'))->extra([
            'icon'    => $icon,
            'message' => $message,
        ])->send();

    }
}
