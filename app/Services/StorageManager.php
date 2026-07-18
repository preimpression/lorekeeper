<?php namespace App\Services;

use Carbon\Carbon;
use App\Services\Service;

use DB;
use Auth;
use Config;
use Notifications;

use Illuminate\Support\Arr;
use App\Models\User\User;
use App\Models\User\UserStorage;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\User\UserItem;
use App\Models\User\UserCurrency;
use App\Models\Character\CharacterItem;
use App\Models\Currency\Currency;

class StorageManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Inventory Manager
    |--------------------------------------------------------------------------
    |
    | Handles moving from and to the Safety Deposit Box for items, currency, and other such things.
    |
    */


    /**
     * Transfers items between user stacks.
     *
     * @param  \App\Models\User\User      $sender
     * @param  \App\Models\User\User      $recipient
     * @param  \App\Models\User\UserItem  $stacks
     * @param  int                        $quantities
     * @return bool
     */
    public function depositStack($user, $stacks, $quantities)
    {
        DB::beginTransaction();

        try {
            foreach($stacks as $key=>$stack) {
                $quantity = (int)$quantities[$key];

                $storage = (new UserStorage)->storageDetails($stack);
                if(!$storage) throw new \Exception("An invalid object was selected.");

                if($this->depositOrigins($user, $stack, $quantity, $storage)){
                    if(!$this->creditStorage(
                        $user,
                        $stack,
                        $stack->data,
                        $quantity,
                        $storage
                    )) throw new \Exception("Unable to add object to storage.");

                } else throw new \Exception("Unable to deposit object.");
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }



    /**
     * Credits an item to a user or character.
     *
     * @param  \App\Models\User\User|\App\Models\Character\Character  $sender
     * @param  \App\Models\User\User|\App\Models\Character\Character  $recipient
     * @param  string                                                 $type
     * @param  array                                                  $data
     * @param  \App\Models\Item\Item                                  $item
     * @param  int                                                    $quantity
     * @return bool
     */
    public function creditStorage($user, $stack, $data, $quantity, $storage)
    {
        DB::beginTransaction();

        try {
            $user_storage = UserStorage::where([
                ['user_id',       '=', $user->id],
                ['storer_type',   '=', get_class($stack)],
                ['storer_id',     '=', $stack->id],
                ['data',          '=', json_encode($stack->data)]
            ])->first();

            if(!$user_storage) {
                $user_storage = UserStorage::create([
                    'user_id'       =>  $user->id,
                    'count'         =>  $quantity,
                    'storable_id'   =>  (int) $storage['id'],
                    'storable_type' =>  $storage['type'],
                    'storer_id'     =>  (int) $stack->id,
                    'storer_type'   =>  get_class($stack),
                    'data'          =>  json_encode($data),
                ]);
            } else {
                $user_storage->count += $quantity;
                $user_storage->save();
            }

            return $this->commitReturn($user_storage);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Credits an item to a user or character.
     *
     * @param  \App\Models\User\User|\App\Models\Character\Character  $sender
     * @param  \App\Models\User\User|\App\Models\Character\Character  $recipient
     * @param  string                                                 $type
     * @param  array                                                  $data
     * @param  \App\Models\Item\Item                                  $item
     * @param  int                                                    $quantity
     * @return bool
     */
    public function depositOrigins($user, $stack, $quantity, $storage)
    {
        $data = ['data' => 'Moved to Safety Deposit Box.'];
        $type = 'Deposit';

        switch(get_class($stack)){
            default: case 'App/Models/User/UserItem':     if(!(new InventoryManager)->debitStack($user, $type, $data, $stack, $quantity)) return false;
            // case 'App/Models/User/UserCurrency':        (new InventoryManager)->debitCurrency($user, null, $type, $data, $stack->currency_id, $quantity);
        }

        return true;
    }


    public function withdrawStack($user, $data){
        DB::beginTransaction();

        try {

            $quantities = array_filter($data['remove']);

            if(isset($data['remove_one'])){
                // Prepare remove with use of remove_one button
                $quantities = [ $data['remove_one'] => 1 ];
                $stacks = UserStorage::where('id',(int)$data['remove_one'])->get()->keyBy('id');
            } elseif(isset($data['remove_all'])) {
                // Prepare remove with use of remove all button
                $stack = UserStorage::find((int)$data['remove_all']);
                if(!$stack) throw new \Exception("Invalid storage object.");

                $stacks = UserStorage::where([
                    ['user_id','=', $user->id],
                    ['storable_type','=', $stack->storable_type],
                    ['storable_id','=', $stack->storable_id],
                    ])->get()->keyBy('id');

                $quantities = [];
                foreach($stacks as $key => $stack) $quantities[$key] = $stack->count;
            } else {
                $stacks = UserStorage::whereIn('id',array_keys($quantities))->get()->keyBy('id');
            }

            if(!$stacks->count()) throw new \Exception("No stacks selected.");

            foreach($stacks as $key=>$stack) {
                $quantity = $quantities[$key];

                if($this->withdrawDestination($user, $stack, $quantity)){
                    $stack->count -= $quantity;
                    if($stack->count < 0) throw new \Exception("Cannot withdraw more objects than were stored.");
                    $stack->save();
                    if($stack->count == 0) $stack->delete(); // Clean up if count is 0
                } else throw new \Exception("Unable to withdraw object.");
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handles processing of
     *
     * @param  \App\Models\User\User|\App\Models\Character\Character  $sender
     * @param  \App\Models\User\User|\App\Models\Character\Character  $recipient
     * @param  string                                                 $type
     * @param  array                                                  $data
     * @param  \App\Models\Item\Item                                  $item
     * @param  int                                                    $quantity
     * @return bool
     */
    public function withdrawDestination($user, $stack, $quantity)
    {
        $data = $stack->data;
        $type = 'Storage Withdrawal';

        switch($stack->storer_type){
            default: case 'App/Models/User/UserItem':       if(!(new InventoryManager)->creditItem($user, $user, $type, $data, $stack->storable, $quantity)) return false;
            // case 'App/Models/User/UserCurrency':        (new InventoryManager)->debitCurrency($user, null, $type, $data, $stack->currency_id, $quantity);
        }

        return true;
    }

}
