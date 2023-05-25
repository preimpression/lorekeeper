<?php namespace App\Services;

use App\Services\Service;

use DB;
use Config;

use App\Models\User\User;
use App\Models\DevLog;

class DevLogService extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Logs Service
    |--------------------------------------------------------------------------
    |
    | Handles the creation and editing of devlogs.
    |
    */

    /**
     * Creates a devlog.
     *
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\DevLog
     */
    public function createLog($data, $user)
    {
        DB::beginTransaction();

        try {
            $data['parsed_text'] = parse($data['text']);
            $data['user_id'] = $user->id;
            if(!isset($data['is_visible'])) $data['is_visible'] = 0;

            $log = DevLog::create($data);

            if($log->is_visible) $this->alertUsers();

            return $this->commitReturn($log);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates a devlog.
     *
     * @param  \App\Models\DevLog       $devLogs
     * @param  array                  $data
     * @param  \App\Models\User\User  $user
     * @return bool|\App\Models\DevLog
     */
    public function updateDevLog($log, $data, $user)
    {
        DB::beginTransaction();

        try {
            $data['parsed_text'] = parse($data['text']);
            $data['user_id'] = $user->id;
            if(!isset($data['is_visible'])) $data['is_visible'] = 0;
            if(isset($data['bump']) && $data['is_visible'] == 1 && $data['bump'] == 1) $this->alertUsers();

            $log->update($data);

            return $this->commitReturn($log);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a devlog.
     *
     * @param  \App\Models\DevLog  $log
     * @return bool
     */
    public function deleteLog($log)
    {
        DB::beginTransaction();

        try {
            $log->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Updates queued devlogs to be visible and alert users when
     * they should be posted.
     *
     * @return bool
     */
    public function updateQueue()
    {
        $count = DevLog::shouldBeVisible()->count();
        if($count) {
            DB::beginTransaction();

            try {
                DevLog::shouldBeVisible()->update(['is_visible' => 1]);
                $this->alertUsers();

                return $this->commitReturn(true);
            } catch(\Exception $e) {
                $this->setError('error', $e->getMessage());
            }
            return $this->rollbackReturn(false);
        }
    }

    /**
     * Updates the unread devlog flag for all users so that
     * the new devlog notification is displayed.
     *
     * @return bool
     */
    private function alertUsers()
    {
        User::query()->update(['is_dev_logs_unread' => 1]);
        return true;
    }
}
