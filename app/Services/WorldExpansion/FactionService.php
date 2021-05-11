<?php namespace App\Services\WorldExpansion;

use App\Services\Service;

use DB;
use Config;
use Auth;
use Notifications;

use App\Models\WorldExpansion\FactionType;
use App\Models\WorldExpansion\Faction;
use App\Models\WorldExpansion\FactionRank;
use App\Models\WorldExpansion\FactionRankMember;
use App\Models\WorldExpansion\Figure;
use App\Models\WorldExpansion\Location;
use App\Models\WorldExpansion\FactionFigure;
use App\Models\WorldExpansion\FactionLocation;

use App\Models\User\User;
use App\Models\Character\Character;

class FactionService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Faction Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of factions and their types.
    |
    */


    /**
     * Creates a new faction type.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Faction\Type
     */
    public function createFactionType($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateFactionTypeData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }
            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            $type = FactionType::create($data);

            if ($image) {
                $type->image_extension = $image->getClientOriginalExtension();
                $type->update();
                $this->handleImage($image, $type->imagePath, $type->imageFileName, null);
            }
            if ($image_th) {
                $type->thumb_extension = $image_th->getClientOriginalExtension();
                $type->update();
                $this->handleImage($image_th, $type->imagePath, $type->thumbFileName, null);
            }

            return $this->commitReturn($type);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a type.
     *
     * @param  \App\Models\Type\Type  $type
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Type\Type
     */
    public function updateFactionType($type, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(FactionType::where('name', $data['name'])->where('id', '!=', $type->id)->exists()) throw new \Exception("The singular name has already been taken.");
            if(FactionType::where('names', $data['names'])->where('id', '!=', $type->id)->exists()) throw new \Exception("The plural name has already been taken.");

            $data = $this->populateFactionTypeData($data, $type);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                if(isset($type->image_extension)) $old = $type->imageFileName;
                else $old = null;
                $image = $data['image'];
                unset($data['image']);
            }
            if ($image) {
                $type->image_extension = $image->getClientOriginalExtension();
                $type->update();
                $this->handleImage($image, $type->imagePath, $type->imageFileName, $old);
            }

            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                if(isset($type->thumb_extension)) $old_th = $type->thumbFileName;
                else $old_th = null;
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            if ($image_th) {
                $type->thumb_extension = $image_th->getClientOriginalExtension();
                $type->update();
                $this->handleImage($image_th, $type->imagePath, $type->thumbFileName, $old_th);
            }
            $type->update($data);

            return $this->commitReturn($type);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }



    /**
     * Deletes a type.
     *
     * @param  \App\Models\Type\Type  $type
     * @return bool
     */
    public function deleteFactionType($type)
    {
        DB::beginTransaction();

        try {

            if(isset($type->image_extension)) $this->deleteImage($type->imagePath, $type->imageFileName);
            if(isset($type->thumb_extension)) $this->deleteImage($type->imagePath, $type->thumbFileName);
            if(count($type->factions)){
                foreach($type->factions as $faction){
                    if(isset($faction->image_extension)) $this->deleteImage($faction->imagePath, $faction->imageFileName);
                    if(isset($faction->thumb_extension)) $this->deleteImage($faction->imagePath, $faction->thumbFileName);
                }
            }
            $type->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts type order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortFactionType($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                FactionType::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a type.
     *
     * @param  array                  $data
     * @param  \App\Models\Type\Type  $type
     * @return array
     */
    private function populateFactionTypeData($data, $type = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        if(isset($data['name']) && $data['name']) $data['name'] = parse($data['name']);
        if(isset($data['names']) && $data['names']) $data['names'] = parse($data['names']);

        if(isset($data['remove_image']))
        {
            if($type && isset($type->image_extension) && $data['remove_image'])
            {
                $data['image_extension'] = null;
                $this->deleteImage($type->imagePath, $type->imageFileName);
            }
            unset($data['remove_image']);
        }

        if(isset($data['remove_image_th']) && $data['remove_image_th'])
        {
            if($type && isset($type->thumb_extension) && $data['remove_image_th'])
            {
                $data['thumb_extension'] = null;
                $this->deleteImage($type->imagePath, $type->thumbFileName);
            }
            unset($data['remove_image_th']);
        }


        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | Factions
    |--------------------------------------------------------------------------
    |
    */

    /**
     * Creates a new faction.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Faction\Type
     */
    public function createFaction($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateFactionData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }
            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            $faction = Faction::create($data);

            if ($image) {
                $faction->image_extension = $image->getClientOriginalExtension();
                $faction->update();
                $this->handleImage($image, $faction->imagePath, $faction->imageFileName, null);
            }
            if ($image_th) {
                $faction->thumb_extension = $image_th->getClientOriginalExtension();
                $faction->update();
                $this->handleImage($image_th, $faction->imagePath, $faction->thumbFileName, null);
            }

            return $this->commitReturn($faction);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a faction.
     *
     * @param  \App\Models\WorldExpansion\Faction  $faction
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\WorldExpansion\Faction
     */
    public function updateFaction($faction, $data, $user)
    {

        DB::beginTransaction();

        try {
            // More specific validation
            if(Faction::where('name', $data['name'])->where('id', '!=', $faction->id)->exists()) throw new \Exception("The name has already been taken.");

            /***************************************************** FACTION FIGURES ***************************************************************/
            // Determine if there are figures added.
            if(isset($data['figure_id'])) {
                $data['figure_id'] = array_unique($data['figure_id']);
                $figures = Figure::whereIn('id', $data['figure_id'])->get();
                if(count($figures) != count($data['figure_id'])) throw new \Exception("One or more of the selected figures does not exist.");
            }
            else $figures = [];

            // Remove all figures from the event so they can be reattached with new data
            FactionFigure::where('faction_id', $faction->id)->delete();

            // Attach any figures to the event
            foreach($figures as $key=>$figure) {
                FactionFigure::create([
                    'figure_id' => $figure->id,
                    'faction_id' => $faction->id,
                ]);
            }

            /***************************************************** FACTION LOCATIONS ***************************************************************/
            // Determine if there are locations added.
            if(isset($data['location_id'])) {
                $data['location_id'] = array_unique($data['location_id']);
                $locations = Location::whereIn('id', $data['location_id'])->get();
                if(count($locations) != count($data['location_id'])) throw new \Exception("One or more of the selected locations does not exist.");
            }
            else $locations = [];

            // Remove all locations from the event so they can be reattached with new data
            FactionLocation::where('faction_id', $faction->id)->delete();

            // Attach any locations to the event
            foreach($locations as $key=>$location) {
                FactionLocation::create([
                    'location_id' => $location->id,
                    'faction_id' => $faction->id,
                ]);
            }

            /***************************************************** FACTION RANKS ***************************************************************/

            // Delete old rank members, then ranks
            foreach($faction->ranks as $rank) $rank->members()->delete();
            if($faction->ranks()->count()) $oldRanks = $faction->ranks()->pluck('id')->toArray();
            $faction->ranks()->delete();

            if(isset($data['rank_name'])) {
                foreach($data['rank_name'] as $key=>$rankName) {
                    // Validate input
                    if($data['rank_is_open'][$key] && $data['rank_breakpoint'][$key] == null) throw new \Exception('Open ranks must have a breakpoint.');
                    elseif(!$data['rank_is_open'][$key] && $data['rank_amount'][$key] < 1) throw new \Exception('Closed ranks must have at least one available position.');

                    // Create rank
                    $ranks[$key] = FactionRank::create([
                        'faction_id' => $faction->id,
                        'sort' => $data['rank_sort'][$key],
                        'name' => $rankName,
                        'description' => $data['rank_description'][$key],
                        'is_open' => $data['rank_is_open'][$key],
                        'breakpoint' => $data['rank_is_open'][$key] ? $data['rank_breakpoint'][$key] : null,
                        'amount' => $data['rank_is_open'][$key] ? null : $data['rank_amount'][$key]
                    ]);

                    if(isset($oldRanks) && isset($oldRanks[$key])) $rankKey = $oldRanks[$key];
                    else $rankKey = null;

                    // Add members if set
                    if(isset($data['rank_member_type'][$rankKey])) {
                        foreach($data['rank_member_type'][$rankKey] as $memberKey=>$memberType) {
                            // Validate input
                            if($memberType == 'figure' && !isset($data['rank_figure_id'][$rankKey][$memberKey])) throw new \Exception('Please select a member figure.');
                            elseif($memberType == 'user' && !isset($data['rank_user_id'][$rankKey][$memberKey])) throw new \Exception('Please select a member user.');
                            elseif($memberType == 'character' && !isset($data['rank_character_id'][$rankKey][$memberKey])) throw new \Exception('Please select a member character.');

                            if($memberType) {
                                if($memberType == 'figure') {
                                    $memberId = $data['rank_figure_id'][$rankKey][$memberKey];
                                    if(Figure::where('id', $memberId)->first()->faction_id != $faction->id) throw new \Exception('One or more selected figures are not part of this faction.');
                                }
                                elseif($memberType == 'user') {
                                    $memberId = $data['rank_user_id'][$rankKey][$memberKey];
                                    if(User::where('id', $memberId)->first()->faction_id != $faction->id) throw new \Exception('One or more selected users are not part of this faction.');
                                }
                                elseif($memberType == 'character') {
                                    $memberId = $data['rank_character_id'][$rankKey][$memberKey];
                                    if(Character::where('id', $memberId)->first()->faction_id != $faction->id) throw new \Exception('One or more selected characters are not part of this faction.');
                                }

                                // Add members
                                $rankMembers[$rankKey][] = FactionRankMember::create([
                                    'faction_id' => $faction->id,
                                    'rank_id' => $ranks[$key]->id,
                                    'member_type' => $memberType,
                                    'member_id' => $memberId
                                ]);
                            }

                            if(isset($rankMembers[$rankKey]) && count($rankMembers[$rankKey]) == $ranks[$key]->amount) break;
                        }
                    }
                }

                foreach($ranks as $rank) if(FactionRank::where('faction_id', $faction->id)->where('is_open', 1)->where('breakpoint', $rank->breakpoint)->where('id', '!=', $rank->id)->exists()) throw new \Exception("Rank breakpoints must be unique within the same faction.");

                if(isset($rankMembers) && isset($rankMembers[$rankKey])) foreach($rankMembers[$rankKey] as $member) if(FactionRankMember::where('member_type', $member->member_type)->where('member_id', $member->member_id)->where('id', '!=', $member->id)->exists()) throw new \Exception("Can't add the same member to two different positions!");
            }

            $data = $this->populateFactionData($data, $faction);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                if(isset($faction->image_extension)) $old = $faction->imageFileName;
                else $old = null;
                $image = $data['image'];
                unset($data['image']);
            }
            if ($image) {
                $faction->image_extension = $image->getClientOriginalExtension();
                $faction->update();
                $this->handleImage($image, $faction->imagePath, $faction->imageFileName, $old);
            }

            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                if(isset($faction->thumb_extension)) $old_th = $faction->thumbFileName;
                else $old_th = null;
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            if ($image_th) {
                $faction->thumb_extension = $image_th->getClientOriginalExtension();
                $faction->update();
                $this->handleImage($image_th, $faction->imagePath, $faction->thumbFileName, $old_th);
            }

            $faction->update($data);

            return $this->commitReturn($faction);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Deletes a faction.
     *
     * @param  \App\Models\WorldExpansion\Faction  $faction
     * @return bool
     */
    public function deleteFaction($faction)
    {
        DB::beginTransaction();

        try {
            if($faction && isset($faction->image_extension)) $this->deleteImage($faction->imagePath, $faction->imageFileName);
            if($faction && isset($faction->thumb_extension)) $this->deleteImage($faction->imagePath, $faction->thumbFileName);
            $faction->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a faction.
     *
     * @param  array                  $data
     * @param  \App\Models\WorldExpansion\Faction  $faction
     * @return array
     */
    private function populateFactionData($data, $faction = null)
    {

        $saveData['description'] = isset($data['description']) ? $data['description'] : null;
        if(isset($data['description']) && $data['description']) $saveData['parsed_description'] = parse($data['description']);
        $saveData['summary'] = isset($data['summary']) ? $data['summary'] : null;

        if(isset($data['name']) && $data['name']) $saveData['name'] = parse($data['name']);
        $saveData['is_active'] = isset($data['is_active']);

        $saveData['image'] = isset($data['image']) ? $data['image'] : null;
        $saveData['image_th'] = isset($data['image_th']) ? $data['image_th'] : null;

        $saveData['is_character_faction'] = isset($data['character_faction']);
        $saveData['is_user_faction'] = isset($data['user_faction']);

        $saveData['display_style'] = isset($data['style']) ? $data['style'] : 0 ;

        $saveData['type_id'] = $data['type_id'];
        $saveData['parent_id'] = $data['parent_id'];

        if(isset($data['remove_image']))
        {
            if($faction && isset($faction->image_extension) && $data['remove_image'])
            {
                $saveData['image_extension'] = null;
                $this->deleteImage($faction->imagePath, $faction->imageFileName);
            }
            unset($data['remove_image']);
        }

        if(isset($data['remove_image_th']) && $data['remove_image_th'])
        {
            if($faction && isset($faction->thumb_extension) && $data['remove_image_th'])
            {
                $saveData['thumb_extension'] = null;
                $this->deleteImage($faction->imagePath, $faction->thumbFileName);
            }
            unset($data['remove_image_th']);
        }

        return $saveData;
    }


    /**
     * Sorts type order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortFaction($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Faction::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }




}
