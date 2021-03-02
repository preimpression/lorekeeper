<?php

namespace App\Http\Controllers\Research;

use Auth;

use App\Models\User\User;
use App\Models\Research\Tree;
use App\Models\Research\Research;
use App\Models\Currency\Currency;
use App\Models\User\UserCurrency;
use App\Models\User\UserResearch;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Services\ResearchService;
use App\Services\ResearchManager;

use App\Services\CurrencyManager;

class ResearchController extends Controller
{

    /**
     * Shows the items page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        $query = Research::with('tree');
        $data = $request->only(['tree_id', 'name', 'sort']);
        if(isset($data['tree_id']) && $data['tree_id'] != 'none')
            $query->where('tree_id', $data['tree_id']);
        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        if(isset($data['sort']))
        {
            switch($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'tree':
                    $query->sortTree();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
            }
        }
        else $query->sortTree();

        return view('research.branches', [
            'researches' => $query->paginate(20)->appends($request->query()),
            'trees' => Tree::orderBy('sort', 'DESC')->get(),
            'researchTrees' => ['none' => 'Any Tree'] + Tree::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows a research.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getResearch ($id)
    {
        $research = Research::where('id', $id)->first();
        if(!$research) abort(404);

        $user = Auth::user();

        if($user) {
            $currency = UserCurrency::where('currency_id',$research->tree->currency_id)->where('user_id',$user->id)->get()->pluck('quantity')->first();
        }
        if(!isset($currency) || !$currency) $currency = 0;

        return view('research.branch_page', [
            'research' => $research,
            'trees' => Tree::where('is_active', 1)->orderBy('sort', 'DESC')->get(),
            'bankroll' => $currency
        ]);
    }


    /**
     * ...
     *
     * @param  \Illuminate\Http\Request      $request
     * @param  App\Services\CurrencyManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getPurchaseResearch($id, CurrencyManager $service) {

        $research = Research::where('id', $id)->where('is_active', 1)->first();
        if(!$research) abort(404);

        $user = Auth::user();

        $currency = UserCurrency::where('currency_id',$research->tree->currency_id)->where('user_id',$user->id)->get()->pluck('quantity')->first();
        if(!$currency) $currency = 0;

        return view('research._purchase_modal', [
            'research' => $research,
            'user' => $user,
            'bankroll' => $currency,
        ]);


    }

    /**
     * ...
     *
     * @param  \Illuminate\Http\Request      $request
     * @param  App\Services\CurrencyManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postPurchaseResearch($id, Request $request, ResearchManager $service) {

        $research = Research::find($id);
        if(!$research) abort(404);

        if($service->buyResearch($research->id, Auth::user())) {
            flash('Research purchased successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();

    }


    /**
     * Shows the user's purchase history.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getResearchHistory()
    {
        return view('research.research_history', [
            'logs' => Auth::user()->getResearchLogs(0),
            'trees' => Tree::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * ...
     *
     * @param  \Illuminate\Http\Request      $request
     * @param  App\Services\CurrencyManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postClaimRewards($id, ResearchManager $service) {

        $research = Research::find($id);
        if(!$research) abort(404);

        if($service->getRewards($research, Auth::user())) {
            flash('Rewards claimed successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();

    }



}
