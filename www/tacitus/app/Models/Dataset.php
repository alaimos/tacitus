<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * App\Models\Dataset
 *
 * @property integer                                                                   $id
 * @property string                                                                    $original_id
 * @property integer                                                                   $source_id
 * @property integer                                                                   $user_id
 * @property string                                                                    $title
 * @property boolean                                                                   $private
 * @property string                                                                    $status
 * @property string                                                                    $error
 * @property \Carbon\Carbon                                                            $created_at
 * @property \Carbon\Carbon                                                            $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MetadataIndex[] $metadataIndex
 * @property-read \App\Models\User                                                     $user
 * @property-read \App\Models\Source                                                   $source
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sample[]        $samples
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Probe[]         $probes
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereOriginalId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset wherePrivate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereError($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Dataset extends Model
{
    use HybridRelations;

    const PENDING = 'pending';
    const READY = 'ready';
    const FAILED = 'failed';

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
        'original_id', 'source_id', 'user_id', 'title', 'private', 'status'
    ];

    /**
     * Get all datasets
     *
     * @param null|\App\Models\User|integer $owner
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getReadyDatasets($owner = null)
    {
        $query = self::whereStatus(self::READY);
        if ($owner !== null) {
            if (!($owner instanceof User)) {
                $owner = User::whereId($owner)->first();
            }
            if ($owner->can('view-all-datasets')) {
                $query->where(function (Builder $query) use ($owner) {
                    $query->where('user_id', '=', $owner->id)->orWhere('private', '=', false);
                });
            }
        }
        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metadataIndex()
    {
        return $this->hasMany('App\Models\MetadataIndex', 'dataset_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo('App\Models\Source', 'source_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function samples()
    {
        return $this->hasMany('App\Models\Sample');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function probes()
    {
        return $this->hasMany('App\Models\Probe');
    }

}
