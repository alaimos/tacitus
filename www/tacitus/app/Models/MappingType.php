<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MappingType
 *
 * @mixin \Eloquent
 * @property integer        $id
 * @property string         $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappingType whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappingType whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappingType whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappingType whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AnnotationMapping[] $mappings
 */
class MappingType extends Model
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mappings()
    {
        return $this->hasMany('App\Models\AnnotationMapping', 'mapping_type_id', 'id');
    }

}
