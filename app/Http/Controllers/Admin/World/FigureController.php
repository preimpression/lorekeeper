<?php

namespace App\Http\Controllers\Admin\World;

use App\Models\WorldExpansion\Location;
use App\Models\WorldExpansion\Figure;
use App\Models\WorldExpansion\FigureCategory;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\WorldExpansion\Faction;

use App\Models\WorldExpansion\Event;
use App\Models\WorldExpansion\EventFigure;
use App\Models\WorldExpansion\EventCategory;

use Auth;

use Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\WorldExpansion\FigureService;

class FigureController extends Controller
{


    /**********************************************************************************************

        Figure Types

    **********************************************************************************************/

    /**
     * Shows the figure category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFigureCategories()
    {
        return view('admin.world_expansion.figure_categories', [
            'categories' => FigureCategory::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create figure category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateFigureCategory()
    {
        return view('admin.world_expansion.create_edit_figure_category', [
            'category' => new FigureCategory
        ]);
    }

    /**
     * Shows the edit figure category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditFigureCategory($id)
    {
        $category = FigureCategory::find($id);
        if(!$category) abort(404);
        return view('admin.world_expansion.create_edit_figure_category', [
            'category' => $category
        ]);
    }

    /**
     * Creates or edits a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FigureService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditFigureCategory(Request $request, FigureService $service, $id = null)
    {
        $id ? $request->validate(FigureCategory::$updateRules) : $request->validate(FigureCategory::$createRules);

        $data = $request->only([
            'name', 'names', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'summary'
        ]);
        if($id && $service->updateFigureCategory(FigureCategory::find($id), $data, Auth::user())) {
            flash('Figure category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createFigureCategory($data, Auth::user())) {
            flash('Figure category created successfully.')->success();
            return redirect()->to('admin/world/figure-categories/edit/'.$category->id);
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
    public function getDeleteFigureCategory($id)
    {
        $category = FigureCategory::find($id);
        return view('admin.world_expansion._delete_figure_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FigureService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteFigureCategory(Request $request, FigureService $service, $id)
    {
        if($id && $service->deleteFigureCategory(FigureCategory::find($id))) {
            flash('Figure Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/figure-categories');
    }

    /**
     * Sorts categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FigureService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortFigureCategory(Request $request, FigureService $service)
    {
        if($service->sortFigureCategory($request->get('sort'))) {
            flash('Figure Category order updated successfully.')->success();
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
     * Shows the figure figure index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFigureIndex()
    {
        return view('admin.world_expansion.figures', [
            'figures' => Figure::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create figure figure page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateFigure()
    {
        return view('admin.world_expansion.create_edit_figure', [
            'figure' => new Figure,
            'categories' => FigureCategory::all()->pluck('name','id')->toArray(),
            'items' => Item::all()->pluck('name','id')->toArray(),
            'figures' => Figure::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
            'factions' => Faction::all()->pluck('name','id')->toArray(),
        ]);
    }

    /**
     * Shows the edit figure figure page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditFigure($id)
    {
        $figure = Figure::find($id);
        if(!$figure) abort(404);
        return view('admin.world_expansion.create_edit_figure', [
            'figure' => $figure,
            'categories' => FigureCategory::all()->pluck('name','id')->toArray(),
            'items' => Item::all()->pluck('name','id')->toArray(),
            'figures' => Figure::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
            'factions' => Faction::all()->pluck('name','id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a figure.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FigureService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditFigure(Request $request, FigureService $service, $id = null)
    {
        $id ? $request->validate(Figure::$updateRules) : $request->validate(Figure::$createRules);

        $data = $request->only([
            'name', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th',
            'is_active', 'summary', 'category_id', 'item_id', 'location_id',
            'birth_date', 'death_date', 'faction_id'
        ]);
        if($id && $service->updateFigure(Figure::find($id), $data, Auth::user())) {
            flash('Figure updated successfully.')->success();
        }
        else if (!$id && $figure = $service->createFigure($data, Auth::user())) {
            flash('Figure created successfully.')->success();
            return redirect()->to('admin/world/figures/edit/'.$figure->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the figure deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteFigure($id)
    {
        $figure = Figure::find($id);
        return view('admin.world_expansion._delete_figure', [
            'figure' => $figure,
        ]);
    }

    /**
     * Deletes a figure.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FigureService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteFigure(Request $request, FigureService $service, $id)
    {
        if($id && $service->deleteFigure(Figure::find($id))) {
            flash('Figure deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/figures');
    }

    /**
     * Sorts figures.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FigureService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortFigure(Request $request, FigureService $service)
    {
        if($service->sortFigure($request->get('sort'))) {
            flash('Figure order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }



}
