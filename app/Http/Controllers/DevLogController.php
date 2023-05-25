<?php

namespace App\Http\Controllers;

use Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\DevLog;
use App\Models\News;

class DevLogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Logs Controller
    |--------------------------------------------------------------------------
    |
    | Displays devlogs and updates the user's dev-log read status.
    |
    */

    /**
     * Shows the logs index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        if(Auth::check() && Auth::user()->is_dev_logs_unread) Auth::user()->update(['is_dev_logs_unread' => 0]);
        return view('logs.index', [
            'newses' => News::visible()->orderBy('updated_at', 'DESC')->paginate(10),
            'logs' => DevLog::visible()->orderBy('updated_at', 'DESC')->paginate(10),
        ]);
    }

    /**
     * Shows a devlog.
     *
     * @param  int          $id
     * @param  string|null  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLogs($id, $slug = null)
    {
        $log = DevLog::where('id', $id)->where('is_visible', 1)->first();
        if(!$log) abort(404);
        return view('logs.log', [
            'log'       => $log,
            'newses'    => News::visible()->orderBy('updated_at', 'DESC')->paginate(10),
            'logs'      => DevLog::visible()->orderBy('updated_at', 'DESC')->paginate(10),
        ]);
    }
}
