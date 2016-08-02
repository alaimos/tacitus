<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Annotation
 *
 * @mixin \Eloquent
 * @property integer        $id
 * @property integer        $platform_id
 * @property string         $probe_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Annotation whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Annotation wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Annotation whereProbeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Annotation whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Annotation whereUpdatedAt($value)
 * @property-read \App\Models\Platform $platform
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AnnotationMapping[] $mappings
 */
class Annotation extends Model
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo('App\Models\Platform', 'platform_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mappings()
    {
        return $this->hasMany('App\Models\AnnotationMapping', 'annotation_id', 'id');
    }

}
