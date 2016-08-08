<?php

namespace App\Models;

use App\Utils\BulkInsertableInterface;
use App\Utils\BulkModelInsertTrait;
use Jenssegers\Mongodb\Eloquent\Model as Model;

/**
 * App\Models\Metadata
 *
 * @mixin \Eloquent
 * @property string                  $name
 * @property string                  $value
 * @property-read \App\Models\Sample $sample
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Metadata whereSampleId($value)
 */
class Metadata extends Model implements BulkInsertableInterface
{

    use BulkModelInsertTrait;

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
        'name', 'value', 'sample_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sample()
    {
        return $this->belongsTo('App\Models\Sample', 'sample_id');
    }

}
