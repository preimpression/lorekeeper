<?php

namespace App\Http\Controllers\WorldExpansion;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Controller;
use Settings;

use App\Models\SitePage;
use App\Models\WorldExpansion\FaunaCategory;
use App\Models\WorldExpansion\FloraCategory;
use App\Models\WorldExpansion\EventCategory;
use App\Models\WorldExpansion\FigureCategory;
use App\Models\WorldExpansion\LocationType;
use App\Models\WorldExpansion\FactionType;
use App\Models\WorldExpansion\Faction;
use App\Models\Currency\Currency;

class FactionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Faction Controller
    |--------------------------------------------------------------------------
    |
    | This controller shows factions and their types.
    |
    */

    /**
     * Shows the factions page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getWorld()
    {
        $world = SitePage::where('key','world')->first();
        if(!$world) abort(404);

        return view('worldexpansion.world', [
            'world' => $world
        ]);
    }

    /**
     * Shows the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('world.index');
    }

    /**
     * Shows the faction types page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFactionTypes(Request $request)
    {
        $query = FactionType::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('worldexpansion.faction_types', [
            'types' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query())

        ]);
    }

    /**
     * Shows the factions page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFactionType($id)
    {
        $type = FactionType::where('is_active',1)->find($id);
        if(!$type) abort(404);

        return view('worldexpansion.faction_type_page', [
            'type' => $type
        ]);
    }

    /**
     * Shows the factions page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFactions(Request $request)
    {
        $query = Faction::with('type');
        $data = $request->only(['type_id', 'name', 'sort']);
        if(isset($data['type_id']) && $data['type_id'] != 'none')
            $query->where('type_id', $data['type_id']);
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
                case 'type':
                    $query->sortFactionType();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
            }
        }
        else $query->sortFactionType();

        return view('worldexpansion.factions', [
            'factions' => $query->paginate(20)->appends($request->query()),
            'types' => ['none' => 'Any Type'] + FactionType::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'loctypes' => FactionType::where('is_active',1)->get(),
            'user_enabled' => Settings::get('WE_user_factions'),
            'ch_enabled' => Settings::get('WE_character_factions')
        ]);
    }

    /**
     * Shows the factions page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFaction($id)
    {
        $faction = Faction::where('is_active',1)->find($id);
        if(!$faction) abort(404);

        return view('worldexpansion.faction_page', [
            'faction' => $faction,
            'user_enabled' => Settings::get('WE_user_factions'),
            'loctypes' => FactionType::where('is_active',1)->get(),
            'ch_enabled' => Settings::get('WE_character_factions'),
            'fauna_categories' => FaunaCategory::where('is_active',1)->get(),
            'flora_categories' => FloraCategory::where('is_active',1)->get(),
            'event_categories' => EventCategory::where('is_active',1)->get(),
            'figure_categories' => FigureCategory::where('is_active',1)->get(),
            'location_categories' => LocationType::where('is_active',1)->get(),
            'currency' => Currency::where('id', Settings::get('WE_faction_currency'))->first()
        ]);
    }

    /**
     * Shows a faction's members page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFactionMembers(Request $request, $id)
    {
        $faction = Faction::where('is_active', 1)->find($id);
        if(!$faction) abort(404);

        $members = $faction->factionMembers->sortByDesc(function ($members) {
            $standing = $members->getCurrencies(true)->where('id', Settings::get('WE_faction_currency'))->first();
            return $standing ? $standing->quantity : 0;
        })->sortBy(function ($members) {return $members->factionRank ? $members->factionRank->sort : 9999;});

        return view('worldexpansion.faction_members', [
            'faction' => $faction,
            'members' => $members->paginate(20),
            'currency' => Currency::where('id', Settings::get('WE_faction_currency'))->first()
        ]);
    }

}
