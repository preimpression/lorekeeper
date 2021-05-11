<?php

namespace App\Http\Controllers\Admin\World;

use App\Models\WorldExpansion\Location;
use App\Models\Item\Item;

use App\Models\WorldExpansion\Fauna;
use App\Models\WorldExpansion\FaunaCategory;
use Auth;

use Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\WorldExpansion\NatureService;

class FaunaController extends Controller
{


    /**********************************************************************************************

        Fauna Types

    **********************************************************************************************/

    /**
     * Shows the fauna category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFaunaCategories()
    {
        return view('admin.world_expansion.fauna_categories', [
            'categories' => FaunaCategory::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create fauna category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateFaunaCategory()
    {
        return view('admin.world_expansion.create_edit_fauna_category', [
            'category' => new FaunaCategory
        ]);
    }

    /**
     * Shows the edit fauna category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditFaunaCategory($id)
    {
        $category = FaunaCategory::find($id);
        if(!$category) abort(404);
        return view('admin.world_expansion.create_edit_fauna_category', [
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
    public function postCreateEditFaunaCategory(Request $request, NatureService $service, $id = null)
    {
        $id ? $request->validate(FaunaCategory::$updateRules) : $request->validate(FaunaCategory::$createRules);

        $data = $request->only([
            'name', 'names', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'summary'
        ]);
        if($id && $service->updateFaunaCategory(FaunaCategory::find($id), $data, Auth::user())) {
            flash('Fauna category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createFaunaCategory($data, Auth::user())) {
            flash('Fauna category created successfully.')->success();
            return redirect()->to('admin/world/fauna-categories/edit/'.$category->id);
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
    public function getDeleteFaunaCategory($id)
    {
        $category = FaunaCategory::find($id);
        return view('admin.world_expansion._delete_fauna_category', [
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
    public function postDeleteFaunaCategory(Request $request, NatureService $service, $id)
    {
        if($id && $service->deleteFaunaCategory(FaunaCategory::find($id))) {
            flash('Fauna Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/fauna-categories');
    }

    /**
     * Sorts categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\NatureService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortFaunaCategory(Request $request, NatureService $service)
    {
        if($service->sortFaunaCategory($request->get('sort'))) {
            flash('Fauna Category order updated successfully.')->success();
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
     * Shows the fauna fauna index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFaunaIndex()
    {
        return view('admin.world_expansion.faunas', [
            'faunas' => Fauna::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create fauna fauna page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateFauna()
    {
        return view('admin.world_expansion.create_edit_fauna', [
            'fauna' => new Fauna,
            'categories' => FaunaCategory::all()->pluck('name','id')->toArray(),
            'faunas' => Fauna::all()->pluck('name','id')->toArray(),
            'items' => Item::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
        ]);
    }

    /**
     * Shows the edit fauna fauna page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditFauna($id)
    {
        $fauna = Fauna::find($id);
        if(!$fauna) abort(404);
        return view('admin.world_expansion.create_edit_fauna', [
            'fauna' => $fauna,
            'categories' => FaunaCategory::all()->pluck('name','id')->toArray(),
            'faunas' => Fauna::all()->where('id','!=',$fauna->id)->pluck('name','id')->toArray(),
            'items' => Item::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a fauna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\NatureService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditFauna(Request $request, NatureService $service, $id = null)
    {
        $id ? $request->validate(Fauna::$updateRules) : $request->validate(Fauna::$createRules);

        $data = $request->only([
            'name', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'is_active', 'summary', 'category_id', 'item_id', 'location_id', 'scientific_name'
        ]);

        if($id && $service->updateFauna(Fauna::find($id), $data, Auth::user())) {
            flash('Fauna updated successfully.')->success();
        }
        else if (!$id && $fauna = $service->createFauna($data, Auth::user())) {
            flash('Fauna created successfully.')->success();
            return redirect()->to('admin/world/faunas/edit/'.$fauna->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the fauna deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteFauna($id)
    {
        $fauna = Fauna::find($id);
        return view('admin.world_expansion._delete_fauna', [
            'fauna' => $fauna,
        ]);
    }

    /**
     * Deletes a fauna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\NatureService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteFauna(Request $request, NatureService $service, $id)
    {
        if($id && $service->deleteFauna(Fauna::find($id))) {
            flash('Fauna deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/faunas');
    }

    /**
     * Sorts faunas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\NatureService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortFauna(Request $request, NatureService $service)
    {
        if($service->sortFauna($request->get('sort'))) {
            flash('Fauna order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }



}
