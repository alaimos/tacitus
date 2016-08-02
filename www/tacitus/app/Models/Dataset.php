<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * App\Models\Dataset
 *
 * @mixin \Eloquent
 * @property integer                                                                              $id
 * @property string                                                                               $id_source
 * @property integer                                                                              $source_id
 * @property integer                                                                              $platform_id
 * @property integer                                                                              $user_id
 * @property string                                                                               $title
 * @property boolean                                                                              $private
 * @property boolean                                                                              $guest_access
 * @property \Carbon\Carbon                                                                       $created_at
 * @property \Carbon\Carbon                                                                       $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereIdSource($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset wherePrivate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereGuestAccess($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AvailableDatasetMetadata[] $availableMetadata
 * @property-read \App\Models\User                                                                $user
 * @property-read \App\Models\Platform                                                            $platform
 * @property-read \App\Models\SupportedSource                                                     $source
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sample[]                   $samples
 */
class Dataset extends Model
{
    use HybridRelations;

    protected $connection = 'mysql';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function availableMetadata()
    {
        return $this->hasMany('App\Models\AvailableDatasetMetadata', 'dataset_id', 'id');
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
    public function platform()
    {
        return $this->belongsTo('App\Models\Platform', 'platform_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo('App\Models\SupportedSource', 'source_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function samples()
    {
        return $this->hasMany('App\Models\Sample');
    }

}
