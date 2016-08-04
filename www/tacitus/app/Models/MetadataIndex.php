<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MetadataIndex
 *
 * @property integer                  $id
 * @property string                   $name
 * @property integer                  $dataset_id
 * @property \Carbon\Carbon           $created_at
 * @property \Carbon\Carbon           $updated_at
 * @property-read \App\Models\Dataset $dataset
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MetadataIndex whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MetadataIndex whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MetadataIndex whereDatasetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MetadataIndex whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MetadataIndex whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MetadataIndex extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'metadata_index';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'dataset_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dataset()
    {
        return $this->belongsTo('App\Models\Dataset', 'dataset_id', 'id');
    }


}
