<?php namespace App\Services\WorldExpansion;

use App\Services\Service;

use DB;
use Config;
use Settings;
use Auth;
use Notifications;

use App\Models\Item\Item;

use App\Models\WorldExpansion\Concept;
use App\Models\WorldExpansion\ConceptCategory;
use App\Models\WorldExpansion\ConceptItem;
use App\Models\WorldExpansion\ConceptLocation;

use App\Models\WorldExpansion\Flora;
use App\Models\WorldExpansion\FloraCategory;
use App\Models\WorldExpansion\FloraItem;
use App\Models\WorldExpansion\FloraLocation;

use App\Models\WorldExpansion\LocationType;
use App\Models\WorldExpansion\Location;

class ConceptService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Concept Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of concepts.
    |
    */


    /**
     * Creates a new concept category.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Concept\Category
     */
    public function createConceptCategory($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateConceptCategoryData($data);

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

            $category = ConceptCategory::create($data);

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
    public function updateConceptCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(ConceptCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateConceptCategoryData($data, $category);

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
    public function deleteConceptCategory($category)
    {
        DB::beginTransaction();

        try {
            if(isset($category->image_extension)) $this->deleteImage($category->imagePath, $category->imageFileName);
            if(isset($category->thumb_extension)) $this->deleteImage($category->imagePath, $category->thumbFileName);

            if(count($category->concepts)){
                foreach($category->concepts as $concept){
                    if(isset($concept->image_extension)) $this->deleteImage($concept->imagePath, $concept->imageFileName);
                    if(isset($concept->thumb_extension)) $this->deleteImage($concept->imagePath, $concept->thumbFileName);
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
    public function sortConceptCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                ConceptCategory::where('id', $s)->update(['sort' => $key]);
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
    private function populateConceptCategoryData($data, $category = null)
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
    | Concepts
    |--------------------------------------------------------------------------
    |
    */



    /**
     * Creates a new concept.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Concept\Category
     */
    public function createConcept($data, $user)
    {

        DB::beginTransaction();

        try {
            $data = $this->populateConceptData($data);

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

            $concept = Concept::create($data);

            if ($image) {
                $concept->image_extension = $image->getClientOriginalExtension();
                $concept->update();
                $this->handleImage($image, $concept->imagePath, $concept->imageFileName, null);
            }
            if ($image_th) {
                $concept->thumb_extension = $image_th->getClientOriginalExtension();
                $concept->update();
                $this->handleImage($image_th, $concept->imagePath, $concept->thumbFileName, null);
            }

            return $this->commitReturn($concept);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a concept.
     *
     * @param  \App\Models\WorldExpansion\Concept  $concept
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\WorldExpansion\Concept
     */
    public function updateConcept($concept, $data, $user)
    {

        DB::beginTransaction();

        try {
            // More specific validation
            if(Concept::where('name', $data['name'])->where('id', '!=', $concept->id)->exists()) throw new \Exception("The name has already been taken.");

            $concept->timestamps = false;

            // Determine if there are items added.
            if(isset($data['item_id'])) {
                $data['item_id'] = array_unique($data['item_id']);
                $items = Item::whereIn('id', $data['item_id'])->get();
                if(count($items) != count($data['item_id'])) throw new \Exception("One or more of the selected items does not exist.");
            }
            else $items = [];

            // Remove all items from the concept so they can be reattached with new data
            ConceptItem::where('concept_id',$concept->id)->delete();

            // Attach any items to the concept
            foreach($items as $key=>$item) {
                ConceptItem::create([
                    'item_id' => $item->id,
                    'concept_id' => $concept->id,
                ]);
            }

            // Determine if there are locations added.
            if(isset($data['location_id'])) {
                $data['location_id'] = array_unique($data['location_id']);
                $locations = Location::whereIn('id', $data['location_id'])->get();
                if(count($locations) != count($data['location_id'])) throw new \Exception("One or more of the selected locations does not exist.");
            }
            else $locations = [];

            // Remove all locations from the concept so they can be reattached with new data
            ConceptLocation::where('concept_id',$concept->id)->delete();

            // Attach any locations to the concept
            foreach($locations as $key=>$location) {
                ConceptLocation::create([
                    'location_id' => $location->id,
                    'concept_id' => $concept->id,
                ]);
            }

            $concept->timestamps = true;

            $data = $this->populateConceptData($data, $concept);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                if(isset($concept->image_extension)) $old = $concept->imageFileName;
                else $old = null;
                $image = $data['image'];
                unset($data['image']);
            }
            if ($image) {
                $concept->image_extension = $image->getClientOriginalExtension();
                $concept->update();
                $this->handleImage($image, $concept->imagePath, $concept->imageFileName, $old);
            }

            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                if(isset($concept->thumb_extension)) $old_th = $concept->thumbFileName;
                else $old_th = null;
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            if ($image_th) {
                $concept->thumb_extension = $image_th->getClientOriginalExtension();
                $concept->update();
                $this->handleImage($image_th, $concept->imagePath, $concept->thumbFileName, $old_th);
            }

            $concept->update($data);

            return $this->commitReturn($concept);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Deletes a concept.
     *
     * @param  \App\Models\WorldExpansion\Concept  $concept
     * @return bool
     */
    public function deleteConcept($concept)
    {
        DB::beginTransaction();

        try {
            if(isset($concept->image_extension)) $this->deleteImage($concept->imagePath, $concept->imageFileName);
            if(isset($concept->thumb_extension)) $this->deleteImage($concept->imagePath, $concept->thumbFileName);
            $concept->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a concept.
     *
     * @param  array                  $data
     * @param  \App\Models\WorldExpansion\Concept  $concept
     * @return array
     */
    private function populateConceptData($data, $concept = null)
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
            if($concept && isset($concept->image_extension) && $data['remove_image'])
            {
                $saveData['image_extension'] = null;
                $this->deleteImage($concept->imagePath, $concept->imageFileName);
            }
            unset($data['remove_image']);
        }

        if(isset($data['remove_image_th']) && $data['remove_image_th'])
        {
            if($concept && isset($concept->thumb_extension) && $data['remove_image_th'])
            {
                $saveData['thumb_extension'] = null;
                $this->deleteImage($concept->imagePath, $concept->thumbFileName);
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
    public function sortConcept($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Concept::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
