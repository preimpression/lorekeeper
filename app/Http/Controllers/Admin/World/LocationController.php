<?php

namespace App\Http\Controllers\Admin\World;

use App\Models\WorldExpansion\Location;
use App\Models\WorldExpansion\LocationType;
use Auth;

use Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\WorldExpansion\LocationService;

class LocationController extends Controller
{


    /**********************************************************************************************

        LOCATION TYPES

    **********************************************************************************************/

    /**
     * Shows the location type index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.world_expansion.location_types', [
            'types' => LocationType::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create location type page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateLocationType()
    {
        return view('admin.world_expansion.create_edit_location_type', [
            'type' => new LocationType
        ]);
    }

    /**
     * Shows the edit location type page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLocationType($id)
    {
        $type = LocationType::find($id);
        if(!$type) abort(404);
        return view('admin.world_expansion.create_edit_location_type', [
            'type' => $type
        ]);
    }

    /**
     * Creates or edits a type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\LocationService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditLocationType(Request $request, LocationService $service, $id = null)
    {
        $id ? $request->validate(LocationType::$updateRules) : $request->validate(LocationType::$createRules);

        $data = $request->only([
            'name', 'names', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'summary'
        ]);
        if($id && $service->updateLocationType(LocationType::find($id), $data, Auth::user())) {
            flash('Location type updated successfully.')->success();
        }
        else if (!$id && $type = $service->createLocationType($data, Auth::user())) {
            flash('Location type created successfully.')->success();
            return redirect()->to('admin/world/location-types/edit/'.$type->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the type deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteLocationType($id)
    {
        $type = LocationType::find($id);
        return view('admin.world_expansion._delete_location_type', [
            'type' => $type,
        ]);
    }

    /**
     * Deletes a type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\LocationService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteLocationType(Request $request, LocationService $service, $id)
    {
        if($id && $service->deleteLocationType(LocationType::find($id))) {
            flash('Location Type deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/location-types');
    }

    /**
     * Sorts types.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\LocationService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortLocationType(Request $request, LocationService $service)
    {
        if($service->sortLocationType($request->get('sort'))) {
            flash('Location Type order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }





    /**********************************************************************************************

        LOCATIONS

    **********************************************************************************************/

    /**
     * Shows the location location index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLocationIndex()
    {
        return view('admin.world_expansion.locations', [
            'locations' => Location::orderBy('sort', 'DESC')->get(),
            'types' => LocationType::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create location location page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateLocation()
    {
        $types = LocationType::all()->pluck('name','id')->toArray();

        if(!count($types)) {
            flash('You need to create a faction type before you can create a faction.')->error();
            return redirect()->to('admin/world/faction-types/');
        }

        return view('admin.world_expansion.create_edit_location', [
            'location' => new Location,
            'types' => $types,
            'locations' => Location::all()->pluck('name','id')->toArray(),
            'ch_enabled' => Settings::get('WE_character_locations'),
            'user_enabled' => Settings::get('WE_user_locations')
        ]);
    }

    /**
     * Shows the edit location location page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLocation($id)
    {
        $location = Location::find($id);
        if(!$location) abort(404);
        return view('admin.world_expansion.create_edit_location', [
            'location' => $location,
            'types' => LocationType::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->where('id','!=',$location->id)->pluck('name','id')->toArray(),
            'ch_enabled' => Settings::get('WE_character_locations'),
            'user_enabled' => Settings::get('WE_user_locations')
        ]);
    }

    /**
     * Creates or edits a location.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\LocationService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditLocation(Request $request, LocationService $service, $id = null)
    {
        $id ? $request->validate(Location::$updateRules) : $request->validate(Location::$createRules);

        $data = $request->only([
            'name', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'is_active', 'summary',
            'parent_id', 'type_id', 'user_home', 'character_home', 'style'
        ]);
        if($id && $service->updateLocation(Location::find($id), $data, Auth::user())) {
            flash('Location updated successfully.')->success();
        }
        else if (!$id && $location = $service->createLocation($data, Auth::user())) {
            flash('Location created successfully.')->success();
            return redirect()->to('admin/world/locations/edit/'.$location->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the location deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteLocation($id)
    {
        $location = Location::find($id);
        return view('admin.world_expansion._delete_location', [
            'location' => $location,
        ]);
    }

    /**
     * Deletes a location.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\LocationService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteLocation(Request $request, LocationService $service, $id)
    {
        if($id && $service->deleteLocation(Location::find($id))) {
            flash('Location deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/locations');
    }

    /**
     * Sorts locations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\LocationService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortLocation(Request $request, LocationService $service)
    {
        if($service->sortLocation($request->get('sort'))) {
            flash('Location  order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }





























}
