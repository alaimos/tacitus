<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Job
 *
 * @property integer        $id
 * @property string         $job_type
 * @property string         $status
 * @property array          $job_data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereJobType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereJobData($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Job extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'job_data' => 'array'
    ];
}
