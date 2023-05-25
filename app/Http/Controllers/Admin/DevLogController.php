<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Auth;

use App\Models\DevLog;
use App\Services\DevLogService;

use App\Http\Controllers\Controller;

class DevLogController extends Controller
{
    /**
     * Shows the logs index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('admin.logs.logs', [
            'logs' => DevLog::orderBy('updated_at', 'DESC')->paginate(20)
        ]);
    }

    /**
     * Shows the create devlog page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateLog()
    {
        return view('admin.logs.create_edit_logs', [
            'log' => new DevLog
        ]);
    }

    /**
     * Shows the edit devlog page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLog($id)
    {
        $log = DevLog::find($id);
        if(!$log) abort(404);
        return view('admin.logs.create_edit_logs', [
            'log' => $log
        ]);
    }

    /**
     * Creates or edits a devlog page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\DevLogService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditLogs(Request $request, DevLogService $service, $id = null)
    {
        $id ? $request->validate(DevLog::$updateRules) : $request->validate(DevLog::$createRules);
        $data = $request->only([
            'title', 'text', 'post_at', 'is_visible', 'bump'
        ]);
        if($id && $service->updateDevLog(DevLog::find($id), $data, Auth::user())) {
            flash('Development log updated successfully.')->success();
        }
        else if (!$id && $devLogs = $service->createLog($data, Auth::user())) {
            flash('Development log created successfully.')->success();
            return redirect()->to('admin/logs/edit/'.$devLogs->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the devlog deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteLogs($id)
    {
        $log = DevLog::find($id);
        return view('admin.logs._delete_logs', [
            'log' => $log,
        ]);
    }

    /**
     * Deletes a devlogs page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\DevLogService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteLogs(Request $request, DevLogService $service, $id)
    {
        if($id && $service->deleteLog(DevLog::find($id))) {
            flash('Development log deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/logs');
    }

}
