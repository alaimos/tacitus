<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PlatformMapping
 *
 * @property integer                   $id
 * @property integer                   $platform_id
 * @property string                    $name
 * @property \Carbon\Carbon            $created_at
 * @property \Carbon\Carbon            $updated_at
 * @property string                    $slug
 * @property-read \App\Models\Platform $platform
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\PlatformMapping whereSlug($value)
 * @mixin \Eloquent
 */
class PlatformMapping extends Model
{

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
        'platform_id', 'slug', 'name',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo('App\Models\Platform', 'platform_id');
    }

}
