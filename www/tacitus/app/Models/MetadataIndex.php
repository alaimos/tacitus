<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Models;

use App\Utils\BulkInsertableInterface;
use App\Utils\BulkModelInsertTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MetadataIndex
 *
 * @property integer                  $id
 * @property string                   $name
 * @property integer                  $dataset_id
 * @property-read \App\Models\Dataset $dataset
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MetadataIndex whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MetadataIndex whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MetadataIndex whereDatasetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MetadataIndex whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MetadataIndex whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MetadataIndex extends Model implements BulkInsertableInterface
{

    use BulkModelInsertTrait;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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
        'name', 'dataset_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dataset()
    {
        return $this->belongsTo('App\Models\Dataset', 'dataset_id', 'id');
    }


}
