<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\GalaxyCredential
 *
 * @property int                   $id
 * @property string                $name
 * @property string                $hostname
 * @property int                   $port
 * @property boolean               $use_https
 * @property string                $api_key
 * @property int                   $user_id
 * @property \Carbon\Carbon|null   $created_at
 * @property \Carbon\Carbon|null   $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GalaxyCredential whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GalaxyCredential whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GalaxyCredential whereHostname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GalaxyCredential whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GalaxyCredential whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GalaxyCredential wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GalaxyCredential whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GalaxyCredential whereUseHttps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\GalaxyCredential whereUserId($value)
 * @mixin \Eloquent
 */
class GalaxyCredential extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'use_https' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'hostname', 'port', 'use_https', 'api_key', 'user_id',
    ];

    /**
     * Get all credentials
     *
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public static function listCredentials($id = null)
    {
        $current = current_user();
        if ($current === null) {
            return abort(401, 'You are not allowed to list credentials.');
        }
        if ($id === null){
            $id = current_user()->id;
        }
        return self::whereUserId($id);
    }

    /**
     * Accessor for the API Key attribute
     *
     * @param string $value
     *
     * @return string
     */
    public function getApiKeyAttribute($value)
    {
        return decrypt($value);
    }

    /**
     * Mutator for the API Key attribute
     *
     * @param string $value
     *
     * @return string
     */
    public function setApiKeyAttribute($value)
    {
        return encrypt($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    /**
     * Checks if the current user can do any action on this object
     *
     * @return bool
     */
    public function userAllowed()
    {
        $current = current_user();
        return ($current !== null && $current->id == $this->user_id);
    }
}
