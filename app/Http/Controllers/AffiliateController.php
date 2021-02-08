<?php

namespace App\Http\Controllers;

use Auth;
use Settings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\SitePage;
use App\Models\Affiliate;
use App\Services\AffiliateManager;

class AffiliateController extends Controller
{
    
    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('home.affiliates', [
            'open' => intval(Settings::get('affiliates_open')),
            'affiliates' => Affiliate::where('status','Accepted')->featured(0)->get(),
            'featured' => Affiliate::where('status','Accepted')->featured(1)->get(),
        ]);
    }
    
    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getApply()
    {

        return view('home.affiliates_apply', [
            'open' => intval(Settings::get('affiliates_open')),
            'affiliates' => Affiliate::where('status','Accepted')->featured(0)->get(),
            'featured' => Affiliate::where('status','Accepted')->featured(1)->get(),
        ]);
    }
    
    /**
     * Shows the homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postApply(Request $request, AffiliateManager $service)
    {
        $slug = randomString(10);

        if(Affiliate::where('slug',$slug)->exists()) while(Affiliate::where('slug',$slug)->exists()) $slug = randomString(10);

        $request->validate(Affiliate::$createRules);
        if($service->createAffiliate($request->only(['name','url','image_url','description','message','guest_name']), Auth::user(), $slug)) {
            flash('Affiliate Request submitted successfully. Please bookmark this page.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('affiliates/status/'.$slug);
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
    

}
