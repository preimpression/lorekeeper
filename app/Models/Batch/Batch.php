<?php

namespace App\Models\Batch;

use Config;

use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

use App\Models\Model;

class Batch extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'trigger_at'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'batches';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Dates on the model to convert to Carbon instances.
     *
     * @var array
     */
    public $dates = ['trigger_at'];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the targets for this batch.
     */
    public function targets()
    {
        return $this->hasMany('App\Models\Batch\BatchTarget', 'batch_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/


    /**
     * Scope a query to only include batches that should be triggered.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShouldBeTriggered($query)
    {
        return $query->whereNotNull('trigger_at')->where('trigger_at', '<', Carbon::now());
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<span class="display-batch">'.$this->name.'</span> '.add_help('This batch will trigger available or visibility of '.$this->targets->count().' target' . ($this->targets->count() == 1 ? '' : 's') . '.' );
    }

    /**
     * Gets the batch's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'batches';
    }


}
