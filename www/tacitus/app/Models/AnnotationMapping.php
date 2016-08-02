<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AnnotationMapping
 *
 * @mixin \Eloquent
 * @property integer        $id
 * @property integer        $annotation_id
 * @property integer        $mapping_type_id
 * @property string         $map_to
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AnnotationMapping whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AnnotationMapping whereAnnotationId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AnnotationMapping whereMappingTypeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AnnotationMapping whereMapTo($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AnnotationMapping whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AnnotationMapping whereUpdatedAt($value)
 * @property-read \App\Models\Annotation $annotation
 * @property-read \App\Models\MappingType $type
 */
class AnnotationMapping extends Model
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function annotation()
    {
        return $this->belongsTo('App\Models\Annotation', 'annotation_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('App\Models\MappingType', 'mapping_type_id', 'id');
    }

}
