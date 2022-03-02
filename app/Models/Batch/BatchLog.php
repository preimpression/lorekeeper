<?php

namespace App\Models\Batch;

use Config;

use Carbon\Carbon;

use App\Models\Model;

class BatchLog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'batch_name', 'batch_id', 'data', 'staff_id', 'created_at', 'updated_at'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'batch_logs';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'data' => 'required',
    ];

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
     * Get the staff member who manually triggered this batch.
     */
    public function staff()
    {
        return $this->belongsTo('App\Models\User\User', 'staff_id');
    }


    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the data attribute as an associative array.
     *
     * @return array
     */
    public function getDataAttribute()
    {
        return json_decode($this->attributes['data'], true);
    }


}
