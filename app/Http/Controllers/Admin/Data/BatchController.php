<?php

namespace App\Http\Controllers\Admin\Data;

use Illuminate\Http\Request;

use Auth;

use App\Models\Batch\Batch;
use App\Models\Batch\BatchTrigger;
use App\Models\Batch\BatchTarget;

use App\Http\Controllers\Controller;

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
    public function getBatchIndex()
    {


        return view('admin.batches.index', [

        ]);
    }
    /**
     * Show the design index page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $type
     * @param  string                    $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateEditBatch()
    {

        return view('admin.batches.create_edit_batch', [

        ]);
    }

    public function postCreateEditBatch(Request $request){
        $user = Auth::user();
        dd($request);
    }

    public function postActivateBatch(Request $request){
        $user = Auth::user();
        dd($request);
    }

}
