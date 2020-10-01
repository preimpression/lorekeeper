<?php

namespace App\Models\Research;

use Illuminate\Database\Eloquent\Model;

use App\Models\User\User;

class Tree extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'summary', 'parsed_description', 'sort', 'image_url', 'currency_id', 'is_active'
    ];


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trees';
    
    public $timestamps = false;
    
    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|unique:trees|between:3,25',
        'description' => 'nullable',
        'summary' => 'nullable|max:300',
        'image' => 'mimes:png,gif,jpg,jpeg',
        'currency_id' => 'integer',
    ];
    
    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|between:3,25',
        'description' => 'nullable',
        'summary' => 'nullable|max:300',
        'image' => 'mimes:png,gif,jpg,jpeg',
        'currency_id' => 'integer',
    ];


    /**********************************************************************************************
    
        RELATIONS

    **********************************************************************************************/

    /**
     * Get the research attached to this tree.
     */
    public function researches() 
    {
        return $this->hasMany('App\Models\Research\Research', 'tree_id');
    }

    /**
     * Get the research attached to this tree.
     */
    public function currency() 
    {
        return $this->belongsTo('App\Models\Currency\Currency','currency_id');
    }

    

    /**********************************************************************************************
    
        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the research tree's name, linked to its purchase page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<a href="'.$this->url.'" class="display-tree">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute()
    {
        return 'images/data/trees';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getTreeImageFileNameAttribute()
    {
        return $this->image_url;
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getTreeImagePathAttribute()
    {
        return public_path($this->imageDirectory);
    }
    
    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getTreeImageUrlAttribute()
    {
        if (!$this->image_url) return null;
        return asset($this->imageDirectory . '/' . $this->treeImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('research-trees/'.$this->id);
    }


}
