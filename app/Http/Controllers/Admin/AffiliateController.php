<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Settings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\SitePage;
use App\Models\Affiliate;
use App\Services\AffiliateService;

class AffiliateController extends Controller
{

    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.affiliates.index', [
            'open' => intval(Settings::get('affiliates_open')),
            'affiliates' => Affiliate::where('status','Accepted')->get()->paginate(10),
        ]);
    }

    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateAffiliate()
    {
        return view('admin.affiliates.create_edit_affiliate', [
            'affiliate' => new Affiliate
        ]);
    }

    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditAffiliate($id)
    {
        return view('admin.affiliates.create_edit_affiliate', [
            'affiliate' => Affiliate::find($id),
        ]);
    }

    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postCreateEditAffiliate(Request $request, AffiliateService $service, $id = null)
    {
        $id ? $request->validate(Affiliate::$updateRules) : $request->validate(Affiliate::$createRules);
        $affiliate = Affiliate::find($id);

        $data = $request->only(['name','url','image_url','description','message','staff_comment', 'is_featured']);

        if($id && $service->updateAffiliate(Affiliate::find($id), $data, Auth::user())) {
            flash('Affiliate updated successfully.')->success();
            return redirect()->to('admin/affiliates/edit/'.$id);
        }
        else if (!$id && $affiliate = $service->createAffiliate($data, Auth::user())) {
            flash('Affiliate created successfully.')->success();
            return redirect()->to('admin/affiliates/edit/'.$affiliate->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/affiliates');
    }

    /**
     * Shows the status of an affiliate request.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStatus($slug)
    {
        $affiliate = Affiliate::where('slug', $slug)->first();
        if(!$affiliate) abort(404);

        return view('home.affiliates_check', [
            'affiliate' => $affiliate
        ]);
    }

    /**
     * Shows the submission index page.
     *
     * @param  string  $status
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getQueue(Request $request, $status = null)
    {
        $affiliates = Affiliate::where('status', $status ? ucfirst($status) : 'Pending');
        $data = $request->only(['sort']);
        if(isset($data['sort']))
        {
            switch($data['sort']) {
                case 'newest':
                    $affiliates->sortNewest();
                    break;
                case 'oldest':
                    $affiliates->sortOldest();
                    break;
            }
        }
        else $affiliates->sortOldest();
        return view('admin.affiliates.affiliates', [
            'affiliates' => $affiliates->paginate(10)->appends($request->query())
        ]);
    }


    /**
     * Accepts or Rejects an affiliate request
     */
    public function getAcceptAffiliate($id)
    {
        $affiliate = Affiliate::find($id);
        if(!$affiliate) abort(404);

        return view('admin.affiliates._accept_modal', [
            'affiliate' => $affiliate
        ]);
    }


    /**
     * Accepts or Rejects an affiliate request
     */
    public function getRejectAffiliate($id)
    {

        $affiliate = Affiliate::find($id);
        if(!$affiliate) abort(404);

        return view('admin.affiliates._reject_modal', [
            'affiliate' => $affiliate
        ]);
    }

    /**
     * Accepts or Rejects an affiliate request
     */
    public function postAffiliate(Request $request, AffiliateService $service, $id, $action)
    {
        if($action == 'reject' && $service->rejectAffiliate($request->only(['staff_comment']) + ['id' => $id], Auth::user())) {
            flash('Affiliate rejected successfully.')->success();
        }
        elseif($action == 'accept' && $service->acceptAffiliate($request->only(['staff_comment','is_featured']) + ['id' => $id], Auth::user())) {
            flash('Affiliate accepted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }


    /**
     * Accepts or Deletes an affiliate request
     */
    public function getDeleteAffiliate($id)
    {
        $affiliate = Affiliate::find($id);
        if(!$affiliate) abort(404);

        return view('admin.affiliates._delete_modal', [
            'affiliate' => $affiliate
        ]);
    }

    /**
     * Deletes an affiliate.
     *
     * @param  \Illuminate\Http\Request             $request
     * @param  App\Services\AffiliateService        $service
     * @param  int                                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteAffiliate(Request $request, AffiliateService $service, $id)
    {
        if($id && $service->deleteAffiliate(Affiliate::find($id))) {
            flash('Affiliate deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/affiliates/current');
    }





}
