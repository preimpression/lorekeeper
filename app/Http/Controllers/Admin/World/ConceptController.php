<?php

namespace App\Http\Controllers\Admin\World;

use App\Models\WorldExpansion\Location;
use App\Models\Item\Item;

use App\Models\WorldExpansion\Concept;
use App\Models\WorldExpansion\ConceptCategory;
use Auth;

use Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\WorldExpansion\ConceptService;

class ConceptController extends Controller
{


    /**********************************************************************************************

        Concept Types

    **********************************************************************************************/

    /**
     * Shows the concept category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getConceptCategories()
    {
        return view('admin.world_expansion.concept_categories', [
            'categories' => ConceptCategory::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create concept category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateConceptCategory()
    {
        return view('admin.world_expansion.create_edit_concept_category', [
            'category' => new ConceptCategory
        ]);
    }

    /**
     * Shows the edit concept category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditConceptCategory($id)
    {
        $category = ConceptCategory::find($id);
        if(!$category) abort(404);
        return view('admin.world_expansion.create_edit_concept_category', [
            'category' => $category
        ]);
    }

    /**
     * Creates or edits a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\ConceptService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditConceptCategory(Request $request, ConceptService $service, $id = null)
    {
        $id ? $request->validate(ConceptCategory::$updateRules) : $request->validate(ConceptCategory::$createRules);

        $data = $request->only([
            'name', 'names', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'summary'
        ]);
        if($id && $service->updateConceptCategory(ConceptCategory::find($id), $data, Auth::user())) {
            flash('Concept category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createConceptCategory($data, Auth::user())) {
            flash('Concept category created successfully.')->success();
            return redirect()->to('admin/world/concept-categories/edit/'.$category->id);
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
    public function getDeleteConceptCategory($id)
    {
        $category = ConceptCategory::find($id);
        return view('admin.world_expansion._delete_concept_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\ConceptService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteConceptCategory(Request $request, ConceptService $service, $id)
    {
        if($id && $service->deleteConceptCategory(ConceptCategory::find($id))) {
            flash('Concept Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/concept-categories');
    }

    /**
     * Sorts categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\ConceptService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortConceptCategory(Request $request, ConceptService $service)
    {
        if($service->sortConceptCategory($request->get('sort'))) {
            flash('Concept Category order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**********************************************************************************************

        CONCEPTS

    **********************************************************************************************/

    /**
     * Shows the concept concept index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getConceptIndex()
    {
        return view('admin.world_expansion.concepts', [
            'concepts' => Concept::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create concept concept page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateConcept()
    {
        return view('admin.world_expansion.create_edit_concept', [
            'concept' => new Concept,
            'categories' => ConceptCategory::all()->pluck('name','id')->toArray(),
            'concepts' => Concept::all()->pluck('name','id')->toArray(),
            'items' => Item::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
        ]);
    }

    /**
     * Shows the edit concept concept page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditConcept($id)
    {
        $concept = Concept::find($id);
        if(!$concept) abort(404);
        return view('admin.world_expansion.create_edit_concept', [
            'concept' => $concept,
            'categories' => ConceptCategory::all()->pluck('name','id')->toArray(),
            'concepts' => Concept::all()->where('id','!=',$concept->id)->pluck('name','id')->toArray(),
            'items' => Item::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a concept.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\ConceptService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditConcept(Request $request, ConceptService $service, $id = null)
    {
        $id ? $request->validate(Concept::$updateRules) : $request->validate(Concept::$createRules);

        $data = $request->only([
            'name', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'is_active', 'summary', 'category_id', 'item_id', 'location_id', 'scientific_name'
        ]);

        if($id && $service->updateConcept(Concept::find($id), $data, Auth::user())) {
            flash('Concept updated successfully.')->success();
        }
        else if (!$id && $concept = $service->createConcept($data, Auth::user())) {
            flash('Concept created successfully.')->success();
            return redirect()->to('admin/world/concepts/edit/'.$concept->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the concept deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteConcept($id)
    {
        $concept = Concept::find($id);
        return view('admin.world_expansion._delete_concept', [
            'concept' => $concept,
        ]);
    }

    /**
     * Deletes a concept.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\ConceptService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteConcept(Request $request, ConceptService $service, $id)
    {
        if($id && $service->deleteConcept(Concept::find($id))) {
            flash('Concept deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/concepts');
    }

    /**
     * Sorts concepts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\ConceptService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortConcept(Request $request, ConceptService $service)
    {
        if($service->sortConcept($request->get('sort'))) {
            flash('Concept order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }



}
