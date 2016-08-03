<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Model;

/**
 * App\Models\Sample
 *
 * @mixin \Eloquent
 * @property string                                                               $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Metadata[] $metadata
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Data[]     $data
 * @property-read \App\Models\Dataset                                             $dataset
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sample whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sample whereDatasetId($value)
 */
class Sample extends Model
{
    protected $connection = 'mongodb';

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dataset()
    {
        return $this->belongsTo('App\Models\Dataset');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metadata()
    {
        return $this->hasMany('App\Models\Metadata');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data()
    {
        return $this->hasMany('App\Models\Data');
    }
}
