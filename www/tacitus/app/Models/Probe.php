<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Model;

/**
 * App\Models\Probe
 *
 * @mixin \Eloquent
 * @property string                   $name
 * @property array                    $data
 * @property integer                  $dataset_id
 * @property-read \App\Models\Dataset $dataset
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Probe whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Probe whereDatasetId($value)
 */
class Probe extends Model
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'data', 'dataset_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dataset()
    {
        return $this->belongsTo('App\Models\Dataset', 'dataset_id');
    }
}
