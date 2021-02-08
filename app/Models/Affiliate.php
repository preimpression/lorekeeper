<?php

namespace App\Models;
use App\Models\Model;
use App\Models\User\User;

use App\Events\CommentCreated;
use App\Events\CommentUpdated;
use App\Events\CommentDeleted;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class Affiliate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_id', 'user_id', 'name', 'description',
        'is_featured', 'url', 'image_url', 'staff_comment',
        'status', 'guest_name', 'message', 'slug'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'affiliates';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;
    
    /**
     * Validation rules for submission creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'unique:affiliates|required|between:2,25',
        'url' => 'required|max:100|active_url',
        'icon_url' => 'nullable|max:100',
        'description' => 'nullable|max:100',
    ];
    
    /**
     * Validation rules for submission updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|between:2,25',
        'url' => 'required|max:100|active_url',
        'is_featured' => 'nullable|boolean',
        'icon_url' => 'nullable|max:100',
        'user_id' => 'nullable|integer',
        'staff_id' => 'nullable|integer',
        'staff_comments' => 'nullable|max:100',
    ];


    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/
   




    
    /**********************************************************************************************
    
        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include pending submissions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Pending');
    }
    /**
     * Scope a query to only include pending submissions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query, $num)
    {
        if($num == 0) return $query->where('is_featured', 0);
        else return $query->where('is_featured', 1);
    }


    /**
     * Scope a query to sort submissions oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to sort submissions by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    
    /**********************************************************************************************
    
        ACCESSORS

    **********************************************************************************************/

    /**
     * Determine who submitted the affiliate.
     */
    public function getSubmitterAttribute()
    {
        if(isset($this->user_id)) $user = User::find($this->user_id)->displayName;

        if(isset($user)) return $user;
        else return $this->guest_name;
    }
    
    /**
     * Determine who submitted the affiliate.
     */
    public function getStaffAttribute()
    {
        if(isset($this->staff_id)) $staff = User::find($this->staff_id)->displayName;

        if(isset($staff)) return $staff;
        else return "a staff member";
    }
    
    /**
     * Determine who submitted the affiliate.
     */
    public function getIconAttribute()
    {
        return '<a href="'. $this->url .'"><img src="'. (isset($this->image_url) ? $this->image_url : asset('images/affiliate.png')) .'" data-toggle="tooltip" title="<strong>'. $this->name .'</strong>'. ($this->description ? ' <br> '.$this->description : '') .'"class="m-1 avatar" /></a>';
    }

    public function getStatusUrlAttribute()
    {
        return url('/affiliates/status/').'/'.$this->slug;
    }



}
