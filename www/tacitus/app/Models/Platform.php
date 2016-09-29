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
 * App\Models\Platform
 *
 * @mixin \Eloquent
 * @property integer                                                                     $id
 * @property string                                                                      $title
 * @property string                                                                      $organism
 * @property \Carbon\Carbon                                                              $created_at
 * @property \Carbon\Carbon                                                              $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlatformMapping[] $mappings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlatformMapData[] $mapData
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereOrganism($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereUpdatedAt($value)
 */
class Platform extends Model
{
    use HybridRelations;

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
        'title', 'organism'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mappings()
    {
        return $this->hasMany('App\Models\PlatformMapping', 'platform_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mapData()
    {
        return $this->hasMany('App\Models\PlatformMapData', 'platform_id', 'id');
    }

    /**
     * Get mapping array
     *
     * @param PlatformMapping|integer $mapping
     * @return array
     */
    public function getMapArray($mapping)
    {
        if (!($mapping instanceof PlatformMapping)) {
            $mapping = $this->mappings()->find($mapping);
            if ($mapping === null) {
                throw new \RuntimeException('Unable to find mapping');
            }
        }
        return $mapping->mapArray();
    }

    /**
     * Map array values
     *
     * @param PlatformMapping|integer $mapping
     * @param array                   $data
     * @return array
     */
    public function mapValues($mapping, array $data)
    {
        $map = $this->getMapArray($mapping);
        $result = [];
        foreach ($data as $value) {
            $result[$value] = (isset($map[$value])) ? $map[$value] : $value;
        }
        return $result;
    }
}
