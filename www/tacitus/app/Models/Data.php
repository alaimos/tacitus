<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Model;

/**
 * App\Models\Data
 *
 * @mixin \Eloquent
 * @property string                  $probe
 * @property double                  $value
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereProbe($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereSampleId($value)
 * @property-read \App\Models\Sample $sample
 */
class Data extends Model
{
    protected $connection = 'mongodb';

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sample()
    {
        return $this->belongsTo('App\Models\Sample');
    }

}
