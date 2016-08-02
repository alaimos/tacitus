<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Model;

/**
 * App\Models\Metadata
 *
 * @mixin \Eloquent
 * @property string                  $name
 * @property string                  $value
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereSampleId($value)
 * @property-read \App\Models\Sample $sample
 */
class Metadata extends Model
{
    protected $connection = 'mongodb';

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sample() {
        return $this->belongsTo('App\Models\Sample');
    }

}
