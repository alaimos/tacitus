<?php

namespace App\Models;

use App\Utils\Permissions;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MappedSampleSelection
 *
 * @property integer                          $id
 * @property string                           $status
 * @property array                            $generated_files
 * @property integer                          $selection_id
 * @property integer                          $platform_id
 * @property integer                          $mapping_id
 * @property integer                          $user_id
 * @property \Carbon\Carbon                   $created_at
 * @property \Carbon\Carbon                   $updated_at
 * @property-read \App\Models\SampleSelection $selection
 * @property-read \App\Models\User            $user
 * @property-read \App\Models\Platform        $platform
 * @property-read \App\Models\PlatformMapping $mapping
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappedSampleSelection whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappedSampleSelection whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappedSampleSelection whereGeneratedFiles($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappedSampleSelection whereSelectionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappedSampleSelection wherePlatformId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappedSampleSelection whereMappingId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappedSampleSelection whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappedSampleSelection whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\MappedSampleSelection whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MappedSampleSelection extends Model
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
        'generated_files' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'selection_id', 'platform_id', 'mapping_id', 'status', 'user_id', 'generated_files'
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
        $query = self::join('sample_selections', 'sample_selections.id', '=', 'mapped_sample_selections.selection_id')
            ->join('platform_mappings', 'platform_mappings.id', '=', 'mapped_sample_selections.mapping_id')
            ->join('platforms', 'platforms.id', '=', 'mapped_sample_selections.platform_id')
            ->where('mapped_sample_selections.status', '=', self::READY)
            ->select([
                'mapped_sample_selections.*',
                'platform_mappings.name AS mapping',
                'platforms.title AS platform',
                'platforms.organism AS organism',
                'sample_selections.name',
            ]);
        if (user_can(Permissions::USE_TOOLS) && !user_can(Permissions::ADMINISTER)) {
            $query->where('mapped_sample_selections.user_id', '=', current_user()->id);
            return $query;
        } elseif (user_can(Permissions::USE_TOOLS) && user_can(Permissions::ADMINISTER)) {
            return $query;
        } else {
            return abort(401, 'You are not allowed to view selections.');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function selection()
    {
        return $this->belongsTo('App\Models\SampleSelection', 'selection_id', 'id');
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
     * Returns the path of the storage directory
     *
     * @return string
     */
    public function getStorageDirectory()
    {
        $path = storage_path('app/selections/mapped/');
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
     * @return string
     */
    public function getFileName($type, $extension)
    {
        return $this->getStorageDirectory() . '/' . $this->id . '-mapped-' . $this->selection->slug . '-' . $type .
               '.' . $extension;
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
        return user_can(Permissions::USE_TOOLS) && $this->selection->canDelete();
    }

    /**
     * Checks if the current user can download this selection
     *
     * @return bool
     */
    public function canDownload()
    {
        return user_can(Permissions::USE_TOOLS) && $this->selection->canDownload();
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
