<?php

namespace App\Http\Controllers\Admin\Data;

use Illuminate\Http\Request;

use Auth;
use Config;

use App\Models\Batch\Batch;
use App\Models\Batch\BatchTrigger;
use App\Models\Batch\BatchTarget;

use App\Models\Item\Item;
use App\Models\Shop\Shop;
use App\Models\Prompt\Prompt;
use App\Models\News;
use App\Models\Sales\Sales;
use App\Models\SitePage;
use App\Models\Raffle\Raffle;
use App\Models\Character\Character;

// WORLD EXPANSION. UNCOMMENT THE FOLLOWING if you have the extension and want to use this! Otherwise **it will error**
// use App\Models\WorldExpansion\Concept;
// use App\Models\WorldExpansion\Event;
// use App\Models\WorldExpansion\Faction;
// use App\Models\WorldExpansion\Fauna;
// use App\Models\WorldExpansion\Flora;
// use App\Models\WorldExpansion\Figure;
// use App\Models\WorldExpansion\Location;

use App\Http\Controllers\Controller;
use App\Services\BatchService;

class BatchController extends Controller
{
    /**
     * Show the design index page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $type
     * @param  string                    $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBatchIndex(Request $request)
    {
        $batches = Batch::query();
        if ($request->get('is_active')) $batches->where('is_active', $request->get('is_active'));
        else $batches->where('is_active', '!=', 2);
        $batches = $batches->orderBy('name');

        return view('admin.batches.index', [
            'batches' => $batches->get(),
        ]);
    }

    /**
     * Shows the create/edit batch modal.
     *
     * @param  int|null  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEditBatch($id = null)
    {
        $batch = null;
        if ($id) {
            $batch = Batch::find($id);
            if (!$batch) abort(404);
        }
        else $batch = new Batch;

        return view('admin.batches.create_edit_batch', [
            'batch'         => $batch,
            'characters'    => Character::orderBy('name')->where('is_visible',0)->get()->pluck('fullName', 'id'),
            'items'         => Item::orderBy('name')->where('is_released',0)->pluck('name', 'id'),
            'newses'        => News::orderBy('title')->where('is_visible',0)->pluck('title', 'id'),
            'raffles'       => Raffle::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
            'prompts'       => Prompt::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
            'shops'         => Shop::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
            'sales'         => Sales::orderBy('title')->where('is_visible',0)->pluck('title', 'id'),
            'sitepages'     => SitePage::orderBy('title')->where('is_visible',0)->pluck('title', 'id'),
            ] +
            (Config::get('lorekeeper.extensions.world_expansion.batched') ? [
                'locations'     => Location::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'events'        => Event::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'concepts'      => Concept::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'faunas'        => Fauna::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'floras'        => Flora::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'figures'       => Figure::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'factions'      => Faction::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
            ] : [])
        );
    }

    public function postCreateEditBatch(Request $request, BatchService $service, $id = null){
        $id ? $request->validate(Batch::$updateRules) : $request->validate(Batch::$createRules);
        $data = $request->only([
            'name', 'is_active', 'trigger_at', 'target_type', 'target_id', 'trigger_type', 'trigger_id'
        ]);
        if($id && $service->updateBatch(Batch::find($id), $data, Auth::user())) {
            flash('Batch updated successfully.')->success();
        }
        else if (!$id && $batch = $service->createBatch($data, Auth::user())) {
            flash('Batch created successfully.')->success();
            return redirect()->to('admin/data/batches/edit/'.$batch->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }


    /**
     * Gets the loot batch deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteBatch($id)
    {
        $batch = Batch::find($id);
        return view('admin.batches._delete_batch', [
            'batch' => $batch,
        ]);
    }

    /**
     * Deletes an item category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\BatchService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteBatch(Request $request, BatchService $service, $id)
    {
        if($id && $service->deleteBatch(Batch::find($id))) {
            flash('Batch deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/batches');
    }
    /**
     * Gets the batch trigger modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTriggerBatch($id)
    {
        $batch = Batch::find($id);
        return view('admin.batches._trigger_batch', [
            'batch' => $batch,
        ]);
    }

    /**
     * Triggers an item category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\BatchService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTriggerBatch(Request $request, BatchService $service, $id)
    {
        if($id && $service->deleteBatch(Batch::find($id))) {
            flash('Batch deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/batches');
    }

}
