<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Model;

/**
 * App\Models\Sample
 *
 * @mixin \Eloquent
 * @property string                                                               $name
 * @property integer                                                              $position
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Metadata[] $metadata
 * @property-read \App\Models\Dataset                                             $dataset
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sample whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Sample whereDatasetId($value)
 */
class Sample extends Model
{

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'position', 'dataset_id',
    ];

    /**
     * Return a complete metadata array for this sample
     *
     * @return array
     */
    public function toMetadataArray()
    {
        $data = [
            'id'   => $this->position + 1,
            'key'  => $this->getKey(),
            'name' => $this->name,
        ];
        $tmpMetadata = new Metadata();
        /** @var \Jenssegers\Mongodb\Query\Builder $query */
        $query = \DB::connection($tmpMetadata->getConnectionName())->collection($tmpMetadata->getTable());
        $query->select()->where('sample_id', '=', $this->getKey());
        foreach ($query->pluck('value', 'name') as $name => $value) {
            $data[snake_case($name)] = $value;
        }
        return $data;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dataset()
    {
        return $this->belongsTo('App\Models\Dataset', 'dataset_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metadata()
    {
        return $this->hasMany('App\Models\Metadata', 'sample_id');
    }
}
