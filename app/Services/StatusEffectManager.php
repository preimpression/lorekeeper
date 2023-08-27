<?php namespace App\Services;

use App\Models\Character\CharacterStatusEffect;
use Carbon\Carbon;
use App\Services\Service;

use DB;
use Config;
use Notifications;

use App\Models\User\User;
use App\Models\Status\StatusEffect;
use App\Models\Status\StatusEffectLog;

class StatusEffectManager extends Service
{
    /*
    |--------------------------------------------------------------------------
    | Status Effect Manager
    |--------------------------------------------------------------------------
    |
    | Handles the modification of status effects applied to characters.
    |
    */

    /**
     * Admin function for granting status effects to a character.
     * Removes status effects if the quantity given is less than 0.
     *
     * @param  array                            $data
     * @param  \App\Models\Character\Character  $staff
     * @param  \App\Models\User\User            $staff
     * @return  bool
     */
    public function grantCharacterStatusEffects($data, $character, $staff)
    {
        DB::beginTransaction();

        try {
            if($data['quantity'] == 0) throw new \Exception("Please enter a non-zero quantity.");

            if(!$character) throw new \Exception("Invalid character selected.");

            // Process status effect
            $status = StatusEffect::find($data['status_id']);
            if(!$status) throw new \Exception("Invalid status effect selected.");
            if($data['quantity'] < 0) {
                if(!$this->debitStatusEffect($character, $staff, 'Staff Removal', $data['data'], $status, -$data['quantity'])) throw new \Exception('Failed to debit status effect.');

                if(isset($character->user)) {
                  Notifications::create('CHARACTER_STATUS_REMOVAL', $character->user, [
                      'status_name' => $status->name,
                      'status_quantity' => -$data['quantity'],
                      'sender_url' => $staff->url,
                      'sender_name' => $staff->name,
                      'character_name' => $character->fullName,
                      'character_slug' => $character->slug
                  ]);
                }
            }
            else{
                if(!$this->creditStatusEffect($staff, $character, 'Staff Grant', $data['data'], $status, $data['quantity'])) throw new \Exception('Failed to credit status effect.');

                if(isset($character->user)) {
                  Notifications::create('CHARACTER_STATUS_GRANT', $character->user, [
                      'status_name' => $status->name,
                      'status_quantity' => $data['quantity'],
                      'sender_url' => $staff->url,
                      'sender_name' => $staff->name,
                      'character_name' => $character->fullName,
                      'character_slug' => $character->slug
                  ]);
                }
            }

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Credits status effects to a character.
     *
     * @param  \App\Models\Character\Character  $sender
     * @param  \App\Models\Character\Character  $recipient
     * @param  string                           $type
     * @param  string                           $data
     * @param  \App\Models\Status\StatusEffect  $status
     * @param  int                              $quantity
     * @return  bool
     */
    public function creditStatusEffect($sender, $recipient, $type, $data, $status, $quantity)
    {
        DB::beginTransaction();

        try {
            if(is_numeric($status)) $status = StatusEffect::find($status);
            
            $record = CharacterStatusEffect::where('character_id', $recipient->id)->where('status_effect_id', $status->id)->first();
            if($record) {
                CharacterStatusEffect::where('character_id', $recipient->id)->where('status_effect_id', $status->id)->update(['quantity' => $record->quantity + $quantity]);
            }
            else {
                $record = CharacterStatusEffect::create(['character_id' => $recipient->id, 'status_effect_id' => $status->id, 'quantity' => $quantity]);
            }
            

            if($type && !$this->createLog($sender ? $sender->id : null, $sender ? $sender->logType : null,
            $recipient ? $recipient->id : null, $recipient ? $recipient->logType : null,
            $type, $data, $status->id, $quantity)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Debits status effects from a character.
     *
     * @param  \App\Models\Character\Character  $sender
     * @param  \App\Models\Character\Character  $recipient
     * @param  string                           $type
     * @param  string                           $data
     * @param  \App\Models\Status\StatusEffect  $status
     * @param  int                              $quantity
     * @return  bool
     */
    public function debitStatusEffect($sender, $recipient, $type, $data, $status, $quantity)
    {
        DB::beginTransaction();

        try {
        
            $record = CharacterStatusEffect::where('character_id', $sender->id)->where('status_effect_id', $status->id)->first();
            if(!$record || $record->quantity < $quantity) throw new \Exception("Not enough ".$status->name." to carry out this action.");

            CharacterStatusEffect::where('character_id', $sender->id)->where('status_effect_id', $status->id)->update(['quantity' => $record->quantity - $quantity]);

            if($type && !$this->createLog($sender ? $sender->id : null, $sender ? $sender->logType : null,
            $recipient ? $recipient->id : null, $recipient ? $recipient->logType : null,
            $type, $data, $status->id, -$quantity)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Creates a currency log.
     *
     * @param  int     $senderId
     * @param  string  $senderType
     * @param  int     $recipientId
     * @param  string  $recipientType
     * @param  string  $type
     * @param  string  $data
     * @param  int     $statusId
     * @param  int     $quantity
     * @return  int
     */
    public function createLog($senderId, $senderType, $recipientId, $recipientType, $type, $data, $statusId, $quantity)
    {
        return DB::table('status_effects_log')->insert(
            [
                'sender_id' => $senderId,
                'sender_type' => $senderType,
                'recipient_id' => $recipientId,
                'recipient_type' => $recipientType,
                'log' => $type . ($data ? ' (' . $data . ')' : ''),
                'log_type' => $type,
                'data' => $data, // this should be just a string
                'status_effect_id' => $statusId,
                'quantity' => $quantity,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
    }
}
