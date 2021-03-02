<?php

namespace App\Http\Controllers\Admin\Data;

use Auth;

use App\Models\Research\Tree;
use App\Models\Research\Research;
use App\Models\Research\ResearchLog;
use App\Models\User\User;
use App\Models\User\UserResearch;
use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Item\ItemCategory;
use App\Models\Loot\LootTable;
use App\Models\Raffle\Raffle;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Services\ResearchService;

class ResearchController extends Controller
{
    /**
     * Researches
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex(Request $request)
    {
        $query = Research::query();
        $data = $request->only(['tree_id', 'name']);
        if(isset($data['tree_id']) && $data['tree_id'] != 'none')
            $query->where('tree_id', $data['tree_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        return view('admin.research.index', [
            'researches' => $query->paginate(20)->appends($request->query()),
            'trees' => ['none' => 'Any Tree'] + Tree::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }



    /**
     * Shows the user's purchase history.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getUserResearchIndex()
    {
        return view('admin.research.user_research', [
            'logs' => ResearchLog::orderBy('id', 'DESC')->with('research')->with('tree')->with('recipient')->with('sender')->paginate(20),
            'trees' => ['none' => 'Any Tree'] + Tree::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }


    /**
     * Shows the create research page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateResearch()
    {
        $research = new Research;
        return view('admin.research.create_edit_branch', [
            'research' => $research,
            'trees' => Tree::orderBy('name')->pluck('name', 'id'),
            'researchTrees' => Tree::get(),
            'branches' => Research::orderBy('name')->pluck('name', 'id')->toArray(),
            'prereq_branches' => ['0' => 'Pick a Tree First'],
            'rewardsData' => isset($research->data['rewards']) ? parseAssetData($research->data['rewards']) : null,
            'itemsrow' => Item::all()->keyBy('id'),
            'items' => Item::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables' => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles' => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Shows the edit research page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditResearch($id)
    {
        $research = Research::find($id);
        $branches = Research::orderBy('name')->where('id', '!=', $research->id)->pluck('name', 'id')->toArray();
        if(!$research) abort(404);
        return view('admin.research.create_edit_branch', [
            'research' => $research,
            'trees' => Tree::orderBy('name')->pluck('name', 'id'),
            'researchTrees' => Tree::get(),
            'branches' => Research::orderBy('name')->where('id', '!=', $research->id)->pluck('name', 'id')->toArray(),
            'prereq_branches' => Research::orderBy('name')->where('id', '!=', $research->id)->where('tree_id', $research->tree_id)->pluck('name', 'id')->toArray(),
            'rewardsData' => isset($research->data['rewards']) ? parseAssetData($research->data['rewards']) : null,
            'itemsrow' => Item::all()->keyBy('id'),
            'items' => Item::orderBy('name')->pluck('name', 'id'),
            'currencies' => Currency::where('is_user_owned', 1)->orderBy('name')->pluck('name', 'id'),
            'tables' => LootTable::orderBy('name')->pluck('name', 'id'),
            'raffles' => Raffle::where('rolled_at', null)->where('is_active', 1)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Shows the edit research page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getParentBranch(Request $request)
    {
        $tree_id = $request->input('tree');
        $id = $request->input('id');

        return view('admin.research._branch_select', [
            'tree' => Tree::find($tree_id),
            'research' => Research::find($id),
            'prereq_branches' => Research::orderBy('name')->where('id', '!=', $id)->where('tree_id', $tree_id)->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Creates or edits a research.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ResearchService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditResearch(Request $request, ResearchService $service, $id = null)
    {
        $id ? $request->validate(Research::$updateRules) : $request->validate(Research::$createRules);

        $data = $request->only([
            'name', 'description', 'icon', 'tree_id', 'parent_id', 'prerequisite_id', 'prereq_is_same', 'is_active', 'summary', 'price',
            'rewardable_type', 'rewardable_id', 'quantity',
        ]);

        if($id && $service->updateResearch(Research::find($id), $data, Auth::user())) {
            flash('Research updated successfully.')->success();
        }
        else if (!$id && $research = $service->createResearch($data, Auth::user())) {
            flash('Research created successfully.')->success();
            return redirect()->to('admin/data/research/edit/'.$research->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the research deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteResearch($id)
    {
        $research = Research::find($id);
        return view('admin.research._delete_branch', [
            'research' => $research,
        ]);
    }

    /**
     * Deletes a research.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ResearchService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteResearch(Request $request, ResearchService $service, $id)
    {
        if($id && $service->deleteResearch(Research::find($id))) {
            flash('Research deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/data/research');
    }

    /**
     * Sorts research.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ResearchService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortResearch(Request $request, ResearchService $service)
    {
        if($service->sortResearch($request->get('sort'))) {
            flash('Research order updated successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
