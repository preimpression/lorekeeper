<?php

namespace App\Http\Controllers\Admin\Data;

use Auth;

use App\Models\Research\Tree;
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
        return view('admin.research.trees', [
            'trees' => Tree::orderBy('sort', 'DESC')->get()
        ]);
    }
    
    /**
     * Shows the create tree page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateTree()
    {
        return view('admin.research.create_edit_tree', [
            'tree' => new Tree,
            'currencies' => Currency::where('is_user_owned',1)->orderBy('name')->pluck('name', 'id'),
        ]);
    }
    
    /**
     * Shows the edit tree page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditTree($id)
    {
        $tree = Tree::find($id);
        if(!$tree) abort(404);
        return view('admin.research.create_edit_tree', [
            'tree' => $tree,
            'currencies' => Currency::where('is_user_owned',1)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Creates or edits a tree.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\TreeService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditTree(Request $request, ResearchService $service, $id = null)
    {
        $id ? $request->validate(Tree::$updateRules) : $request->validate(Tree::$createRules);

        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_active', 'summary', 'currency_id'
        ]);
        if($id && $service->updateTree(Tree::find($id), $data, Auth::user())) {
            flash('Research tree updated successfully.')->success();
        }
        else if (!$id && $tree = $service->createTree($data, Auth::user())) {
            flash('Research tree created successfully.')->success();
            return redirect()->to('admin/data/trees/edit/'.$tree->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the tree deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteTree($id)
    {
        $tree = Tree::find($id);
        return view('admin.research._delete_tree', [
            'tree' => $tree,
        ]);
    }

    /**
     * Deletes a tree.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\TreeService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteTree(Request $request, ResearchService $service, $id)
    {
        if($id && $service->deleteTree(Tree::find($id))) {
            flash('Tree deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/trees');
    }

    /**
     * Sorts trees.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\TreeService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortTree(Request $request, ResearchService $service)
    {
        if($service->sortTree($request->get('sort'))) {
            flash('Tree order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
