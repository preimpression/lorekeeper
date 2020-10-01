<?php

namespace App\Models\Research;

use Config;
use DB;
use App\Models\Model;

use Illuminate\Database\Eloquent\SoftDeletes;


class ResearchLog extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tree_id', 'research_id', 'data',
        'sender_id', 'recipient_id',
        'currency_id', 'cost'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_research_log';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who initiated the logged action.
     */
    public function sender() 
    {
        return $this->belongsTo('App\Models\User\User', 'sender_id');
    }

    /**
     * Get the user who received the logged action.
     */
    public function recipient() 
    {
        return $this->belongsTo('App\Models\User\User', 'recipient_id');
    }

    /**
     * Get the currency that is the target of the action.
     */
    public function currency() 
    {
        return $this->belongsTo('App\Models\Currency\Currency', 'currency_id');
    }

    /**
     * Get the currency that is the target of the action.
     */
    public function tree() 
    {
        return $this->belongsTo('App\Models\Research\Tree', 'tree_id');
    }

    /**
     * Get the currency that is the target of the action.
     */
    public function research() 
    {
        return $this->belongsTo('App\Models\Research\Research', 'research_id');
    }

}
