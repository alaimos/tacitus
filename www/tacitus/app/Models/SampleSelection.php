<?php
/**
 * TACITuS - Transcriptomic dAta Collector, InTegrator, and Selector
 *
 * @author S. Alaimo, Ph.D. <alaimos at gmail dot com>
 */

namespace App\Models;

use App\Utils\Permissions;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SampleSelection
 *
 * @property integer                  $id
 * @property string                   $name
 * @property string                   $slug
 * @property array                    $selected_samples
 * @property array                    $generated_files
 * @property string                   $status
 * @property integer                  $dataset_id
 * @property \Carbon\Carbon           $created_at
 * @property \Carbon\Carbon           $updated_at
 * @property integer                  $user_id
 * @property-read \App\Models\User    $user
 * @property-read \App\Models\Dataset $dataset
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SampleSelection whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SampleSelection whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SampleSelection whereSlug($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SampleSelection whereSelectedSamples($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SampleSelection whereGeneratedFiles($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SampleSelection whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SampleSelection whereDatasetId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SampleSelection whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SampleSelection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SampleSelection whereUserId($value)
 * @mixin \Eloquent
 */
class SampleSelection extends Model
{

    const PENDING = 'pending';
    const READY = 'ready';
    const FAILED = 'failed';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'selected_samples' => 'array',
        'generated_files'  => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'selected_samples', 'generated_files', 'status', 'dataset_id', 'user_id'
    ];

    /**
     * Get all selections
     *
     * @return \Illuminate\Database\Query\Builder
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public static function listSelections()
    {
        /** @var \Illuminate\Database\Query\Builder $query */
        $query = self::whereStatus(self::READY);
        if (user_can(Permissions::VIEW_SELECTIONS) && !user_can(Permissions::ADMINISTER)) {
            $owner = current_user();
            $query->whereUserId($owner->id);
            return $query;
        } elseif (user_can(Permissions::VIEW_SELECTIONS) && user_can(Permissions::ADMINISTER)) {
            return $query;
        } else {
            return abort(401, 'You are not allowed to view selections.');
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
    public function dataset()
    {
        return $this->belongsTo('App\Models\Dataset', 'dataset_id', 'id');
    }

    /**
     * Return a file name for this object
     *
     * @param string $type
     * @param string $extension
     * @return string
     */
    public function getFileName($type, $extension)
    {
        return $this->slug . '-' . $type . '.' . $extension;
    }

    /**
     * Set the filename for metadata
     *
     * @param string $filename
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
        return (user_can(Permissions::REMOVE_SELECTIONS) && (user_can(Permissions::ADMINISTER) || $isOwned));
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
        return (user_can(Permissions::DOWNLOAD_SELECTIONS) && (user_can(Permissions::ADMINISTER) || $isOwned));
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
