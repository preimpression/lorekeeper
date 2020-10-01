<?php

namespace App\Http\Controllers\Research;

use Auth;

use App\Models\User\User;
use App\Models\Research\Tree;
use App\Models\Research\Research;
use App\Models\Currency\Currency;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Services\ResearchService;

class TreeController extends Controller
{


    /**
     * Trees are essentially Research Categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex()
    {
        return view('research.trees_index', [
            'trees' => Tree::where('is_active', 1)->orderBy('sort', 'DESC')->get(),
        ]);
    }

    
    /**
     * Shows a tree.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTree($id)
    {
        $tree = Tree::where('id', $id)->where('is_active', 1)->first();
        if(!$tree) abort(404);

        return view('research.tree', [
            'tree' => $tree,
            'trees' => Tree::where('is_active', 1)->orderBy('sort', 'DESC')->get(),
            'researches' => Research::where('tree_id','=',$tree->id)->where('parent_id','=',null)->orderBy('sort', 'DESC')->get(),
            'currencies' => Currency::find($tree->currency_id)
        ]);
    }

    /**
     * Shows a tree.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserTree($name = null)
    {
        $user = (isset($name) ? User::where('name',$name)->first() : Auth::user());

        if($name) $text = ['user','profile'];
        else $text = ['research','research'];

        $trees = Tree::where('is_active', 1)->orderBy('sort', 'DESC')->get();
        if(!$trees) abort(404);

        return view('research.user_trees', [
            'trees' => $trees,
            'user' => $user,
            'type' => $text
        ]);
    }

}