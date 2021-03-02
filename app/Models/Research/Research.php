<?php

namespace App\Models\Research;

use Config;
use DB;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User\User;
use App\Models\Research\Tree;

class Research extends Model
{

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'summary', 'parsed_description', 'sort','icon_code', 'tree_id',
        'parent_id', 'prerequisite_id', 'prereq_is_same', 'is_active', 'price', 'data'
    ];


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'researches';

    public $timestamps = false;

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|unique:researches|between:3,25',
        'description' => 'nullable',
        'summary' => 'nullable|max:300',
        'icon' => 'nullable|max:300',
        'tree_id' => 'required|integer',
        'prerequisite_id' => 'nullable|integer',
        'parent_id' => 'nullable|integer',
        'price' => 'integer',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'description' => 'nullable',
        'summary' => 'nullable|max:300',
        'icon' => 'nullable|max:300',
        'tree_id' => 'required|integer',
        'prerequisite_id' => 'nullable|integer',
        'parent_id' => 'nullable|integer',
        'price' => 'integer',
    ];


    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the research attached to this research.
     */
    public function tree()
    {
        return $this->belongsTo('App\Models\Research\Tree', 'tree_id');
    }

    /**
     * Get the research attached to this research.
     */
    public function parent()
    {
        return $this->belongsTo('App\Models\Research\Research', 'parent_id');
    }

    /**
     * Get the research attached to this research.
     */
    public function children()
    {
        return $this->hasMany('App\Models\Research\Research', 'parent_id');
    }

    /**
     * Get the research attached to this research.
     */
    public function prerequisite()
    {
        return $this->belongsTo('App\Models\Research\Research', 'prerequisite_id');
    }

    /**
     * Get the research attached to this research.
     */
    public function subrequisites()
    {
        return $this->hasMany('App\Models\Research\Research', 'prerequisite_id');
    }

    /**
     * Get the rewards attached to this research branch.
     */
    public function rewards()
    {
        return $this->hasMany('App\Models\Research\ResearchReward', 'research_id');
    }



    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the research research's name, linked to its purchase page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if($this->is_active) {return '<a href="'.$this->url.'" class="display-research">'.$this->name.'</a>';}
        else {return '<s><a href="'.$this->url.'" class="display-research text-muted">'.$this->name.'</a></s>';}
    }

    /**
     * Displays the research research's name, linked to its purchase page.
     *
     * @return string
     */
    public function getIconAttribute()
    {
        return '<i class=" mx-1 '.$this->icon_code.'"/></i>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/research';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getResearchImageFileNameAttribute()
    {
        return $this->image_url;
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getResearchImagePathAttribute()
    {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getResearchImageUrlAttribute()
    {
        if (!$this->image_url) return null;
        return asset($this->imageDirectory . '/' . $this->researchImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('research/'.$this->id);
    }

    /**
     * Gets the research branch's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute()
    {
        return 'researches';
    }


    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/



    /**
     * Scope a query to sort items in category order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortTree($query)
    {
        $ids = Tree::orderBy('sort', 'DESC')->pluck('id')->toArray();
        return count($ids) ? $query->orderByRaw(DB::raw('FIELD(tree_id, '.implode(',', $ids).')')) : $query;
    }
    /**
     * Scope a query to sort items in alphabetical order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool                                   $reverse
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false)
    {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort items by newest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query)
    {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort features oldest first.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query)
    {
        return $query->orderBy('id');
    }



}
