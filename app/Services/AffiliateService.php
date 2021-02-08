<?php namespace App\Services;

use Notifications;
use Auth;
use DB;
use App\Models\User\User;
use App\Models\Affiliate;
use App\Services\Service;

class AffiliateService extends Service
{

    /**
     * Create an affiliation request.
     */
    public function createAffiliate($data, $user)
    {
        DB::beginTransaction();

        try {

            $user = Auth::user()->id;

            $saveData = [
                'name' => $data['name'],
                'url' => $data['url'],
                'staff_id' => $user,
                'status' => 'Accepted',
                'is_featured' => isset($data['is_featured']) && $data['is_featured'] ? intval($data['is_featured']) : 0,
                'staff_comment' => isset($data['staff_comment']) ? parse($data['staff_comment']) : null,
                'image_url' => isset($data['image_url']) ? $data['image_url'] : null,
                'description' => isset($data['description']) ? parse($data['description']) : null,
                'message' => isset($data['message']) ? parse($data['message']) : null,
                'guest_name' => null,
                'user_id' => $user,
                'slug' => null
            ];
            $affiliate = Affiliate::create($saveData);

            return $this->commitReturn($affiliate);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update an affiliation request or affiliate.
     */
    public function updateAffiliate($affiliate, $data, $user)
    {
        DB::beginTransaction();

        try {
            if(!$affiliate) abort(404);

            if(Auth::check()) $user = Auth::user()->id;

            $saveData = [
                'name' => $data['name'],
                'url' => $data['url'],
                'is_featured' => isset($data['is_featured']) && $data['is_featured'] ? intval($data['is_featured'])  : 0,
                'staff_comment' => isset($data['staff_comment']) ? parse($data['staff_comment']) : null,
                'image_url' => isset($data['image_url']) ? $data['image_url'] : null,
                'staff_id' => isset($affiliate->staff_id) ? $affiliate->staff_id : $user,
                'description' => isset($data['description']) ? parse($data['description']) : null,
            ];

            $affiliate->update($saveData);

            return $this->commitReturn($affiliate);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Accept an affiliate request
     */
    public function acceptAffiliate($data, $user)
    {
        DB::beginTransaction();

        try {
            $affiliate = Affiliate::find($data['id']);
            if(!$affiliate) abort(404);

            $saveData = [
                'status' => 'Accepted',
                'is_featured' => isset($data['is_featured']) && $data['is_featured'] ? intval($data['is_featured'])  : 0,
                'staff_id' => Auth::user()->id,
                'staff_comment' => isset($data['staff_comment']) ? parse($data['staff_comment']) : null,
            ];

            $affiliate->update($saveData);

            $recipient = User::find($affiliate->user_id);
            if($recipient){
                Notifications::create('AFFILIATE_ACCEPTION', $recipient, [
                    'admin_name' => Auth::user()->name,
                    'affiliate_name' => $affiliate->name,
                    'affiliate_slug' => $affiliate->slug
                ]);
            }


            return $this->commitReturn($affiliate);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Reject an affiliate request
     */
    public function rejectAffiliate($data, $user)
    {
        DB::beginTransaction();

        try {
            $affiliate = Affiliate::find($data['id']);
            if(!$affiliate) abort(404);

            $saveData = [
                'status' => 'Rejected',
                'staff_id' => Auth::user()->id,
                'staff_comment' => isset($data['staff_comment']) ? parse($data['staff_comment']) : null,
            ];

            $affiliate->update($saveData);

            $recipient = User::find($affiliate->user_id);
            if($recipient){
                Notifications::create('AFFILIATE_REJECTION', $recipient, [
                    'admin_name' => Auth::user()->name,
                    'affiliate_name' => $affiliate->name,
                    'affiliate_slug' => $affiliate->slug
                ]);
            }

            return $this->commitReturn($affiliate);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an affiliate.
     *
     * @param  \App\Models\Affiliate\Affiliate  $affiliate
     * @return bool
     */
    public function deleteAffiliate($affiliate)
    {
        DB::beginTransaction();

        try {
            if(!$affiliate) throw new \Exception("This affiliate doesn't exist.");
            $affiliate->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }


}
