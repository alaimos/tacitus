<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * App\Models\Dataset
 *
 * @property integer                                                                              $id
 * @property string                                                                               $original_id
 * @property integer                                                                              $source_id
 * @property integer                                                                              $user_id
 * @property string                                                                               $title
 * @property boolean                                                                              $private
 * @property \Carbon\Carbon                                                                       $created_at
 * @property \Carbon\Carbon                                                                       $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AvailableDatasetMetadata[] $availableMetadata
 * @property-read \App\Models\User                                                                $user
 * @property-read \App\Models\Source                                                              $source
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sample[]                   $samples
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereOriginalId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset wherePrivate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Dataset extends Model
{
    use HybridRelations;

    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const READY = 'ready';

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

}
