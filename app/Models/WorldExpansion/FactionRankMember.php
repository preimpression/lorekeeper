<?php

namespace App\Models\WorldExpansion;

use Illuminate\Database\Eloquent\Model;

use App\Models\User\User;

class FactionRankMember extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'faction_id', 'rank_id', 'member_type', 'member_id'
    ];


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'faction_rank_members';

    public $timestamps = false;


    /**********************************************************************************************

        RELATIONS
    **********************************************************************************************/

    /**
     * Get faction this member belongs to.
     */
    public function faction()
    {
        return $this->belongsTo('App\Models\WorldExpansion\Faction', 'faction_id');
    }

    /**
     * Get rank this member belongs to.
     */
    public function rank()
    {
        return $this->belongsTo('App\Models\WorldExpansion\FactionRank', 'rank_id');
    }

    /**********************************************************************************************

        ACCESSORS
    **********************************************************************************************/

}
