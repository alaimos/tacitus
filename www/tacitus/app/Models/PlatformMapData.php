<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Models;

use App\Utils\BulkInsertableInterface;
use App\Utils\BulkModelInsertTrait;
use Jenssegers\Mongodb\Eloquent\Model as Model;

/**
 * App\Models\Metadata
 *
 * @mixin \Eloquent
 * @property string                    $platform_id
 * @property string                    $probe
 * @property-read \App\Models\Platform $platform
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapData wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapData whereProbe($value)
 */
class PlatformMapData extends Model implements BulkInsertableInterface
{
    use BulkModelInsertTrait;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * The name of the mongodb collection
     *
     * @var string
     */
    protected $collection = 'platform_map_data';

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
        'platform_id', 'probe',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo('App\Models\Platform', 'platform_id');
    }


}
