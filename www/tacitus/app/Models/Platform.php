<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Models;

use App\Utils\FixedMongoQueryBuilder;
use App\Utils\Permissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * App\Models\Platform
 *
 * @property integer                                                                     $id
 * @property string                                                                      $title
 * @property string                                                                      $organism
 * @property string                                                                      $status
 * @property \Carbon\Carbon                                                              $created_at
 * @property \Carbon\Carbon                                                              $updated_at
 * @property boolean                                                                     $private
 * @property integer                                                                     $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlatformMapping[] $mappings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlatformMapData[] $mapData
 * @property-read \App\Models\User                                                       $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereOrganism($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform wherePrivate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereUserId($value)
 * @mixin \Eloquent
 */
class Platform extends Model
{
    use HybridRelations;

    const PENDING = 'pending';
    const READY   = 'ready';
    const FAILED  = 'failed';

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
        'title', 'organism', 'private', 'user_id', 'status',
    ];

    /**
     * Cache of map array
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Get all selections
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder|static[]
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public static function listPlatforms()
    {
        /** @var \Illuminate\Database\Query\Builder $query */
        $query = self::whereStatus(self::READY);
        if (user_can(Permissions::USE_TOOLS) && !user_can(Permissions::ADMINISTER)) {
            $owner = current_user();
            return $query->whereUserId($owner->id);
        } elseif (user_can(Permissions::USE_TOOLS) && user_can(Permissions::ADMINISTER)) {
            return $query;
        } else {
            return abort(401, 'You are not allowed to view platforms.');
        }
    }

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    /**
     * Delete all MapData
     *
     * @return $this
     */
    protected function deleteMapData()
    {
        $mapData = new PlatformMapData();
        /** @var \MongoDb\Collection $collection */
        $collection = \DB::connection($mapData->getConnectionName())->getCollection($mapData->getTable());
        $collection->deleteMany(['platform_id' => $this->id]);
        return $this;
    }

    /**
     * Checks if the current user can use this platform
     *
     * @return bool
     */
    public function canUse()
    {
        $current = current_user();
        $isOwned = ($current !== null && $current->id == $this->user->id);
        return (user_can(Permissions::USE_TOOLS) && (user_can(Permissions::ADMINISTER) || $isOwned));
    }

    /**
     * Checks if the current user can delete this platform
     *
     * @return bool
     */
    public function canDelete()
    {
        $current = current_user();
        $isOwned = ($current !== null && $current->id == $this->user->id);
        return (user_can(Permissions::USE_TOOLS) && (user_can(Permissions::ADMINISTER) || $isOwned));
    }

    /**
     * Delete the model from the database.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        if ($this->exists) {
            $this->deleteMapData();
        }
        return parent::delete();
    }

    /**
     * Get mapping array
     *
     * @param PlatformMapping|integer $mapping
     *
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
        if (isset($this->cache[$mapping->getKey()])) {
            return $this->cache[$mapping->getKey()];
        }
        $tmp = new PlatformMapData();
        /** @var \Jenssegers\Mongodb\Query\Builder $query */
        $query = \DB::connection($tmp->getConnectionName())->collection($tmp->getTable());
        $field = $mapping->slug;
        $this->cache[$mapping->getKey()] = $query->select([$field, 'probe'])
                                                 ->where('platform_id', '=', $this->getKey())
                                                 ->pluck($field, 'probe');
        return $this->cache[$mapping->getKey()];
    }

    /**
     * Map array values
     *
     * @param PlatformMapping|integer $mapping
     * @param array                   $data
     *
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

    /**
     * Generate a map from mapping id to mapping name
     *
     * @param bool $byId
     *
     * @return array|Collection
     */
    public function mappingList($byId = false)
    {
        return PlatformMapping::wherePlatformId($this->id)->pluck('name', (($byId) ? 'id' : 'slug'));
    }

    /**
     * Count all map data without querying for a resultset
     *
     * @return int
     */
    public function countMapData()
    {
        $tmp = new PlatformMapData();
        $connection = $tmp->getConnectionName();
        $collection = $tmp->getTable();
        return \DB::connection($connection)->getCollection($collection)->count(['platform_id' => $this->getKey()]);
    }

    /**
     * Returns a collection of map data to be used as table source
     *
     * @return \Jenssegers\Mongodb\Query\Builder
     */
    public function getMappingsCollection()
    {
        $tmp = new PlatformMapData();
        /** @var \Jenssegers\Mongodb\Connection $connection */
        $connection = \DB::connection($tmp->getConnectionName());
        $query = new FixedMongoQueryBuilder($connection->collection($tmp->getTable()));
        $query->where('platform_id', '=', $this->getKey());
        $connection->setQueryGrammar($query->getGrammar());
        return $query;
    }

}
