<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * App\Models\PlatformMapping
 *
 * @property integer                                                                     $id
 * @property integer                                                                     $platform_id
 * @property string                                                                      $name
 * @property \Carbon\Carbon                                                              $created_at
 * @property \Carbon\Carbon                                                              $updated_at
 * @property-read \App\Models\Platform                                                   $platform
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlatformMapData[] $mapData
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PlatformMapping extends Model
{
    use HybridRelations;

    /**
     * Map Array Cache
     *
     * @var null|array
     */
    protected $map = null;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'platform_id', 'name'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo('App\Models\Platform', 'platform_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mapData()
    {
        return $this->hasMany('App\Models\PlatformMapData', 'mapping_id', 'id');
    }

    /**
     * Builds map array
     *
     * @return array
     */
    public function mapArray()
    {
        if ($this->map === null) {
            $this->map = [];
            foreach ($this->mapData as $mapData) {
                $from = $mapData->mapFrom;
                if (isset($this->map[$from])) {
                    if (is_array($this->map[$from])) {
                        $this->map[$from][] = $mapData->mapTo;
                    } else {
                        $this->map[$from] = [$this->map[$from], $mapData->mapTo];
                    }
                } else {
                    $this->map[$from] = $mapData->mapTo;
                }
            }
        }
        return $this->map;
    }

}
