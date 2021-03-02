<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\Research\Tree;
use App\Models\Research\Research;
use App\Models\Research\ResearchLog;
use App\Models\User\User;
use App\Models\User\UserResearch;
use App\Models\User\UserCurrency;

use App\Services\CurrencyManager;

class ResearchManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Research Manager
    |--------------------------------------------------------------------------
    |
    | Handles the purchasing and granting of Research
    |
    */

    /**
     * Buys an item from a shop.
     *
     * @param  array                 $data
     * @param  \App\Models\User\User $user
     * @return bool|App\Models\Shop\Shop
     */
    public function buyResearch($research_id, $user)
    {
        DB::beginTransaction();

        try {
            // Check that the research branch exists and is open
            $research = Research::where('id', $research_id)->where('is_active', 1)->first();
            if(!$research) throw new \Exception("Invalid research.");

            $userCurrency = UserCurrency::where('user_id',$user->id)->where('currency_id',$research->tree->currency_id)->first();
            if(!$userCurrency) throw new \Exception("Invalid currency.");

            if($userCurrency->quantity < $research->price) throw new \Exception("You don't have enough of the currency.");

            $userResearch = UserResearch::where('user_id',$user->id)->where('research_id',$research->id)->first();
            if($userResearch) throw new \Exception("You already have this research.");

            if($research->price > 0 && !(new CurrencyManager)->debitCurrency($user, null, 'Research Purchase', 'Purchased '.$research->name.' from '.$research->tree->name, $research->tree->currency, $research->price)) throw new \Exception("Not enough currency to make this purchase.");

            $quantity = 1;

            // Add a purchase log
            $researchLog = ResearchLog::create([
                'tree_id' => $research->tree->id,
                'research_id' => $research->id,
                'data' => json_encode(['log_type' => 'Research Purchase', 'message' => null]),
                'recipient_id' => $user->id,
                'sender_id' => null,
                'currency_id' => $research->tree->currency->id,
                'cost' => $research->price,
            ]);

            // Give the user the research, noting down 1. who purchased it 3. which shop it was purchased from
            if(!$this->creditResearch(null, $user, 'Research Purchase', $researchLog, $research)) throw new \Exception("Failed to purchase research.");

            return $this->commitReturn($research);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }



    /**
     * Credits an item to a user.
     *
     * @param  \App\Models\User\User  $sender
     * @param  \App\Models\User\User  $recipient
     * @param  string                 $type
     * @param  array                  $data
     * @param  \App\Models\Item\Item  $item
     * @param  int                    $quantity
     * @return bool
     */
    public function creditResearch($sender, $recipient, $type, $data = null, $research)
    {
        DB::beginTransaction();

        try {
            UserResearch::create(['user_id' => $recipient->id, 'research_id' => $research->id]);
            // if($type && !$this->createLog($sender ? $sender->id : null, $recipient->id, $type, $data['data'], $research->id)) throw new \Exception("Failed to create log.");
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    public function getRewards($research, $user)
    {
        DB::beginTransaction();

        try {
            $userResearch = UserResearch::where('user_id',$user->id)->where('research_id',$research->id)->first();

            $inventoryManager = new InventoryManager;

            // Get a list of rewards, then create the userResearch itself
            $researchRewards = createAssetsArray();
            foreach($research->rewards as $reward)
            {
                addAsset($researchRewards, $reward->reward, $reward->quantity);
            }

            // Logging data
            $researchLogType = 'Research Rewards';
            $researchData = [
                'data' => 'Redeemed rewards for '.$research->displayName
            ];

            // Distribute user rewards
            if(!$rewards = fillUserAssets($researchRewards, null, $userResearch->user, $researchLogType, $researchData)) throw new \Exception("Failed to distribute rewards to user.");


            $userResearch->update(['rewards_claimed' => 1]);
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Processes reward data into a format that can be used for distribution.
     *
     * @param  array $data
     * @param  bool  $isCharacter
     * @param  bool  $isStaff
     * @return array
     */
    private function processRewards($data, $isStaff = false)
    {
        $assets = createAssetsArray(false);
        // Process the additional rewards
        if(isset($data['rewardable_type']) && $data['rewardable_type'])
        {
            foreach($data['rewardable_type'] as $key => $type)
            {
                $reward = null;
                switch($type)
                {
                    case 'Item':
                        $reward = Item::find($data['rewardable_id'][$key]);
                        break;
                    case 'Currency':
                        $reward = Currency::find($data['rewardable_id'][$key]);
                        if(!$reward->is_user_owned) throw new \Exception("Invalid currency selected.");
                        break;
                    case 'LootTable':
                        if (!$isStaff) break;
                        $reward = LootTable::find($data['rewardable_id'][$key]);
                        break;
                    case 'Raffle':
                        if (!$isStaff) break;
                        $reward = Raffle::find($data['rewardable_id'][$key]);
                        break;
                }
                if(!$reward) continue;
                addAsset($assets, $reward, $data['quantity'][$key]);
            }
        }
        return $assets;

    }

}
