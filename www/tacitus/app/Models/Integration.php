<?php

namespace App\Models;

use App\Utils\Permissions;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Integration
 *
 * @property integer                                                                           $id
 * @property string                                                                            $name
 * @property string                                                                            $slug
 * @property string                                                                            $generated_files
 * @property string                                                                            $status
 * @property boolean                                                                           $enable_post_mapping
 * @property integer                                                                           $user_id
 * @property integer                                                                           $platform_id
 * @property integer                                                                           $mapping_id
 * @property \Carbon\Carbon                                                                    $created_at
 * @property \Carbon\Carbon                                                                    $updated_at
 * @property-read \App\Models\User                                                             $user
 * @property-read \App\Models\Platform                                                         $platform
 * @property-read \App\Models\PlatformMapping                                                  $mapping
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SampleSelection[]       $selections
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MappedSampleSelection[] $mappedSelections
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration whereSlug($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration whereGeneratedFiles($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration whereEnablePostMapping($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration whereMappingId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Integration whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Integration extends Model
{

    const PENDING = 'pending';
    const READY   = 'ready';
    const FAILED  = 'failed';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'generated_files' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'generated_files', 'status', 'enable_post_mapping', 'user_id', 'platform_id', 'mapping_id',
    ];

    /**
     * Returns a list of supported integration algorithms
     * This list is hard coded since the R package inSilicoMerging is employed to merge expression datasets.
     *
     * @return array
     */
    public static function getSupportedIntegrationAlgorithms()
    {
        return [
            'NONE'     => [
                'None',
                'Expression matrices are put together without the use of any normalization technique.',
            ],
            'BMC'      => [
                'BMC (Sims et al. 2008)',
                'A method which employs a technique similar to z-score normalization for merging expression datasets.',
            ],
            'COMBAT'   => [
                'COMBAT (Li and Rabinovic 2007)',
                'A method which employs Empirical Bayes to estimates the parameters of a model for mean and variance for each gene and then adjusts the genes in each batch to meet the assumed model.',
            ],
            'GENENORM' => [
                'GENENORM (Benito et al. 2004)',
                'Z-score normalization: for each gene expression value in each study separately all values are altered by subtracting the mean of the gene in that dataset divided by its standard deviation.',
            ],
            'XPN'      => [
                'XPN (Shabalin et al. 2008)',
                'A method for cross-platform normalization. It finds blocks (clusters) of genes and samples in both studies that have similar expression characteristics.',
            ],
        ];
    }

    /**
     * Get all integrations
     *
     * @return \Illuminate\Database\Query\Builder
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public static function listIntegrations()
    {
        /** @var \Illuminate\Database\Query\Builder $query */
        $query = self::whereStatus(self::READY);
        if (user_can(Permissions::INTEGRATE_DATASETS) && !user_can(Permissions::ADMINISTER)) {
            $query->whereUserId(current_user()->id);
            return $query;
        } elseif (user_can(Permissions::INTEGRATE_DATASETS) && user_can(Permissions::ADMINISTER)) {
            return $query;
        } else {
            return abort(401, 'You are not allowed to view integrations.');
        }
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
    public function platform()
    {
        return $this->belongsTo('App\Models\Platform', 'platform_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mapping()
    {
        return $this->belongsTo('App\Models\PlatformMapping', 'mapping_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function selections()
    {
        return $this->belongsToMany('App\Models\SampleSelection', 'integration_selections',
            'integration_id', 'selection_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mappedSelections()
    {
        return $this->belongsToMany('App\Models\MappedSampleSelection', 'integration_mapped_selections',
            'integration_id', 'selection_id');
    }

    /**
     * Returns the path of the storage directory
     *
     * @return string
     */
    public function getStorageDirectory()
    {
        $path = storage_path('app/integrations/');
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            chmod($path, 0777);
        }
        return $path;
    }

    /**
     * Return a file name for this object
     *
     * @param string $type
     * @param string $extension
     *
     * @return string
     */
    public function getFileName($type, $extension)
    {
        return $this->getStorageDirectory() . '/' . $this->id . '-' . $this->slug . '-' . $type . '.' . $extension;
    }

    /**
     * Set the filename for metadata
     *
     * @param string $filename
     *
     * @return $this
     */
    public function setMetadataFilename($filename)
    {
        $tmp = $this->generated_files;
        $tmp['metadata'] = $filename;
        $this->generated_files = $tmp;
        return $this;
    }

    /**
     * Get the filename for metadata
     *
     * @return string|null
     */
    public function getMetadataFilename()
    {
        return isset($this->generated_files['metadata']) ? $this->generated_files['metadata'] : null;
    }

    /**
     * Set the filename for data
     *
     * @param string $filename
     *
     * @return $this
     */
    public function setDataFilename($filename)
    {
        $tmp = $this->generated_files;
        $tmp['data'] = $filename;
        $this->generated_files = $tmp;
        return $this;
    }

    /**
     * Get the filename for data
     *
     * @return string|null
     */
    public function getDataFilename()
    {
        return isset($this->generated_files['data']) ? $this->generated_files['data'] : null;
    }

    /**
     * Checks if the current user can delete this selection
     *
     * @return bool
     */
    public function canDelete()
    {
        $current = current_user();
        $isOwned = ($current !== null && $current->id == $this->user->id);
        return (user_can(Permissions::INTEGRATE_DATASETS) && (user_can(Permissions::ADMINISTER) || $isOwned));
    }

    /**
     * Checks if the current user can download this selection
     *
     * @return bool
     */
    public function canDownload()
    {
        $current = current_user();
        $isOwned = ($current !== null && $current->id == $this->user->id);
        return (user_can(Permissions::INTEGRATE_DATASETS) && (user_can(Permissions::ADMINISTER) || $isOwned));
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
        if (file_exists($this->getMetadataFilename())) {
            @unlink($this->getMetadataFilename());
        }
        if (file_exists($this->getDataFilename())) {
            @unlink($this->getDataFilename());
        }
        return parent::delete();
    }

}
