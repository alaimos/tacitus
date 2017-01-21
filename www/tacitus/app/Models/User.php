<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Models;

use App\Utils\Permissions;
use Fenos\Notifynder\Notifable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laratrust\Traits\LaratrustUserTrait;

/**
 * App\Models\User
 *
 * @property integer                                                                                    $id
 * @property string                                                                                     $name
 * @property string                                                                                     $email
 * @property string                                                                                     $password
 * @property string                                                                                     $remember_token
 * @property \Carbon\Carbon                                                                             $created_at
 * @property \Carbon\Carbon                                                                             $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Dataset[]                        $datasets
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Job[]                            $jobs
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[]                           $roles
 * @property-read \Fenos\Notifynder\Models\NotifynderCollection|\Fenos\Notifynder\Models\Notification[] $notifications
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereRoleIs($role = '')
 * @mixin \Eloquent
 * @property string                                                                                     $affiliation
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereAffiliation($value)
 */
class User extends Authenticatable
{
    use LaratrustUserTrait;
    use Notifable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'affiliation',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function datasets()
    {
        return $this->hasMany('App\Models\Dataset', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs()
    {
        return $this->hasMany('App\Models\Job', 'user_id', 'id');
    }


    /**
     * Returns some statistics about the user. System will be added if the user is an administrator
     *
     * @return array
     */
    public function statistics()
    {
        $stats = [];
        $stats['jobs'] = [
            'all'        => Job::whereUserId($this->id)->count(),
            'queued'     => Job::whereUserId($this->id)->whereStatus(Job::QUEUED)->count(),
            'processing' => Job::whereUserId($this->id)->whereStatus(Job::PROCESSING)->count(),
            'failed'     => Job::whereUserId($this->id)->whereStatus(Job::FAILED)->count(),
            'completed'  => Job::whereUserId($this->id)->whereStatus(Job::COMPLETED)->count(),
        ];
        $stats['datasets'] = [
            'all'     => Dataset::whereUserId($this->id)->count(),
            'pending' => Dataset::whereUserId($this->id)->whereStatus(Dataset::PENDING)->count(),
            'ready'   => Dataset::whereUserId($this->id)->whereStatus(Dataset::READY)->count(),
            'failed'  => Dataset::whereUserId($this->id)->whereStatus(Dataset::FAILED)->count(),
        ];
        $stats['selections'] = [
            'all'     => SampleSelection::whereUserId($this->id)->count(),
            'pending' => SampleSelection::whereUserId($this->id)->whereStatus(SampleSelection::PENDING)->count(),
            'ready'   => SampleSelection::whereUserId($this->id)->whereStatus(SampleSelection::READY)->count(),
            'failed'  => SampleSelection::whereUserId($this->id)->whereStatus(SampleSelection::FAILED)->count(),
        ];
        $stats['notifications'] = $this->countNotificationsNotRead();
        if (user_can(Permissions::ADMINISTER) && $this->can(Permissions::ADMINISTER)) {
            $stats['all'] = [
                'jobs'         => [
                    'all'        => Job::count(),
                    'queued'     => Job::whereStatus(Job::QUEUED)->count(),
                    'processing' => Job::whereStatus(Job::PROCESSING)->count(),
                    'failed'     => Job::whereStatus(Job::FAILED)->count(),
                    'completed'  => Job::whereStatus(Job::COMPLETED)->count(),
                ],
                'datasets'     => [
                    'all'     => Dataset::count(),
                    'pending' => Dataset::whereStatus(Dataset::PENDING)->count(),
                    'ready'   => Dataset::whereStatus(Dataset::READY)->count(),
                    'failed'  => Dataset::whereStatus(Dataset::FAILED)->count(),
                ],
                'selections'   => [
                    'all'     => SampleSelection::count(),
                    'pending' => SampleSelection::whereStatus(SampleSelection::PENDING)->count(),
                    'ready'   => SampleSelection::whereStatus(SampleSelection::READY)->count(),
                    'failed'  => SampleSelection::whereStatus(SampleSelection::FAILED)->count(),
                ],
                'failed-tasks' => Task::whereStatus(Task::FAILED)->count(),
            ];
        }
        return $stats;
    }
}
