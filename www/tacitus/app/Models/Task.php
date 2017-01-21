<?php

namespace App\Models;

use App\Utils\Permissions;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Task
 *
 * @property integer        $id
 * @property string         $description
 * @property string         $status
 * @property string         $log
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Task whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Task whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Task whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Task whereLog($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Task whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Task extends Model
{
    const RUNNING   = 'running';
    const COMPLETED = 'completed';
    const FAILED    = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description', 'status', 'log',
    ];

    /**
     * Get all tasks
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public static function listTasks()
    {
        if (user_can(Permissions::ADMINISTER)) {
            return self::query();
        } else {
            return abort(401, 'You are not allowed to view tasks.');
        }
    }

    /**
     * Checks if the current user can delete this task
     *
     * @return bool
     */
    public function canDelete()
    {
        return (user_can(Permissions::ADMINISTER));
    }

}
