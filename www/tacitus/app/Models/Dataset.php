<?php

namespace App\Models;

use App\Jobs\Factory;
use App\Utils\Permissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;
use App\Models\Job as JobData;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * App\Models\Dataset
 *
 * @property integer                                                                   $id
 * @property string                                                                    $original_id
 * @property integer                                                                   $source_id
 * @property integer                                                                   $user_id
 * @property string                                                                    $title
 * @property boolean                                                                   $private
 * @property string                                                                    $status
 * @property string                                                                    $error
 * @property \Carbon\Carbon                                                            $created_at
 * @property \Carbon\Carbon                                                            $updated_at
 * @property integer                                                                   $platform_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MetadataIndex[] $metadataIndex
 * @property-read \App\Models\User                                                     $user
 * @property-read \App\Models\Source                                                   $source
 * @property-read \App\Models\Platform                                                 $platform
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sample[]        $samples
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Probe[]         $probes
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereOriginalId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset wherePrivate($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereError($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Dataset wherePlatformId($value)
 * @mixin \Eloquent
 */
class Dataset extends Model
{
    use HybridRelations, DispatchesJobs;

    const PENDING = 'pending';
    const READY = 'ready';
    const FAILED = 'failed';

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
        'original_id', 'source_id', 'user_id', 'title', 'private', 'status', 'platform_id'
    ];

    /**
     * Get all datasets
     *
     * @return \Illuminate\Database\Query\Builder
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public static function listDatasets()
    {
        /** @var \Illuminate\Database\Query\Builder $query */
        $query = self::select(['datasets.*', 'sources.name', 'sources.display_name'])
            ->join('sources', 'datasets.source_id', '=', 'sources.id')
            ->where('datasets.status', '=', self::READY);
        if (user_can(Permissions::VIEW_DATASETS) && !user_can(Permissions::ADMINISTER)) {
            $owner = current_user();
            if ($owner === null) {
                $query->where('datasets.private', '=', false);
            } else {
                $query->where(function (Builder $query) use ($owner) {
                    $query->where('datasets.user_id', '=', $owner->id)->orWhere('datasets.private', '=', false);
                });
            }
            return $query;
        } elseif (user_can(Permissions::VIEW_DATASETS) && user_can(Permissions::ADMINISTER)) {
            return $query;
        } else {
            return abort(401, 'You are not allowed to view datasets.');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function metadataIndex()
    {
        return $this->hasMany('App\Models\MetadataIndex', 'dataset_id', 'id');
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo('App\Models\Platform', 'platform_id', 'id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function samples()
    {
        return $this->hasMany('App\Models\Sample');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function probes()
    {
        return $this->hasMany('App\Models\Probe');
    }

    /**
     * Checks if the current user can delete this dataset
     *
     * @return bool
     */
    public function canDelete()
    {
        $current = current_user();
        $isOwned = ($current !== null && $current->id == $this->user->id);
        return (user_can(Permissions::DELETE_DATASETS) && (user_can(Permissions::ADMINISTER) || $isOwned));
    }

    /**
     * Checks if the current user can select data from this dataset
     *
     * @return bool
     */
    public function canSelect()
    {
        $current = current_user();
        $isOwned = ($current !== null && $current->id == $this->user->id);
        return user_can(Permissions::SELECT_FROM_DATASETS)
               && ((!$this->private) ? true : ($isOwned || user_can(Permissions::ADMINISTER)));
    }

    /**
     * Returns a collection of samples to be used as table source
     *
     * @param array $selection
     * @return Collection
     */
    public function getMetadataSamplesCollection(array $selection = [])
    {
        $samples = new Collection();
        $query = Sample::whereDatasetId($this->id);
        if (!empty($selection)) {
            $query->where(function ($query) use ($selection) {
                foreach ($selection as $id) {
                    $query->orWhere('_id', '=', $id);
                }
            });
        }
        foreach ($query->get() as $sample) {
            $samples->push($sample->toMetadataArray());
        }
        return $samples;
    }

    /**
     * Delete a sample metadata
     *
     * @param string $sampleId
     * @return void
     */
    protected function deleteSampleMetadata($sampleId)
    {
        $tmpMetadata = new Metadata();
        /** @var \MongoDb\Collection $collection */
        $collection = \DB::connection($tmpMetadata->getConnectionName())->getCollection($tmpMetadata->getTable());
        $collection->deleteMany(['sample_id' => $sampleId]);
    }

    /**
     * Delete all probes
     *
     * @return $this
     */
    public function deleteProbes()
    {
        $probe = new Probe();
        /** @var \MongoDb\Collection $collection */
        $collection = \DB::connection($probe->getConnectionName())->getCollection($probe->getTable());
        $collection->deleteMany(['dataset_id' => $this->id]);
        return $this;
    }

    /**
     * Delete all samples
     *
     * @return $this
     */
    public function deleteSamples()
    {
        $query = Sample::whereDatasetId($this->id);
        foreach ($query->get() as $sample) {
            $this->deleteSampleMetadata($sample->getKey());
            $sample->delete();
        }
        return $this;
    }

    /**
     * Delete the model from the database.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        if (!$this->canDelete()) {
            throw new \RuntimeException('You are not allowed to delete this dataset.');
        }
        $jobData = new JobData([
            'job_type' => 'delete_dataset',
            'status'   => JobData::QUEUED,
            'job_data' => [
                'dataset_id' => $this->id
            ],
            'log'      => ''
        ]);
        $jobData->save();
        $this->dispatch(Factory::getQueueJob($jobData));
        return true;
    }

    /**
     * Delete the model from the database.
     *
     * @return bool|null
     */
    public function realDelete()
    {
        return parent::delete();
    }


}
