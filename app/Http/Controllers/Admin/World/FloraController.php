<?php

namespace App\Http\Controllers\Admin\World;

use App\Models\WorldExpansion\Location;
use App\Models\Item\Item;

use App\Models\WorldExpansion\Flora;
use App\Models\WorldExpansion\FloraCategory;
use Auth;

use Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\WorldExpansion\NatureService;

class FloraController extends Controller
{


    /**********************************************************************************************

        Flora Types

    **********************************************************************************************/

    /**
     * Shows the flora category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFloraCategories()
    {
        return view('admin.world_expansion.flora_categories', [
            'categories' => FloraCategory::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create flora category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateFloraCategory()
    {
        return view('admin.world_expansion.create_edit_flora_category', [
            'category' => new FloraCategory
        ]);
    }

    /**
     * Shows the edit flora category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditFloraCategory($id)
    {
        $category = FloraCategory::find($id);
        if(!$category) abort(404);
        return view('admin.world_expansion.create_edit_flora_category', [
            'category' => $category
        ]);
    }

    /**
     * Creates or edits a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\NatureService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditFloraCategory(Request $request, NatureService $service, $id = null)
    {
        $id ? $request->validate(FloraCategory::$updateRules) : $request->validate(FloraCategory::$createRules);

        $data = $request->only([
            'name', 'names', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'summary'
        ]);
        if($id && $service->updateFloraCategory(FloraCategory::find($id), $data, Auth::user())) {
            flash('Flora category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createFloraCategory($data, Auth::user())) {
            flash('Flora category created successfully.')->success();
            return redirect()->to('admin/world/flora-categories/edit/'.$category->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the category deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteFloraCategory($id)
    {
        $category = FloraCategory::find($id);
        return view('admin.world_expansion._delete_flora_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\NatureService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteFloraCategory(Request $request, NatureService $service, $id)
    {
        if($id && $service->deleteFloraCategory(FloraCategory::find($id))) {
            flash('Flora Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/flora-categories');
    }

    /**
     * Sorts categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\NatureService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortFloraCategory(Request $request, NatureService $service)
    {
        if($service->sortFloraCategory($request->get('sort'))) {
            flash('Flora Category order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }





    /**********************************************************************************************

        FAUNA

    **********************************************************************************************/

    /**
     * Shows the flora flora index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFloraIndex()
    {
        return view('admin.world_expansion.floras', [
            'floras' => Flora::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create flora flora page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateFlora()
    {
        return view('admin.world_expansion.create_edit_flora', [
            'flora' => new Flora,
            'categories' => FloraCategory::all()->pluck('name','id')->toArray(),
            'floras' => Flora::all()->pluck('name','id')->toArray(),
            'items' => Item::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
        ]);
    }

    /**
     * Shows the edit flora flora page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditFlora($id)
    {
        $flora = Flora::find($id);
        if(!$flora) abort(404);
        return view('admin.world_expansion.create_edit_flora', [
            'flora' => $flora,
            'categories' => FloraCategory::all()->pluck('name','id')->toArray(),
            'floras' => Flora::all()->where('id','!=',$flora->id)->pluck('name','id')->toArray(),
            'items' => Item::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a flora.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\NatureService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditFlora(Request $request, NatureService $service, $id = null)
    {
        $id ? $request->validate(Flora::$updateRules) : $request->validate(Flora::$createRules);

        $data = $request->only([
            'name', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'is_active', 'summary', 'category_id', 'item_id', 'location_id', 'scientific_name'
        ]);

        if($id && $service->updateFlora(Flora::find($id), $data, Auth::user())) {
            flash('Flora updated successfully.')->success();
        }
        else if (!$id && $flora = $service->createFlora($data, Auth::user())) {
            flash('Flora created successfully.')->success();
            return redirect()->to('admin/world/floras/edit/'.$flora->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the flora deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteFlora($id)
    {
        $flora = Flora::find($id);
        return view('admin.world_expansion._delete_flora', [
            'flora' => $flora,
        ]);
    }

    /**
     * Deletes a flora.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\NatureService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteFlora(Request $request, NatureService $service, $id)
    {
        if($id && $service->deleteFlora(Flora::find($id))) {
            flash('Flora deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/floras');
    }

    /**
     * Sorts floras.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\NatureService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortFlora(Request $request, NatureService $service)
    {
        if($service->sortFlora($request->get('sort'))) {
            flash('Flora order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }



}
