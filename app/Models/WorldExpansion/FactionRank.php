<?php

namespace App\Models\WorldExpansion;

use Illuminate\Database\Eloquent\Model;

use App\Models\User\User;

class FactionRank extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'faction_id', 'name', 'description', 'sort', 'is_open', 'amount', 'breakpoint'
    ];


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'faction_ranks';

    public $timestamps = false;


    /**********************************************************************************************

        RELATIONS
    **********************************************************************************************/

    /**
     * Get faction this rank belongs to.
     */
    public function faction()
    {
        return $this->belongsTo('App\Models\WorldExpansion\Faction', 'faction_id');
    }

    /**
     * Get members attached to this rank.
     */
    public function members()
    {
        return $this->hasMany('App\Models\WorldExpansion\FactionRankMember', 'rank_id');
    }

    /**********************************************************************************************

        ACCESSORS
    **********************************************************************************************/

    /**
     * Displays the faction rank's name, linked to its faction's page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<a href="'.$this->faction->url.'" class="display-location text-muted">'.$this->name.'</a>';
    }

}
