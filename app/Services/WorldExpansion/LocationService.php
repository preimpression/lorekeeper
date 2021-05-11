<?php namespace App\Services\WorldExpansion;

use App\Services\Service;

use DB;
use Config;
use Auth;
use Notifications;

use App\Models\WorldExpansion\LocationType;
use App\Models\WorldExpansion\Location;

class LocationService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Location Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of locations and their types.
    |
    */


    /**
     * Creates a new location type.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Location\Type
     */
    public function createLocationType($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateLocationTypeData($data);

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

            $type = LocationType::create($data);

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
    public function updateLocationType($type, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(LocationType::where('name', $data['name'])->where('id', '!=', $type->id)->exists()) throw new \Exception("The singular name has already been taken.");
            if(LocationType::where('names', $data['names'])->where('id', '!=', $type->id)->exists()) throw new \Exception("The plural name has already been taken.");

            $data = $this->populateLocationTypeData($data, $type);

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
    public function deleteLocationType($type)
    {
        DB::beginTransaction();

        try {

            if(isset($type->image_extension)) $this->deleteImage($type->imagePath, $type->imageFileName);
            if(isset($type->thumb_extension)) $this->deleteImage($type->imagePath, $type->thumbFileName);
            if(count($type->locations)){
                foreach($type->locations as $location){
                    if(isset($location->image_extension)) $this->deleteImage($location->imagePath, $location->imageFileName);
                    if(isset($location->thumb_extension)) $this->deleteImage($location->imagePath, $location->thumbFileName);
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
    public function sortLocationType($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                LocationType::where('id', $s)->update(['sort' => $key]);
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
    private function populateLocationTypeData($data, $type = null)
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
    | Locations
    |--------------------------------------------------------------------------
    |
    */



    /**
     * Creates a new location.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Location\Type
     */
    public function createLocation($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateLocationData($data);

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

            $location = Location::create($data);

            if ($image) {
                $location->image_extension = $image->getClientOriginalExtension();
                $location->update();
                $this->handleImage($image, $location->imagePath, $location->imageFileName, null);
            }
            if ($image_th) {
                $location->thumb_extension = $image_th->getClientOriginalExtension();
                $location->update();
                $this->handleImage($image_th, $location->imagePath, $location->thumbFileName, null);
            }

            return $this->commitReturn($location);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a location.
     *
     * @param  \App\Models\WorldExpansion\Location  $location
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\WorldExpansion\Location
     */
    public function updateLocation($location, $data, $user)
    {

        DB::beginTransaction();

        try {
            // More specific validation
            if(Location::where('name', $data['name'])->where('id', '!=', $location->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateLocationData($data, $location);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                if(isset($location->image_extension)) $old = $location->imageFileName;
                else $old = null;
                $image = $data['image'];
                unset($data['image']);
            }
            if ($image) {
                $location->image_extension = $image->getClientOriginalExtension();
                $location->update();
                $this->handleImage($image, $location->imagePath, $location->imageFileName, $old);
            }

            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                if(isset($location->thumb_extension)) $old_th = $location->thumbFileName;
                else $old_th = null;
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            if ($image_th) {
                $location->thumb_extension = $image_th->getClientOriginalExtension();
                $location->update();
                $this->handleImage($image_th, $location->imagePath, $location->thumbFileName, $old_th);
            }

            $location->update($data);

            return $this->commitReturn($location);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Deletes a location.
     *
     * @param  \App\Models\WorldExpansion\Location  $location
     * @return bool
     */
    public function deleteLocation($location)
    {
        DB::beginTransaction();

        try {
            if($location && isset($location->image_extension)) $this->deleteImage($location->imagePath, $location->imageFileName);
            if($location && isset($location->thumb_extension)) $this->deleteImage($location->imagePath, $location->thumbFileName);
            $location->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a location.
     *
     * @param  array                  $data
     * @param  \App\Models\WorldExpansion\Location  $location
     * @return array
     */
    private function populateLocationData($data, $location = null)
    {

        $saveData['description'] = isset($data['description']) ? $data['description'] : null;
        if(isset($data['description']) && $data['description']) $saveData['parsed_description'] = parse($data['description']);
        $saveData['summary'] = isset($data['summary']) ? $data['summary'] : null;

        if(isset($data['name']) && $data['name']) $saveData['name'] = parse($data['name']);
        $saveData['is_active'] = isset($data['is_active']);

        $saveData['image'] = isset($data['image']) ? $data['image'] : null;
        $saveData['image_th'] = isset($data['image_th']) ? $data['image_th'] : null;

        $saveData['is_character_home'] = isset($data['character_home']);
        $saveData['is_user_home'] = isset($data['user_home']);

        $saveData['display_style'] = isset($data['style']) ? $data['style'] : 0 ;


        $saveData['type_id'] = $data['type_id'];
        $saveData['parent_id'] = $data['parent_id'];

        if(isset($data['remove_image']))
        {
            if($location && isset($location->image_extension) && $data['remove_image'])
            {
                $saveData['image_extension'] = null;
                $this->deleteImage($location->imagePath, $location->imageFileName);
            }
            unset($data['remove_image']);
        }

        if(isset($data['remove_image_th']) && $data['remove_image_th'])
        {
            if($location && isset($location->thumb_extension) && $data['remove_image_th'])
            {
                $saveData['thumb_extension'] = null;
                $this->deleteImage($location->imagePath, $location->thumbFileName);
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
    public function sortLocation($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Location::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }




}
