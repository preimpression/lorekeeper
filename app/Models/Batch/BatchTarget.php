<?php

namespace App\Models\Batch;

use Config;
use App\Models\Model;

class BatchTarget extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'batch_id', 'target_type', 'target_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'batch_targets';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'target_type' => 'required',
        'target_id' => 'required',
        'batch_id' => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'target_type' => 'required',
        'target_id' => 'required',
        'batch_id' => 'required',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the reward attached to the loot entry.
     */
    public function reward()
    {
        switch ($this->target_type)
        {
            case 'Item':
                return $this->belongsTo('App\Models\Item\Item', 'batch_id');
            case 'Shop':
                return $this->belongsTo('App\Models\Shop\Shop', 'batch_id');
            case 'Prompt':
                return $this->belongsTo('App\Models\Prompt\Prompt', 'batch_id');
            case 'News':
                return $this->belongsTo('App\Models\News', 'batch_id');
            case 'Sales':
                return $this->belongsTo('App\Models\Sales', 'batch_id');
            case 'SitePage':
                return $this->belongsTo('App\Models\SitePage', 'batch_id');
            case 'Raffle':
                return $this->belongsTo('App\Models\Raffle\Raffle', 'batch_id');
            case 'Gallery':
                return $this->belongsTo('App\Models\Gallery\Gallery', 'batch_id');

            // World Expansion - Uncomment these if you have this extension and want them to be included.
            case 'Location':
                return $this->belongsTo('App\Models\WorldExpansion\Location', 'batch_id');
            case 'Event':
                return $this->belongsTo('App\Models\WorldExpansion\Event', 'batch_id');
            case 'Fauna':
                return $this->belongsTo('App\Models\WorldExpansion\Fauna', 'batch_id');
            case 'Flora':
                return $this->belongsTo('App\Models\WorldExpansion\Flora', 'batch_id');
            case 'Faction':
                return $this->belongsTo('App\Models\WorldExpansion\Faction', 'batch_id');
            case 'Concept':
                return $this->belongsTo('App\Models\WorldExpansion\Concept', 'batch_id');
            case 'Figure':
                return $this->belongsTo('App\Models\WorldExpansion\Figure', 'batch_id');


            case 'None':
                // Laravel requires a relationship instance to be returned (cannot return null), so returning one that doesn't exist here.
                return $this->belongsTo('App\Models\Batch\BatchTarget', 'target_id', 'batch_id')->whereNull('batch_id');
        }
        return null;
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/



}
