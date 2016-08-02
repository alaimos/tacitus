<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SupportedSource
 *
 * @mixin \Eloquent
 * @property integer        $id
 * @property string         $name
 * @property string         $display_name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SupportedSource whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SupportedSource whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SupportedSource whereDisplayName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SupportedSource whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SupportedSource whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Dataset[] $datasets
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Platform[] $platforms
 */
class SupportedSource extends Model
{

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function datasets()
    {
        return $this->hasMany('App\Models\Dataset', 'source_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function platforms()
    {
        return $this->hasMany('App\Models\Platform', 'source_id', 'id');
    }

}
