<?php

namespace App\Http\Controllers\WorldExpansion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use Settings;

use App\Models\WorldExpansion\Flora;
use App\Models\WorldExpansion\FloraItem;
use App\Models\WorldExpansion\FloraCategory;
use App\Models\WorldExpansion\FloraLocation;

use App\Models\WorldExpansion\Fauna;
use App\Models\WorldExpansion\FaunaItem;
use App\Models\WorldExpansion\FaunaCategory;
use App\Models\WorldExpansion\FaunaLocation;

use App\Models\WorldExpansion\LocationType;

use App\Models\Item\ItemCategory;

class NatureController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Nature Controller
    |--------------------------------------------------------------------------
    |
    | This controller shows locations and their categories, as well as the
    | main World Info page created in the World Expansion extension.
    |
    */

    /**
     * Shows the faunas page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFaunaCategories(Request $request)
    {
        $query = FaunaCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('worldexpansion.fauna_categories', [
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query())

        ]);
    }

    /**
     * Shows the locations page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFaunaCategory($id)
    {
        $category = FaunaCategory::find($id);
        if(!$category) abort(404);

        return view('worldexpansion.fauna_category_page', [
            'category' => $category
        ]);
    }

    /**
     * Shows the locations page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFaunas(Request $request)
    {
        $query = Fauna::with('category');
        $data = $request->only(['category_id', 'name', 'sort']);
        if(isset($data['category_id']) && $data['category_id'] != 'none')
            $query->where('category_id', $data['category_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        if(isset($data['sort']))
        {
            switch($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
            }
        }
        else $query->sortCategory();

        if(!Auth::check() || !(Auth::check() && Auth::user()->isStaff)) $query->visible();

        return view('worldexpansion.faunas', [
            'faunas' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + FaunaCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Shows the locations page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFauna($id)
    {
        $fauna = Fauna::find($id);
        if(!$fauna || !$fauna->is_active && (!Auth::check() || !(Auth::check() && Auth::user()->isStaff))) abort(404);

        return view('worldexpansion.fauna_page', [
            'fauna' => $fauna,
            'fauna_categories'  => FaunaCategory::get(),
            'flora_categories'  => FloraCategory::get(),
            'item_categories'   => ItemCategory::get(),
            'location_types'     => LocationType::get(),
        ]);
    }



    /**
     * Shows the floras page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFloraCategories(Request $request)
    {
        $query = FloraCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('worldexpansion.flora_categories', [
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query())

        ]);
    }

    /**
     * Shows the locations page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFloraCategory($id)
    {
        $category = FloraCategory::find($id);
        if(!$category) abort(404);

        return view('worldexpansion.flora_category_page', [
            'category' => $category
        ]);
    }

    /**
     * Shows the locations page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFloras(Request $request)
    {
        $query = Flora::with('category');
        $data = $request->only(['category_id', 'name', 'sort']);
        if(isset($data['category_id']) && $data['category_id'] != 'none')
            $query->where('category_id', $data['category_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        if(isset($data['sort']))
        {
            switch($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
            }
        }
        else $query->sortCategory();

        if(!Auth::check() || !(Auth::check() && Auth::user()->isStaff)) $query->visible();

        return view('worldexpansion.floras', [
            'floras' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + FloraCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the locations page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFlora($id)
    {
        $flora = Flora::find($id);
        if(!$flora || !$flora->is_active && (!Auth::check() || !(Auth::check() && Auth::user()->isStaff))) abort(404);

        return view('worldexpansion.flora_page', [
            'flora' => $flora,
            'fauna_categories'  => FaunaCategory::get(),
            'flora_categories'  => FloraCategory::get(),
            'item_categories'   => ItemCategory::get(),
            'location_types'     => LocationType::get(),
        ]);
    }



}
