<?php namespace App\Services\WorldExpansion;

use App\Services\Service;

use DB;
use Config;
use Settings;
use Auth;
use Notifications;

use App\Models\Item\Item;

use App\Models\WorldExpansion\Fauna;
use App\Models\WorldExpansion\FaunaCategory;
use App\Models\WorldExpansion\FaunaItem;
use App\Models\WorldExpansion\FaunaLocation;

use App\Models\WorldExpansion\Flora;
use App\Models\WorldExpansion\FloraCategory;
use App\Models\WorldExpansion\FloraItem;
use App\Models\WorldExpansion\FloraLocation;

use App\Models\WorldExpansion\LocationType;
use App\Models\WorldExpansion\Location;

class NatureService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Nature Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of natural things like flora and fauna.
    |
    */


    /**
     * Creates a new fauna category.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Fauna\Category
     */
    public function createFaunaCategory($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateFaunaCategoryData($data);

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

            $category = FaunaCategory::create($data);

            if ($image) {
                $category->image_extension = $image->getClientOriginalExtension();
                $category->update();
                $this->handleImage($image, $category->imagePath, $category->imageFileName, null);
            }
            if ($image_th) {
                $category->thumb_extension = $image_th->getClientOriginalExtension();
                $category->update();
                $this->handleImage($image_th, $category->imagePath, $category->thumbFileName, null);
            }

            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a category.
     *
     * @param  \App\Models\Category\Category  $category
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Category\Category
     */
    public function updateFaunaCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(FaunaCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateFaunaCategoryData($data, $category);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                if(isset($category->image_extension)) $old = $category->imageFileName;
                else $old = null;
                $image = $data['image'];
                unset($data['image']);
            }
            if ($image) {
                $category->image_extension = $image->getClientOriginalExtension();
                $category->update();
                $this->handleImage($image, $category->imagePath, $category->imageFileName, $old);
            }

            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                if(isset($category->thumb_extension)) $old_th = $category->thumbFileName;
                else $old_th = null;
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            if ($image_th) {
                $category->thumb_extension = $image_th->getClientOriginalExtension();
                $category->update();
                $this->handleImage($image_th, $category->imagePath, $category->thumbFileName, $old_th);
            }
            $category->update($data);

            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }



    /**
     * Deletes a category.
     *
     * @param  \App\Models\Category\Category  $category
     * @return bool
     */
    public function deleteFaunaCategory($category)
    {
        DB::beginTransaction();

        try {
            if(isset($category->image_extension)) $this->deleteImage($category->imagePath, $category->imageFileName);
            if(isset($category->thumb_extension)) $this->deleteImage($category->imagePath, $category->thumbFileName);

            if(count($category->faunas)){
                foreach($category->faunas as $fauna){
                    if(isset($fauna->image_extension)) $this->deleteImage($fauna->imagePath, $fauna->imageFileName);
                    if(isset($fauna->thumb_extension)) $this->deleteImage($fauna->imagePath, $fauna->thumbFileName);
                }
            }

            $category->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortFaunaCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                FaunaCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a category.
     *
     * @param  array                  $data
     * @param  \App\Models\Category\Category  $category
     * @return array
     */
    private function populateFaunaCategoryData($data, $category = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        if(isset($data['name']) && $data['name']) $data['name'] = parse($data['name']);

        if(isset($data['remove_image']))
        {
            if($category && isset($category->image_extension) && $data['remove_image'])
            {
                $data['image_extension'] = null;
                $this->deleteImage($category->imagePath, $category->imageFileName);
            }
            unset($data['remove_image']);
        }

        if(isset($data['remove_image_th']) && $data['remove_image_th'])
        {
            if($category && isset($category->thumb_extension) && $data['remove_image_th'])
            {
                $data['thumb_extension'] = null;
                $this->deleteImage($category->imagePath, $category->thumbFileName);
            }
            unset($data['remove_image_th']);
        }


        return $data;
    }



    /*
    |--------------------------------------------------------------------------
    | Faunas
    |--------------------------------------------------------------------------
    |
    */



    /**
     * Creates a new fauna.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Fauna\Category
     */
    public function createFauna($data, $user)
    {

        DB::beginTransaction();

        try {
            $data = $this->populateFaunaData($data);

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

            $fauna = Fauna::create($data);

            if ($image) {
                $fauna->image_extension = $image->getClientOriginalExtension();
                $fauna->update();
                $this->handleImage($image, $fauna->imagePath, $fauna->imageFileName, null);
            }
            if ($image_th) {
                $fauna->thumb_extension = $image_th->getClientOriginalExtension();
                $fauna->update();
                $this->handleImage($image_th, $fauna->imagePath, $fauna->thumbFileName, null);
            }

            return $this->commitReturn($fauna);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a fauna.
     *
     * @param  \App\Models\WorldExpansion\Fauna  $fauna
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\WorldExpansion\Fauna
     */
    public function updateFauna($fauna, $data, $user)
    {

        DB::beginTransaction();

        try {
            // More specific validation
            if(Fauna::where('name', $data['name'])->where('id', '!=', $fauna->id)->exists()) throw new \Exception("The name has already been taken.");

            $fauna->timestamps = false;

            // Determine if there are items added.
            if(isset($data['item_id'])) {
                $data['item_id'] = array_unique($data['item_id']);
                $items = Item::whereIn('id', $data['item_id'])->get();
                if(count($items) != count($data['item_id'])) throw new \Exception("One or more of the selected items does not exist.");
            }
            else $items = [];

            // Remove all items from the fauna so they can be reattached with new data
            FaunaItem::where('fauna_id',$fauna->id)->delete();

            // Attach any items to the fauna
            foreach($items as $key=>$item) {
                FaunaItem::create([
                    'item_id' => $item->id,
                    'fauna_id' => $fauna->id,
                ]);
            }

            // Determine if there are locations added.
            if(isset($data['location_id'])) {
                $data['location_id'] = array_unique($data['location_id']);
                $locations = Location::whereIn('id', $data['location_id'])->get();
                if(count($locations) != count($data['location_id'])) throw new \Exception("One or more of the selected locations does not exist.");
            }
            else $locations = [];

            // Remove all locations from the fauna so they can be reattached with new data
            FaunaLocation::where('fauna_id',$fauna->id)->delete();

            // Attach any locations to the fauna
            foreach($locations as $key=>$location) {
                FaunaLocation::create([
                    'location_id' => $location->id,
                    'fauna_id' => $fauna->id,
                ]);
            }

            $fauna->timestamps = true;

            $data = $this->populateFaunaData($data, $fauna);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                if(isset($fauna->image_extension)) $old = $fauna->imageFileName;
                else $old = null;
                $image = $data['image'];
                unset($data['image']);
            }
            if ($image) {
                $fauna->image_extension = $image->getClientOriginalExtension();
                $fauna->update();
                $this->handleImage($image, $fauna->imagePath, $fauna->imageFileName, $old);
            }

            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                if(isset($fauna->thumb_extension)) $old_th = $fauna->thumbFileName;
                else $old_th = null;
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            if ($image_th) {
                $fauna->thumb_extension = $image_th->getClientOriginalExtension();
                $fauna->update();
                $this->handleImage($image_th, $fauna->imagePath, $fauna->thumbFileName, $old_th);
            }

            $fauna->update($data);

            return $this->commitReturn($fauna);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Deletes a fauna.
     *
     * @param  \App\Models\WorldExpansion\Fauna  $fauna
     * @return bool
     */
    public function deleteFauna($fauna)
    {
        DB::beginTransaction();

        try {
            if(isset($fauna->image_extension)) $this->deleteImage($fauna->imagePath, $fauna->imageFileName);
            if(isset($fauna->thumb_extension)) $this->deleteImage($fauna->imagePath, $fauna->thumbFileName);
            $fauna->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a fauna.
     *
     * @param  array                  $data
     * @param  \App\Models\WorldExpansion\Fauna  $fauna
     * @return array
     */
    private function populateFaunaData($data, $fauna = null)
    {

        $saveData['description'] = isset($data['description']) ? $data['description'] : null;
        if(isset($data['description']) && $data['description']) $saveData['parsed_description'] = parse($data['description']);
        $saveData['summary'] = isset($data['summary']) ? $data['summary'] : null;

        if(isset($data['name']) && $data['name']) $saveData['name'] = parse($data['name']);
        if(isset($data['scientific_name']) && $data['scientific_name']) $saveData['scientific_name'] = parse($data['scientific_name']);

        $saveData['is_active'] = isset($data['is_active']);
        $saveData['category_id'] = isset($data['category_id']) && $data['category_id'] ? $data['category_id'] : null;

        $saveData['image'] = isset($data['image']) ? $data['image'] : null;
        $saveData['image_th'] = isset($data['image_th']) ? $data['image_th'] : null;


        if(isset($data['remove_image']))
        {
            if($fauna && isset($fauna->image_extension) && $data['remove_image'])
            {
                $saveData['image_extension'] = null;
                $this->deleteImage($fauna->imagePath, $fauna->imageFileName);
            }
            unset($data['remove_image']);
        }

        if(isset($data['remove_image_th']) && $data['remove_image_th'])
        {
            if($fauna && isset($fauna->thumb_extension) && $data['remove_image_th'])
            {
                $saveData['thumb_extension'] = null;
                $this->deleteImage($fauna->imagePath, $fauna->thumbFileName);
            }
            unset($data['remove_image_th']);
        }
        return $saveData;
    }


    /**
     * Sorts category order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortFauna($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Fauna::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }




    /**
     * Creates a new flora category.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Flora\Category
     */
    public function createFloraCategory($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateFloraCategoryData($data);

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

            $category = FloraCategory::create($data);

            if ($image) {
                $category->image_extension = $image->getClientOriginalExtension();
                $category->update();
                $this->handleImage($image, $category->imagePath, $category->imageFileName, null);
            }
            if ($image_th) {
                $category->thumb_extension = $image_th->getClientOriginalExtension();
                $category->update();
                $this->handleImage($image_th, $category->imagePath, $category->thumbFileName, null);
            }

            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a category.
     *
     * @param  \App\Models\Category\Category  $category
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Category\Category
     */
    public function updateFloraCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(FloraCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateFloraCategoryData($data, $category);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                if(isset($category->image_extension)) $old = $category->imageFileName;
                else $old = null;
                $image = $data['image'];
                unset($data['image']);
            }
            if ($image) {
                $category->image_extension = $image->getClientOriginalExtension();
                $category->update();
                $this->handleImage($image, $category->imagePath, $category->imageFileName, $old);
            }

            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                if(isset($category->thumb_extension)) $old_th = $category->thumbFileName;
                else $old_th = null;
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            if ($image_th) {
                $category->thumb_extension = $image_th->getClientOriginalExtension();
                $category->update();
                $this->handleImage($image_th, $category->imagePath, $category->thumbFileName, $old_th);
            }
            $category->update($data);

            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }



    /**
     * Deletes a category.
     *
     * @param  \App\Models\Category\Category  $category
     * @return bool
     */
    public function deleteFloraCategory($category)
    {
        DB::beginTransaction();

        try {
            if(isset($category->image_extension)) $this->deleteImage($category->imagePath, $category->imageFileName);
            if(isset($category->thumb_extension)) $this->deleteImage($category->imagePath, $category->thumbFileName);
            if(count($category->floras)){
                foreach($category->floras as $flora){
                    if(isset($flora->image_extension)) $this->deleteImage($flora->imagePath, $flora->imageFileName);
                    if(isset($flora->thumb_extension)) $this->deleteImage($flora->imagePath, $flora->thumbFileName);
                }
            }
            $category->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortFloraCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                FloraCategory::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a category.
     *
     * @param  array                  $data
     * @param  \App\Models\Category\Category  $category
     * @return array
     */
    private function populateFloraCategoryData($data, $category = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        if(isset($data['name']) && $data['name']) $data['name'] = parse($data['name']);

        if(isset($data['remove_image']))
        {
            if($category && isset($category->image_extension) && $data['remove_image'])
            {
                $data['image_extension'] = null;
                $this->deleteImage($category->imagePath, $category->imageFileName);
            }
            unset($data['remove_image']);
        }

        if(isset($data['remove_image_th']) && $data['remove_image_th'])
        {
            if($category && isset($category->thumb_extension) && $data['remove_image_th'])
            {
                $data['thumb_extension'] = null;
                $this->deleteImage($category->imagePath, $category->thumbFileName);
            }
            unset($data['remove_image_th']);
        }


        return $data;
    }



    /*
    |--------------------------------------------------------------------------
    | Floras
    |--------------------------------------------------------------------------
    |
    */



    /**
     * Creates a new flora.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Flora\Category
     */
    public function createFlora($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateFloraData($data);

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

            $flora = Flora::create($data);

            if ($image) {
                $flora->image_extension = $image->getClientOriginalExtension();
                $flora->update();
                $this->handleImage($image, $flora->imagePath, $flora->imageFileName, null);
            }
            if ($image_th) {
                $flora->thumb_extension = $image_th->getClientOriginalExtension();
                $flora->update();
                $this->handleImage($image_th, $flora->imagePath, $flora->thumbFileName, null);
            }

            return $this->commitReturn($flora);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a flora.
     *
     * @param  \App\Models\WorldExpansion\Flora  $flora
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\WorldExpansion\Flora
     */
    public function updateFlora($flora, $data, $user)
    {

        DB::beginTransaction();

        try {
            // More specific validation
            if(Flora::where('name', $data['name'])->where('id', '!=', $flora->id)->exists()) throw new \Exception("The name has already been taken.");


            $flora->timestamps = false;

            // Determine if there are items added.
            if(isset($data['item_id'])) {
                $data['item_id'] = array_unique($data['item_id']);
                $items = Item::whereIn('id', $data['item_id'])->get();
                if(count($items) != count($data['item_id'])) throw new \Exception("One or more of the selected items does not exist.");
            }
            else $items = [];

            // Remove all items from the flora so they can be reattached with new data
            FloraItem::where('flora_id',$flora->id)->delete();

            // Attach any items to the flora
            foreach($items as $key=>$item) {
                FloraItem::create([
                    'item_id' => $item->id,
                    'flora_id' => $flora->id,
                ]);
            }

            // Determine if there are locations added.
            if(isset($data['location_id'])) {
                $data['location_id'] = array_unique($data['location_id']);
                $locations = Location::whereIn('id', $data['location_id'])->get();
                if(count($locations) != count($data['location_id'])) throw new \Exception("One or more of the selected locations does not exist.");
            }
            else $locations = [];

            // Remove all locations from the flora so they can be reattached with new data
            FloraLocation::where('flora_id',$flora->id)->delete();

            // Attach any locations to the flora
            foreach($locations as $key=>$location) {
                FloraLocation::create([
                    'location_id' => $location->id,
                    'flora_id' => $flora->id,
                ]);
            }

            $flora->timestamps = true;

            $data = $this->populateFloraData($data, $flora);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                if(isset($flora->image_extension)) $old = $flora->imageFileName;
                else $old = null;
                $image = $data['image'];
                unset($data['image']);
            }
            if ($image) {
                $flora->image_extension = $image->getClientOriginalExtension();
                $flora->update();
                $this->handleImage($image, $flora->imagePath, $flora->imageFileName, $old);
            }

            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                if(isset($flora->thumb_extension)) $old_th = $flora->thumbFileName;
                else $old_th = null;
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            if ($image_th) {
                $flora->thumb_extension = $image_th->getClientOriginalExtension();
                $flora->update();
                $this->handleImage($image_th, $flora->imagePath, $flora->thumbFileName, $old_th);
            }

            $flora->update($data);

            return $this->commitReturn($flora);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Deletes a flora.
     *
     * @param  \App\Models\WorldExpansion\Flora  $flora
     * @return bool
     */
    public function deleteFlora($flora)
    {
        DB::beginTransaction();

        try {
            if(isset($flora->image_extension)) $this->deleteImage($flora->imagePath, $flora->imageFileName);
            if(isset($flora->thumb_extension)) $this->deleteImage($flora->imagePath, $flora->thumbFileName);
            $flora->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a flora.
     *
     * @param  array                  $data
     * @param  \App\Models\WorldExpansion\Flora  $flora
     * @return array
     */
    private function populateFloraData($data, $flora = null)
    {

        $saveData['description'] = isset($data['description']) ? $data['description'] : null;
        if(isset($data['description']) && $data['description']) $saveData['parsed_description'] = parse($data['description']);
        $saveData['summary'] = isset($data['summary']) ? $data['summary'] : null;

        if(isset($data['scientific_name']) && $data['scientific_name']) $saveData['scientific_name'] = parse($data['scientific_name']);
        if(isset($data['name']) && $data['name']) $saveData['name'] = parse($data['name']);

        $saveData['is_active'] = isset($data['is_active']);
        $saveData['category_id'] = isset($data['category_id']) && $data['category_id'] ? $data['category_id'] : null;

        $saveData['image'] = isset($data['image']) ? $data['image'] : null;
        $saveData['image_th'] = isset($data['image_th']) ? $data['image_th'] : null;

        if(isset($data['remove_image']))
        {
            if($flora && isset($flora->image_extension) && $data['remove_image'])
            {
                $saveData['image_extension'] = null;
                $this->deleteImage($flora->imagePath, $flora->imageFileName);
            }
            unset($data['remove_image']);
        }

        if(isset($data['remove_image_th']) && $data['remove_image_th'])
        {
            if($flora && isset($flora->thumb_extension) && $data['remove_image_th'])
            {
                $saveData['thumb_extension'] = null;
                $this->deleteImage($flora->imagePath, $flora->thumbFileName);
            }
            unset($data['remove_image_th']);
        }

        return $saveData;
    }


    /**
     * Sorts category order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortFlora($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Flora::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
