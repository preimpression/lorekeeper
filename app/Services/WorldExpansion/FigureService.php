<?php namespace App\Services\WorldExpansion;

use App\Services\Service;

use DB;
use Config;
use Settings;
use Auth;
use Notifications;

use App\Models\Item\Item;

use App\Models\WorldExpansion\Event;
use App\Models\WorldExpansion\EventCategory;
use App\Models\WorldExpansion\EventFigure;
use App\Models\WorldExpansion\EventItem;

use App\Models\WorldExpansion\Figure;
use App\Models\WorldExpansion\FigureItem;
use App\Models\WorldExpansion\FigureCategory;

use App\Models\WorldExpansion\FactionRankMember;

class FigureService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Figure Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of natural things like figure and figure.
    |
    */


    /**
     * Creates a new figure category.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Figure\Category
     */
    public function createFigureCategory($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateFigureCategoryData($data);

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

            $category = FigureCategory::create($data);

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
    public function updateFigureCategory($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(FigureCategory::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateFigureCategoryData($data, $category);

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
    public function deleteFigureCategory($category)
    {
        DB::beginTransaction();

        try {
            if(isset($category->image_extension)) $this->deleteImage($category->imagePath, $category->imageFileName);
            if(isset($category->thumb_extension)) $this->deleteImage($category->imagePath, $category->thumbFileName);

            if(count($category->figures)){
                foreach($category->figures as $figure){
                    if(isset($figure->image_extension)) $this->deleteImage($figure->imagePath, $figure->imageFileName);
                    if(isset($figure->thumb_extension)) $this->deleteImage($figure->imagePath, $figure->thumbFileName);
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
    public function sortFigureCategory($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                FigureCategory::where('id', $s)->update(['sort' => $key]);
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
    private function populateFigureCategoryData($data, $category = null)
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
    | Figures
    |--------------------------------------------------------------------------
    |
    */



    /**
     * Creates a new figure.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Figure\Category
     */
    public function createFigure($data, $user)
    {

        DB::beginTransaction();

        try {
            $data = $this->populateFigureData($data);

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

            $figure = Figure::create($data);

            if ($image) {
                $figure->image_extension = $image->getClientOriginalExtension();
                $figure->update();
                $this->handleImage($image, $figure->imagePath, $figure->imageFileName, null);
            }
            if ($image_th) {
                $figure->thumb_extension = $image_th->getClientOriginalExtension();
                $figure->update();
                $this->handleImage($image_th, $figure->imagePath, $figure->thumbFileName, null);
            }

            return $this->commitReturn($figure);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a figure.
     *
     * @param  \App\Models\WorldExpansion\Figure  $figure
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\WorldExpansion\Figure
     */
    public function updateFigure($figure, $data, $user)
    {

        DB::beginTransaction();

        try {
            // More specific validation
            if(Figure::where('name', $data['name'])->where('id', '!=', $figure->id)->exists()) throw new \Exception("The name has already been taken.");

            $figure->timestamps = false;

            // Determine if there are items added.
            if(isset($data['item_id'])) {
                $data['item_id'] = array_unique($data['item_id']);
                $items = Item::whereIn('id', $data['item_id'])->get();
                if(count($items) != count($data['item_id'])) throw new \Exception("One or more of the selected items does not exist.");
            }
            else $items = [];

            // Remove all items from the figure so they can be reattached with new data
            FigureItem::where('figure_id',$figure->id)->delete();

            // Attach any items to the figure
            foreach($items as $key=>$item) {
                FigureItem::create([
                    'item_id' => $item->id,
                    'figure_id' => $figure->id,
                ]);
            }

            $figure->timestamps = true;

            $data = $this->populateFigureData($data, $figure);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                if(isset($figure->image_extension)) $old = $figure->imageFileName;
                else $old = null;
                $image = $data['image'];
                unset($data['image']);
            }
            if ($image) {
                $figure->image_extension = $image->getClientOriginalExtension();
                $figure->update();
                $this->handleImage($image, $figure->imagePath, $figure->imageFileName, $old);
            }

            $image_th = null;
            if(isset($data['image_th']) && $data['image_th']) {
                if(isset($figure->thumb_extension)) $old_th = $figure->thumbFileName;
                else $old_th = null;
                $image_th = $data['image_th'];
                unset($data['image_th']);
            }

            if ($image_th) {
                $figure->thumb_extension = $image_th->getClientOriginalExtension();
                $figure->update();
                $this->handleImage($image_th, $figure->imagePath, $figure->thumbFileName, $old_th);
            }

            // Remove from closed faction rank if applicable
            if(isset($data['faction_id']) && $data['faction_id']) {
                if($figure->faction_id && $figure->faction_id != $data['faction_id']){
                    if(FactionRankMember::where('member_type', 'figure')->where('member_id', $figure->id)->first()){
                        FactionRankMember::where('member_type', 'figure')->where('member_id', $figure->id)->first()->delete();}}
            }

            $figure->update($data);

            return $this->commitReturn($figure);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Deletes a figure.
     *
     * @param  \App\Models\WorldExpansion\Figure  $figure
     * @return bool
     */
    public function deleteFigure($figure)
    {
        DB::beginTransaction();

        try {
            if(isset($figure->image_extension)) $this->deleteImage($figure->imagePath, $figure->imageFileName);
            if(isset($figure->thumb_extension)) $this->deleteImage($figure->imagePath, $figure->thumbFileName);
            $figure->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a figure.
     *
     * @param  array                  $data
     * @param  \App\Models\WorldExpansion\Figure  $figure
     * @return array
     */
    private function populateFigureData($data, $figure = null)
    {

        $saveData['description'] = isset($data['description']) ? $data['description'] : null;
        if(isset($data['description']) && $data['description']) $saveData['parsed_description'] = parse($data['description']);
        $saveData['summary'] = isset($data['summary']) ? $data['summary'] : null;

        if(isset($data['name']) && $data['name']) $saveData['name'] = parse($data['name']);
        $saveData['is_active'] = isset($data['is_active']);
        $saveData['category_id'] = isset($data['category_id']) && $data['category_id'] ? $data['category_id'] : null;
        $saveData['faction_id'] = isset($data['faction_id']) && $data['faction_id'] ? $data['faction_id'] : null;

        $saveData['image'] = isset($data['image']) ? $data['image'] : null;
        $saveData['image_th'] = isset($data['image_th']) ? $data['image_th'] : null;

        $saveData['birth_date'] = isset($data['birth_date']) ? $data['birth_date'] : null;
        $saveData['death_date'] = isset($data['death_date']) ? $data['death_date'] : null;

        if(isset($data['remove_image']))
        {
            if($figure && isset($figure->image_extension) && $data['remove_image'])
            {
                $saveData['image_extension'] = null;
                $this->deleteImage($figure->imagePath, $figure->imageFileName);
            }
            unset($data['remove_image']);
        }

        if(isset($data['remove_image_th']) && $data['remove_image_th'])
        {
            if($figure && isset($figure->thumb_extension) && $data['remove_image_th'])
            {
                $saveData['thumb_extension'] = null;
                $this->deleteImage($figure->imagePath, $figure->thumbFileName);
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
    public function sortFigure($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Figure::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


}
