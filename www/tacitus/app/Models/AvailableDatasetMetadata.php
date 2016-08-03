<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AvailableDatasetMetadata
 *
 * @property-read \App\Models\Dataset $dataset
 * @mixin \Eloquent
 * @property integer                  $id
 * @property string                   $name
 * @property integer                  $dataset_id
 * @property \Carbon\Carbon           $created_at
 * @property \Carbon\Carbon           $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AvailableDatasetMetadata whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AvailableDatasetMetadata whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AvailableDatasetMetadata whereDatasetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AvailableDatasetMetadata whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AvailableDatasetMetadata whereUpdatedAt($value)
 */
class AvailableDatasetMetadata extends Model
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dataset()
    {
        return $this->belongsTo('App\Models\Dataset', 'dataset_id', 'id');
    }


}
