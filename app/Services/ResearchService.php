<?php namespace App\Services;

use App\Services\Service;

use Arr;
use DB;
use Config;
use Notifications;

use App\Models\User\User;
use App\Models\User\UserResearch;
use App\Models\Research\Tree;
use App\Models\Research\Research;
use App\Models\Research\ResearchReward;
use App\Models\Research\ResearchLog;

use App\Services\ResearchManager;

class ResearchService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Research Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of trees and researches.
    |
    */


    /**
     * Creates a new research tere.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Research\Tree
     */
    public function createTree($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateTreeData($data);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $image = $data['image'];
                unset($data['image']);
            }

            $tree = Tree::create($data);

            if ($image) {
                $tree->image_url = $tree->id . '-image.' . $image->getClientOriginalExtension();
                $tree->update();
                $this->handleImage($image, $tree->treeImagePath, $tree->treeImageFileName, null);
            }

            return $this->commitReturn($tree);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a tree.
     *
     * @param  \App\Models\Tree\Tree  $tree
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Tree\Tree
     */
    public function updateTree($tree, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(Tree::where('name', $data['name'])->where('id', '!=', $tree->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateTreeData($data, $tree);

            $image = null;
            if(isset($data['image']) && $data['image']) {
                $old = $tree->image_url;
                $image = $data['image'];
                unset($data['image']);
            }


            if ($image) {
                $tree->image_url = $tree->id . '-image.' . $image->getClientOriginalExtension();
                $tree->update();
                $this->handleImage($image, $tree->treeImagePath, $tree->treeImageFileName, $old);
            }
            $tree->update($data);

            return $this->commitReturn($tree);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }



    /**
     * Deletes a tree.
     *
     * @param  \App\Models\Tree\Tree  $tree
     * @return bool
     */
    public function deleteTree($tree)
    {
        DB::beginTransaction();

        try {

            if($tree->has_image) $this->deleteImage($tree->treeImagePath, $tree->treeImageFileName);
            $tree->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts tree order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortTree($data)
    {
        DB::beginTransaction();

        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));

            foreach($sort as $key => $s) {
                Tree::where('id', $s)->update(['sort' => $key]);
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a tree.
     *
     * @param  array                  $data
     * @param  \App\Models\Tree\Tree  $tree
     * @return array
     */
    private function populateTreeData($data, $tree = null)
    {
        if(isset($data['description']) && $data['description']) $data['parsed_description'] = parse($data['description']);
        $data['is_active'] = isset($data['is_active']);

        $data['summary'] = isset($data['summary']) ? $data['summary'] : null;

        if(isset($data['remove_image']))
        {
            if($tree && isset($tree->image_url) && $data['remove_image'])
            {
                $data['image_url'] = null;
                $this->deleteImage($tree->treeImagePath, $tree->treeImageFileName);
            }
            unset($data['remove_image']);
        }

        return $data;
    }



    /*
    |--------------------------------------------------------------------------
    | Research Branches
    |--------------------------------------------------------------------------
    |
    | Research itself
    |
    */


    /**
     * Creates a new research branch.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Research\Tree
     */
    public function createResearch($data, $user)
    {

        DB::beginTransaction();

        try {

            $data = $this->populateResearchData($data);

            $research = Research::create($data);

            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity']), $research);

            return $this->commitReturn($research);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a tree.
     *
     * @param  \App\Models\Research\Research  $tree
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\Research\Research
     */
    public function updateResearch($research, $data, $user)
    {
        DB::beginTransaction();

        try {
            // More specific validation
            if(Research::where('name', $data['name'])->where('id', '!=', $research->id)->exists()) throw new \Exception("The name has already been taken.");

            $data = $this->populateResearchData($data, $research);

            $research->update($data);

            if(!$this->updateChildrenOnTreeChange($research, $data['tree_id'])) throw new \Exception("Could not update children.");

            $this->populateRewards(Arr::only($data, ['rewardable_type', 'rewardable_id', 'quantity']), $research);

            return $this->commitReturn($research);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating research rewards.
     *
     * @param  array                            $data
     * @param  \App\Models\Research\Research    $research
     */
    private function populateRewards($data, $research)
    {
        // Clear the old rewards...
        $research->rewards()->delete();

        if(isset($data['rewardable_type'])) {
            foreach($data['rewardable_type'] as $key => $type)
            {
                ResearchReward::create([
                    'research_id'       => $research->id,
                    'rewardable_type' => $type,
                    'rewardable_id'   => $data['rewardable_id'][$key],
                    'quantity'        => $data['quantity'][$key],
                ]);
            }
        }
    }

    /**
     * Recursively update children
     *
     */
    public function updateChildrenOnTreeChange($research, $tree)
    {
        try
        {
            if(count($research->children))
            {
                foreach($research->children as $research)
                {
                    $research->tree_id = $tree;
                    $research->update();
                    $this->updateChildrenOnTreeChange($research, $tree);
                    return true;
                }
            }
            else return true;
        }
        catch(\Exception $e) {
            return false;
        }
        return false;
    }


    /**
     * Deletes a tree.
     *
     * @param  \App\Models\Research\Research  $tree
     * @return bool
     */
    public function deleteResearch($research)
    {
        DB::beginTransaction();

        try {

            // TODO: Recursively delete child research? Set any child of this to be child of its parent?

            $research->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes user input for creating/updating a research.
     *
     * @param  array                  $data
     * @param  \App\Models\Research\Research  $tree
     * @return array
     */
    private function populateResearchData($data, $research = null)
    {

        $saveData['name'] = $data['name'];

        $saveData['summary'] = isset($data['summary']) ? $data['summary'] : null;
        $saveData['description'] = isset($data['description']) ? $data['description'] : null;
        if(isset($data['description']) && $data['description']) $saveData['parsed_description'] = parse($data['description']);

        $saveData['icon_code'] = isset($data['icon']) ? $data['icon'] : 'fas fa-sitemap';

        $saveData['tree_id'] = $data['tree_id'];

        $saveData['parent_id'] = isset($data['parent_id']) && $data['parent_id'] ? $data['parent_id'] : null;
        $saveData['price'] = isset($data['price']) ? $data['price'] : 0;

        if(isset($data['prereq_is_same']) && $data['prereq_is_same']) $saveData['prerequisite_id'] = $saveData['parent_id'];
        else $saveData['prerequisite_id'] = isset($data['prerequisite_id']) && $data['prerequisite_id'] ? $data['prerequisite_id'] : null;

        $saveData['is_active'] = isset($data['is_active']);

        $saveData['rewardable_type'] = isset($data['rewardable_type']) ? $data['rewardable_type'] : null;
        $saveData['rewardable_id'] = isset($data['rewardable_id']) ? $data['rewardable_id'] : null;
        $saveData['quantity'] = isset($data['quantity']) ? $data['quantity'] : null;

        return $saveData;
    }





    /**
     * Grants an item to multiple users.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $staff
     * @return bool
     */
    public function grantResearch($data, $staff)
    {
        DB::beginTransaction();

        //['users', 'research_ids', 'message']\

        try {
            // Process names
            $users = User::find($data['users']);
            if(count($users) != count($data['users'])) throw new \Exception("An invalid user was selected.");

            $researches = Research::find($data['research_ids']);
            if(!count($researches)) throw new \Exception("No valid researches found.");

            $service = new ResearchManager;

            foreach($users as $user) {
                foreach($researches as $research) {
                    $userResearch = UserResearch::where('user_id',$user->id)->where('research_id',$research->id)->first();
                    if($userResearch) throw new \Exception($user->displayName." already has ".$research->displayName.".");
                    elseif($service->creditResearch($staff, $user, 'Staff Grant', $data['message'], $research))
                    {
                        Notifications::create('RESEARCH_GRANT', $user, [
                            'research_name' => $research->name,
                            'research_url' => $research->url,
                            'tree_name' => $research->tree->name,
                            'tree_url' => $research->tree->url,
                            'sender_url' => $staff->url,
                            'sender_name' => $staff->name
                        ]);

                        // Add a purchase log
                        $researchLog = ResearchLog::create([
                            'tree_id' => $research->tree->id,
                            'research_id' => $research->id,
                            'data' => json_encode(['log_type' => 'Staff Grant', 'message' => isset($data['message']) ? $data['message'] : null]),
                            'recipient_id' => $user->id,
                            'sender_id' => $staff->id,
                            'currency_id' => $research->tree->currency->id,
                            'cost' => 0 ,
                        ]);
                    }
                    else
                    {
                        throw new \Exception("Failed to credit items to ".$user->displayName.".");
                    }
                }
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

}
