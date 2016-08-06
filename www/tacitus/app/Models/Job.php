<?php

namespace App\Models;

use app\Utils\Utils;
use Illuminate\Database\Eloquent\Model;
use League\Flysystem\Util;

/**
 * App\Models\Job
 *
 * @property integer        $id
 * @property string         $job_type
 * @property string         $status
 * @property string         $job_data
 * @property string         $log
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereJobType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereJobData($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereLog($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Job extends Model
{

    const QUEUED = 'queued';
    const PROCESSING = 'processing';
    const COMPLETED = 'completed';
    const FAILED = 'failed';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'job_data' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_type', 'status', 'job_data'
    ];

    /**
     * Returns the path of the job storage directory
     *
     * @return string
     */
    public function getJobDirectory()
    {
        $path = storage_path('app/jobs/' . $this->id);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

    /**
     * Delete the job directory
     *
     * @return bool
     */
    public function deleteJobDirectory()
    {
        return Utils::delete($this->getJobDirectory());
    }

}
