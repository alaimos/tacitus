<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Models;

use App\Utils\Permissions;
use App\Utils\Utils;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Job
 *
 * @property integer               $id
 * @property string                $job_type
 * @property string                $status
 * @property array                 $job_data
 * @property string                $log
 * @property \Carbon\Carbon        $created_at
 * @property \Carbon\Carbon        $updated_at
 * @property integer               $user_id
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereJobType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereJobData($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereLog($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereUserId($value)
 * @mixin \Eloquent
 */
class Job extends Model
{

    const QUEUED     = 'queued';
    const PROCESSING = 'processing';
    const COMPLETED  = 'completed';
    const FAILED     = 'failed';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'job_data' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_type', 'status', 'job_data', 'log', 'user_id',
    ];

    /**
     * Get all jobs
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public static function listJobs()
    {
        $query = self::query();
        if (user_can(Permissions::VIEW_JOBS) && !user_can(Permissions::ADMINISTER)) {
            $owner = current_user();
            if ($owner !== null) {
                $query->where('user_id', '=', $owner->id);
            }
            return $query;
        } elseif (user_can(Permissions::VIEW_JOBS) && user_can(Permissions::ADMINISTER)) {
            return $query;
        } else {
            return abort(401, 'You are not allowed to view jobs.');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

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
            chmod($path, 0777);
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

    /**
     * Checks if the current user can delete this dataset
     *
     * @return bool
     */
    public function canDelete()
    {
        $current = current_user();
        $isOwned = ($current !== null && $current->id == $this->user->id);
        return (user_can(Permissions::VIEW_JOBS) && (user_can(Permissions::ADMINISTER) || $isOwned));
    }

    /**
     * Get the value of a parameter for this job
     *
     * @param string|array|null $parameter
     * @param mixed             $default
     *
     * @return mixed
     */
    public function getData($parameter = null, $default = null)
    {
        if ($parameter === null) {
            return $this->job_data;
        } elseif (is_array($parameter)) {
            $slice = [];
            foreach ($parameter as $key) {
                $slice[$key] = (isset($this->job_data[$key])) ? $this->job_data[$key] : $default;
            }
            return $slice;
        }
        return (isset($this->job_data[$parameter])) ? $this->job_data[$parameter] : $default;
    }

    /**
     * Set the value of a parameter for this job
     *
     * @param string $parameter
     * @param mixed  $value
     *
     * @return $this
     */
    public function setData($parameter, $value)
    {
        $tmp = $this->job_data;
        $tmp[$parameter] = $value;
        $this->job_data = $tmp;
        return $this;
    }

    /**
     * Add parameters to this job
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function addData($parameters)
    {
        foreach ($parameters as $param => $value) {
            $this->setData($param, $value);
        }
        return $this;
    }

    /**
     * Set parameters to this job
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->job_data = [];
        return $this->addData($parameters);
    }


}
