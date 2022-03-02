<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use Illuminate\Support\Arr;
use App\Models\Batch\Batch;
use App\Models\Batch\BatchTarget;
use App\Models\Batch\BatchTrigger;

class BatchService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Batch Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of batchs.
    |
    */

    /**
     * Creates a batch.
     *
     * @param  array  $data
     * @return bool|\App\Models\Batch\Batch
     */
    public function createBatch($data)
    {
        DB::beginTransaction();

        try {

            if(isset($data['is_active']) && $data['is_active']) $data['is_active'] = 1;
            else $data['is_active'] = 0;

            $batch = Batch::create(Arr::only($data, ['name', 'is_active', 'trigger_at']));

            $this->populateTargets($batch, Arr::only($data, ['target_type', 'target_id']));

            return $this->commitReturn($batch);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a batch.
     *
     * @param  \App\Models\Batch\Batch  $batch
     * @param  array                       $data
     * @return bool|\App\Models\Batch\Batch
     */
    public function updateBatch($batch, $data)
    {
        DB::beginTransaction();

        try {

            if(isset($data['is_active']) && $data['is_active']) $data['is_active'] = 1;
            else $data['is_active'] = 0;

            $batch->update(Arr::only($data, ['name', 'is_active', 'trigger_at']));

            $this->populateTargets($batch, Arr::only($data, ['target_type', 'target_id']));

            return $this->commitReturn($batch);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Handles the creation of targets for a batch.
     *
     * @param  \App\Models\Batch\Batch  $batch
     * @param  array                       $data
     */
    private function populateTargets($batch, $data)
    {
        // Clear the old targets...
        $batch->targets()->delete();

        if(isset($data['target_type'])){
            foreach($data['target_type'] as $key => $type)
            {
                if(isset($data['target_id'][$key]) && isset($data['target_type'][$key])){
                    BatchTarget::create([
                        'batch_id'    => $batch->id,
                        'target_type' => $type,
                        'target_id'   => $data['target_id'][$key],
                    ]);
                }
            }
        }
    }

    /**
     * Deletes a batch.
     *
     * @param  \App\Models\Batch\Batch  $batch
     * @return bool
     */
    public function deleteBatch($batch)
    {
        DB::beginTransaction();

        try {

            $batch->targets()->delete();
            $batch->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Triggers a batch.
     *
     * @param  \App\Models\Batch\Batch  $batch
     * @return bool
     */
    public function triggerBatch($batch)
    {
        DB::beginTransaction();

        try {
            foreach($batch->targets as $target) if(!$this->activateTarget($target)) throw new \Exception("Unable to activate ".($target->target->name ? $target->target->name : $target->target->title));
            $batch->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


    /**
     * Activates a target
     *
     * @param  \App\Models\Batch\BatchTarget  $target
     * @return bool
     */
    private function activateTarget($target)
    {

        // Using $ring instead of $target, etc, to make it clear that it is not the actual item/news post etc being deleted by the ->delete() function!

        $ring = $target->target;

        // Sets activity to true depending on method of determining activity.
        if(isset($ring->is_active)) $ring->update(['is_active' => 1]);
        elseif(isset($ring->is_released)) $ring->update(['is_released' => 1]);
        elseif(isset($ring->is_visible)) {
            $ring->update(['is_visible' => 1]);
            if($target->target_type == 'News') (new NewsService)->alertUsers();
            elseif($target->target_type == 'Sale') (new SaleService)->alertUsers();
        }
        else return false;

        $ring->save();
        $target->delete();

        return $ring;
    }


    /**
     * Updates queued batches and triggers them.
     *
     * @return bool
     */
    public function updateQueue()
    {
        $batches = Batch::shouldBeTriggered()->get();
        if($batches->count()) {
            DB::beginTransaction();

            try {
                foreach($batches as $batch) $this->triggerBatch($batch);

                return $this->commitReturn(true);
            } catch(\Exception $e) {
                $this->setError('error', $e->getMessage());
            }
            return $this->rollbackReturn(false);
        }
    }



    /**
     * Initialises a new blank targets array, keyed by the asset type.
     *
     * @param  bool  $isCharacter
     * @return array
     */
    function createTargetArray()
    {
        $keys = $this->getTargetKeys();
        $targets = [];
        foreach($keys as $key) $targets[$key] = [];
        return $targets;
    }


    /**
     * Gets the target keys for an array
     *
     * @return array
     */
    function getTargetKeys()
    {
        return [

            // Standard
            'items', 'shops', 'prompts', 'newses', 'sales', 'site_pages', 'raffles', 'galleries', 'characters',

            // World Expansion
            'concepts', 'events', 'factions', 'faunas', 'floras', 'figures', 'locations',

        ];
    }

    /**
     * Gets the model name for an asset type.
     * The asset type has to correspond to one of the asset keys above.
     *
     * @param  string  $type
     * @param  bool    $namespaced
     * @return string
     */
    function getAssetModelString($type, $namespaced = true)
    {
        switch($type)
        {
            case 'items':
                if($namespaced) return '\App\Models\Item\Item';
                else return 'Item';
                break;

            case 'shops':
                if($namespaced) return '\App\Models\Shop\Shop';
                else return 'Shop';
                break;

            case 'prompts':
                if($namespaced) return '\App\Models\Prompt\Prompt';
                else return 'Prompt';
                break;

            case 'newses':
                if($namespaced) return '\App\Models\News';
                else return 'News';
                break;

            case 'sales':
                if($namespaced) return '\App\Models\Sales\Sales';
                else return 'Sales';
                break;

            case 'site_pages':
                if($namespaced) return '\App\Models\SitePage';
                else return 'SitePage';
                break;

            case 'raffles':
                if($namespaced) return '\App\Models\Raffle\Raffle';
                else return 'Raffle';
                break;

            case 'characters':
                if($namespaced) return '\App\Models\Character\Character';
                else return 'Character';
                break;


            /**
            * World Expanded
            *
            * The following are cases for the world expansion extension.
            * They can be ignored if you don't have the extension and deleted if you never plan on having it.
            */

            case 'concepts':
                if($namespaced) return '\App\Models\WorldExpansion\Concept';
                else return 'Concept';
                break;

            case 'events':
                if($namespaced) return '\App\Models\WorldExpansion\Event';
                else return 'Event';
                break;

            case 'factions':
                if($namespaced) return '\App\Models\WorldExpansion\Faction';
                else return 'Faction';
                break;

            case 'faunas':
                if($namespaced) return '\App\Models\WorldExpansion\Fauna';
                else return 'Fauna';
                break;

            case 'floras':
                if($namespaced) return '\App\Models\WorldExpansion\Flora';
                else return 'Flora';
                break;

            case 'figures':
                if($namespaced) return '\App\Models\WorldExpansion\Figure';
                else return 'Figure';
                break;

            case 'locations':
                if($namespaced) return '\App\Models\WorldExpansion\Location';
                else return 'Location';
                break;

        }
        return null;
    }


}
