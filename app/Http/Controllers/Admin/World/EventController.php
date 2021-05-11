<?php

namespace App\Http\Controllers\Admin\World;

use App\Models\WorldExpansion\Location;
use App\Models\WorldExpansion\Faction;
use App\Models\WorldExpansion\Figure;
use App\Models\Item\Item;
use App\Models\Prompt\Prompt;
use App\Models\News;

use App\Models\WorldExpansion\Event;
use App\Models\WorldExpansion\EventFigure;
use App\Models\WorldExpansion\EventCategory;

use Auth;

use Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\WorldExpansion\EventService;

class EventController extends Controller
{


    /**********************************************************************************************

        Event Types

    **********************************************************************************************/

    /**
     * Shows the event category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEventCategories()
    {
        return view('admin.world_expansion.event_categories', [
            'categories' => EventCategory::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create event category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEventCategory()
    {
        return view('admin.world_expansion.create_edit_event_category', [
            'category' => new EventCategory
        ]);
    }

    /**
     * Shows the edit event category page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditEventCategory($id)
    {
        $category = EventCategory::find($id);
        if(!$category) abort(404);
        return view('admin.world_expansion.create_edit_event_category', [
            'category' => $category
        ]);
    }

    /**
     * Creates or edits a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\EventService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditEventCategory(Request $request, EventService $service, $id = null)
    {
        $id ? $request->validate(EventCategory::$updateRules) : $request->validate(EventCategory::$createRules);

        $data = $request->only([
            'name', 'names', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'summary'
        ]);
        if($id && $service->updateEventCategory(EventCategory::find($id), $data, Auth::user())) {
            flash('Event category updated successfully.')->success();
        }
        else if (!$id && $category = $service->createEventCategory($data, Auth::user())) {
            flash('Event category created successfully.')->success();
            return redirect()->to('admin/world/event-categories/edit/'.$category->id);
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
    public function getDeleteEventCategory($id)
    {
        $category = EventCategory::find($id);
        return view('admin.world_expansion._delete_event_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\EventService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteEventCategory(Request $request, EventService $service, $id)
    {
        if($id && $service->deleteEventCategory(EventCategory::find($id))) {
            flash('Event Category deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/event-categories');
    }

    /**
     * Sorts categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\EventService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortEventCategory(Request $request, EventService $service)
    {
        if($service->sortEventCategory($request->get('sort'))) {
            flash('Event Category order updated successfully.')->success();
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
     * Shows the event event index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEventIndex()
    {
        return view('admin.world_expansion.events', [
            'events' => Event::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create event event page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEvent()
    {
        return view('admin.world_expansion.create_edit_event', [
            'event' => new Event,
            'categories' => EventCategory::all()->pluck('name','id')->toArray(),
            'events' => Event::all()->pluck('name','id')->toArray(),
            'figures' => Figure::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
            'factions' => Faction::all()->pluck('name','id')->toArray(),
            'newses' => News::all()->pluck('title','id')->toArray(),
            'prompts' => Prompt::all()->pluck('name','id')->toArray(),
        ]);
    }

    /**
     * Shows the edit event event page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditEvent($id)
    {
        $event = Event::find($id);
        if(!$event) abort(404);
        return view('admin.world_expansion.create_edit_event', [
            'event' => $event,
            'categories' => EventCategory::all()->pluck('name','id')->toArray(),
            'events' => Event::all()->where('id','!=',$event->id)->pluck('name','id')->toArray(),
            'figures' => Figure::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
            'factions' => Faction::all()->pluck('name','id')->toArray(),
            'newses' => News::all()->pluck('title','id')->toArray(),
            'prompts' => Prompt::all()->pluck('name','id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\EventService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditEvent(Request $request, EventService $service, $id = null)
    {
        $id ? $request->validate(Event::$updateRules) : $request->validate(Event::$createRules);

        $data = $request->only([
            'name', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th',
            'is_active', 'summary', 'category_id', 'figure_id', 'location_id', 'faction_id', 'news_id', 'prompt_id',
            'occur_start', 'occur_end'
        ]);
        if($id && $service->updateEvent(Event::find($id), $data, Auth::user())) {
            flash('Event updated successfully.')->success();
        }
        else if (!$id && $event = $service->createEvent($data, Auth::user())) {
            flash('Event created successfully.')->success();
            return redirect()->to('admin/world/events/edit/'.$event->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the event deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteEvent($id)
    {
        $event = Event::find($id);
        return view('admin.world_expansion._delete_event', [
            'event' => $event,
        ]);
    }

    /**
     * Deletes a event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\EventService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteEvent(Request $request, EventService $service, $id)
    {
        if($id && $service->deleteEvent(Event::find($id))) {
            flash('Event deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/events');
    }

    /**
     * Sorts events.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\EventService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortEvent(Request $request, EventService $service)
    {
        if($service->sortEvent($request->get('sort'))) {
            flash('Event order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }



}
