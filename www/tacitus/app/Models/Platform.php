<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Platform
 *
 * @mixin \Eloquent
 * @property integer        $id
 * @property string         $id_source
 * @property integer        $source_id
 * @property string         $name
 * @property string         $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereIdSource($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Platform whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Dataset[] $datasets
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Annotation[] $annotations
 * @property-read \App\Models\SupportedSource $source
 */
class Platform extends Model
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function datasets()
    {
        return $this->hasMany('App\Models\Dataset', 'platform_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function annotations()
    {
        return $this->hasMany('App\Models\Annotation', 'platform_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo('App\Models\SupportedSource', 'source_id', 'id');
    }

}
