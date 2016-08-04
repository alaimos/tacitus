<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Model;

/**
 * App\Models\Data
 *
 * @mixin \Eloquent
 * @property string                  $probe
 * @property double                  $value
 * @property integer                 $sample_id
 * @property-read \App\Models\Sample $sample
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereProbe($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereSampleId($value)
 */
class Data extends Model
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
        'probe', 'value', 'sample_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sample()
    {
        return $this->belongsTo('App\Models\Sample', 'sample_id');
    }

}
