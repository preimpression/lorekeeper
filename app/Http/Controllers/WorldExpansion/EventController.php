<?php

namespace App\Http\Controllers\WorldExpansion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use Settings;

use App\Models\WorldExpansion\Location;
use App\Models\WorldExpansion\LocationType;
use App\Models\WorldExpansion\Faction;
use App\Models\WorldExpansion\FactionType;
use App\Models\WorldExpansion\Figure;
use App\Models\WorldExpansion\FigureCategory;
use App\Models\Item\Item;
use App\Models\Prompt\Prompt;
use App\Models\Prompt\PromptCategory;

use App\Models\WorldExpansion\Event;
use App\Models\WorldExpansion\EventFigure;
use App\Models\WorldExpansion\EventCategory;
use App\Models\Item\ItemCategory;

class EventController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Event Controller
    |--------------------------------------------------------------------------
    |
    | This controller shows locations and their categories, as well as the
    | main World Info page created in the World Expansion extension.
    |
    */

    /**
     * Shows the events page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEventCategories(Request $request)
    {
        $query = EventCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('worldexpansion.event_categories', [
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query())

        ]);
    }

    /**
     * Shows the locations page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEventCategory($id)
    {
        $category = EventCategory::find($id);
        if(!$category) abort(404);

        return view('worldexpansion.event_category_page', [
            'category' => $category
        ]);
    }

    /**
     * Shows the locations page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEvents(Request $request)
    {
        $query = Event::with('category');
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

        return view('worldexpansion.events', [
            'events' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + EventCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the locations page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEvent($id)
    {
        $event = Event::find($id);
        if(!$event || !$event->is_active && (!Auth::check() || !(Auth::check() && Auth::user()->isStaff))) abort(404);

        return view('worldexpansion.event_page', [
            'event' => $event,
            'figure_categories'  => FigureCategory::get(),
            'location_types'  => LocationType::get(),
            'faction_types'  => FactionType::get(),
            'event_categories'  => EventCategory::get(),
            'prompt_categories'  => PromptCategory::get(),
        ]);
    }



}
