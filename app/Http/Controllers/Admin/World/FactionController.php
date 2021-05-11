<?php

namespace App\Http\Controllers\Admin\World;

use App\Models\WorldExpansion\Faction;
use App\Models\WorldExpansion\FactionType;
use App\Models\WorldExpansion\Figure;
use App\Models\WorldExpansion\Location;
use App\Models\User\User;
use App\Models\Character\Character;
use Auth;

use Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\WorldExpansion\FactionService;

class FactionController extends Controller
{


    /**********************************************************************************************

        FACTION TYPES

    **********************************************************************************************/

    /**
     * Shows the faction type index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.world_expansion.faction_types', [
            'types' => FactionType::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create faction type page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateFactionType()
    {
        return view('admin.world_expansion.create_edit_faction_type', [
            'type' => new FactionType
        ]);
    }

    /**
     * Shows the edit faction type page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditFactionType($id)
    {
        $type = FactionType::find($id);
        if(!$type) abort(404);
        return view('admin.world_expansion.create_edit_faction_type', [
            'type' => $type
        ]);
    }

    /**
     * Creates or edits a type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FactionService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditFactionType(Request $request, FactionService $service, $id = null)
    {
        $id ? $request->validate(FactionType::$updateRules) : $request->validate(FactionType::$createRules);

        $data = $request->only([
            'name', 'names', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'summary'
        ]);
        if($id && $service->updateFactionType(FactionType::find($id), $data, Auth::user())) {
            flash('Faction type updated successfully.')->success();
        }
        else if (!$id && $type = $service->createFactionType($data, Auth::user())) {
            flash('Faction type created successfully.')->success();
            return redirect()->to('admin/world/faction-types/edit/'.$type->id);
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
    public function getDeleteFactionType($id)
    {
        $type = FactionType::find($id);
        return view('admin.world_expansion._delete_faction_type', [
            'type' => $type,
        ]);
    }

    /**
     * Deletes a type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FactionService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteFactionType(Request $request, FactionService $service, $id)
    {
        if($id && $service->deleteFactionType(FactionType::find($id))) {
            flash('Faction Type deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/faction-types');
    }

    /**
     * Sorts types.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FactionService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortFactionType(Request $request, FactionService $service)
    {
        if($service->sortFactionType($request->get('sort'))) {
            flash('Faction Type order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**********************************************************************************************

        FACTIONS

    **********************************************************************************************/

    /**
     * Shows the faction faction index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFactionIndex()
    {
        return view('admin.world_expansion.factions', [
            'factions' => Faction::orderBy('sort', 'DESC')->get(),
            'types' => FactionType::orderBy('sort', 'DESC')->get()
        ]);
    }

    /**
     * Shows the create faction faction page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateFaction()
    {
        $types = FactionType::all()->pluck('name','id')->toArray();

        if(!count($types)) {
            flash('You need to create a faction type before you can create a faction.')->error();
            return redirect()->to('admin/world/faction-types/');
        }

        return view('admin.world_expansion.create_edit_faction', [
            'faction' => new Faction,
            'types' => $types,
            'factions' => Faction::all()->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
            'figures' => Figure::all()->pluck('name','id')->toArray(),
            'ch_enabled' => Settings::get('WE_character_factions'),
            'user_enabled' => Settings::get('WE_user_factions')
        ]);
    }

    /**
     * Shows the edit faction faction page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditFaction($id)
    {
        $faction = Faction::find($id);
        if(!$faction) abort(404);
        return view('admin.world_expansion.create_edit_faction', [
            'faction' => $faction,
            'types' => FactionType::all()->pluck('name','id')->toArray(),
            'factions' => Faction::all()->where('id','!=',$faction->id)->pluck('name','id')->toArray(),
            'locations' => Location::all()->pluck('name','id')->toArray(),
            'figures' => Figure::all()->pluck('name','id')->toArray(),
            'figureOptions' => Figure::all()->where('faction_id', $faction->id)->pluck('name','id')->toArray(),
            'users' => User::visible()->where('faction_id', $faction->id)->orderBy('name')->pluck('name', 'id')->toArray(),
            'characters' => Character::visible()->myo(0)->where('faction_id', $faction->id)->orderBy('sort','DESC')->get()->pluck('fullName','id')->toArray(),
            'ch_enabled' => Settings::get('WE_character_factions'),
            'user_enabled' => Settings::get('WE_user_factions')
        ]);
    }

    /**
     * Creates or edits a faction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FactionService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditFaction(Request $request, FactionService $service, $id = null)
    {
        $id ? $request->validate(Faction::$updateRules) : $request->validate(Faction::$createRules);

        $data = $request->only([
            'name', 'description', 'image', 'image_th', 'remove_image', 'remove_image_th', 'is_active', 'summary',
            'parent_id', 'type_id', 'user_faction', 'character_faction', 'style',
            'figure_id', 'location_id',
            'rank_name', 'rank_description', 'rank_sort', 'rank_is_open', 'rank_breakpoint', 'rank_amount', 'rank_member_type', 'rank_figure_id', 'rank_user_id', 'rank_character_id',
        ]);
        if($id && $service->updateFaction(Faction::find($id), $data, Auth::user())) {
            flash('Faction updated successfully.')->success();
        }
        else if (!$id && $faction = $service->createFaction($data, Auth::user())) {
            flash('Faction created successfully.')->success();
            return redirect()->to('admin/world/factions/edit/'.$faction->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the faction deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteFaction($id)
    {
        $faction = Faction::find($id);
        return view('admin.world_expansion._delete_faction', [
            'faction' => $faction,
        ]);
    }

    /**
     * Deletes a faction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FactionService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteFaction(Request $request, FactionService $service, $id)
    {
        if($id && $service->deleteFaction(Faction::find($id))) {
            flash('Faction deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/world/factions');
    }

    /**
     * Sorts factions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\WorldExpansion\FactionService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortFaction(Request $request, FactionService $service)
    {
        if($service->sortFaction($request->get('sort'))) {
            flash('Faction  order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
