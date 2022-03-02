<?php

namespace App\Http\Controllers\Admin\Data;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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
        $query = Batch::query();
        $data = $request->only(['name']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        return view('admin.batches.index', [
            'batches' => $query->paginate(20)->appends($request->query()),
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
            (Schema::hasTable('locations') ? [
                'world_expanded' => true,
                'locations'      => \App\Models\WorldExpansion\Location::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'events'         => \App\Models\WorldExpansion\Event::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'concepts'       => \App\Models\WorldExpansion\Concept::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'faunas'         => \App\Models\WorldExpansion\Fauna::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'floras'         => \App\Models\WorldExpansion\Flora::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'figures'        => \App\Models\WorldExpansion\Figure::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
                'factions'       => \App\Models\WorldExpansion\Faction::orderBy('name')->where('is_active',0)->pluck('name', 'id'),
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
        $batch = Batch::findOrFail($id);
        $targetgroups = [];
        foreach($batch->targets as $target){
            $targetgroups[$target->target_type][] = $target->target->displayName;
        }

        if($id && $service->triggerBatch($batch)) {
            flash('Batch triggered successfully. It has been soft deleted and all targets have been cleared.')->success();
            foreach($targetgroups as $key => $item) flash('The following '.strtolower($key).(count($item) == 1 ? ' has' : 's have').' been activated: '.implode(', ', $item).'.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/batches');
    }

}
