<?php

namespace App\Http\Controllers\Characters;

use Illuminate\Http\Request;

use DB;
use Auth;
use Route;
use Settings;
use App\Models\User\User;
use App\Models\Character\Character;
use App\Models\Species\Species;
use App\Models\Rarity;
use App\Models\Feature\Feature;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyLog;
use App\Models\User\UserCurrency;
use App\Models\Character\CharacterCurrency;
use App\Models\Character\CharacterTransfer;

use App\Services\CurrencyManager;
use App\Services\CharacterManager;

use App\Http\Controllers\Controller;

class CharacterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Character Controller
    |--------------------------------------------------------------------------
    |
    | Handles displaying and acting on a character.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $slug = Route::current()->parameter('slug');
            $query = Character::myo(0)->where('slug', $slug);
            if(!(Auth::check() && Auth::user()->hasPower('manage_characters'))) $query->where('is_visible', 1);
            $this->character = $query->first();
            if(!$this->character) abort(404);

            $this->character->updateOwner();
            return $next($request);
        });
    }

    /**
     * Shows a character's masterlist entry.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacter($slug)
    {
        return view('character.character', [
            'character' => $this->character,
        ]);
    }

    /**
     * Shows a character's profile.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterProfile($slug)
    {
        return view('character.profile', [
            'character' => $this->character,
        ]);
    }

    /**
     * Shows a character's edit profile page.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCharacterProfile($slug)
    {
        if(!Auth::check()) abort(404);
        
        $isMod = Auth::user()->hasPower('manage_characters');
        $isOwner = ($this->character->user_id == Auth::user()->id);
        if(!$isMod && !$isOwner) abort(404);

        return view('character.edit_profile', [
            'character' => $this->character,
        ]);
    }
    
    /**
     * Edits a character's profile.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @param  string                         $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditCharacterProfile(Request $request, CharacterManager $service, $slug)
    {
        if(!Auth::check()) abort(404);

        $isMod = Auth::user()->hasPower('manage_characters');
        $isOwner = ($this->character->user_id == Auth::user()->id);
        if(!$isMod && !$isOwner) abort(404);
        
        if($service->updateCharacterProfile($request->only(['name', 'text', 'is_gift_art_allowed', 'is_trading', 'alert_user']), $this->character, Auth::user(), !$isOwner)) {
            flash('Profile edited successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows a character's images.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterImages($slug)
    {
        return view('character.images', [
            'user' => Auth::check() ? Auth::user() : null,
            'character' => $this->character,
        ]);
    }

    /**
     * Shows a character's bank.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterBank($slug)
    {
        $character = $this->character;
        return view('character.bank', [
            'character' => $this->character,
            'currencies' => $character->getCurrencies(true),
            'logs' => $this->character->getCurrencyLogs(),
        ] + (Auth::check() && Auth::user()->id == $this->character->user_id ? [
            'takeCurrencyOptions' => Currency::where('allow_character_to_user', 1)->where('is_user_owned', 1)->where('is_character_owned', 1)->whereIn('id', CharacterCurrency::where('character_id', $this->character->id)->pluck('currency_id')->toArray())->orderBy('sort_character', 'DESC')->pluck('name', 'id')->toArray(),
            'giveCurrencyOptions' => Currency::where('allow_user_to_character', 1)->where('is_user_owned', 1)->where('is_character_owned', 1)->whereIn('id', UserCurrency::where('user_id', Auth::user()->id)->pluck('currency_id')->toArray())->orderBy('sort_user', 'DESC')->pluck('name', 'id')->toArray(),

        ] : []) + (Auth::check() && (Auth::user()->hasPower('edit_inventories') || Auth::user()->id == $this->character->user_id) ? [
            'currencyOptions' => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id')->toArray(),
        ] : []));
    }
    
    /**
     * Transfers currency between the user and character.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @param  string                         $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCurrencyTransfer(Request $request, CurrencyManager $service, $slug)
    {
        if(!Auth::check()) abort(404);

        $action = $request->get('action');
        $sender = ($action == 'take') ? $this->character : Auth::user();
        $recipient = ($action == 'take') ? Auth::user() : $this->character;

        if($service->transferCharacterCurrency($sender, $recipient, Currency::where(($action == 'take') ? 'allow_character_to_user' : 'allow_user_to_character', 1)->where('id', $request->get(($action == 'take') ? 'take_currency_id' : 'give_currency_id'))->first(), $request->get('quantity'))) {
            flash('Currency transferred successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows a character's currency logs.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterCurrencyLogs($slug)
    {
        return view('character.currency_logs', [
            'character' => $this->character,
            'logs' => $this->character->getCurrencyLogs(0)
        ]);
    }
    
    /**
     * Shows a character's ownership logs.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterOwnershipLogs($slug)
    {
        return view('character.ownership_logs', [
            'character' => $this->character,
            'logs' => $this->character->getOwnershipLogs(0)
        ]);
    }
    
    /**
     * Shows a character's ownership logs.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterLogs($slug)
    {
        return view('character.character_logs', [
            'character' => $this->character,
            'logs' => $this->character->getCharacterLogs()
        ]);
    }

    /**
     * Shows a character's submissions.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterSubmissions($slug)
    {
        return view('character.submission_logs', [
            'character' => $this->character,
            'logs' => $this->character->getSubmissions()
        ]);
    }

    /**
     * Shows a character's transfer page.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTransfer($slug)
    {
        if(!Auth::check()) abort(404);
        
        $isMod = Auth::user()->hasPower('manage_characters');
        $isOwner = ($this->character->user_id == Auth::user()->id);
        if(!$isMod && !$isOwner) abort(404);

        return view('character.transfer', [
            'character' => $this->character,
            'transfer' => CharacterTransfer::active()->where('character_id', $this->character->id)->first(),
            'cooldown' => Settings::get('transfer_cooldown'),
            'transfersQueue' => Settings::get('open_transfers_queue'),
            'userOptions' => User::visible()->orderBy('name')->pluck('name', 'id')->toArray(),
        ]);
    }
    
    /**
     * Opens a transfer request for a character.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @param  string                         $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postTransfer(Request $request, CharacterManager $service, $slug)
    {
        if(!Auth::check()) abort(404);
        
        if($service->createTransfer($request->only(['recipient_id']), $this->character, Auth::user())) {
            flash('Transfer created successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Cancels a transfer request for a character.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  App\Services\CharacterManager  $service
     * @param  string                         $slug
     * @param  int                            $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCancelTransfer(Request $request, CharacterManager $service, $slug, $id)
    {
        if(!Auth::check()) abort(404);
        
        if($service->cancelTransfer(['transfer_id' => $id], Auth::user())) {
            flash('Transfer cancelled.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
    
    /**
     * Shows a character's design update approval page.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterApproval($slug)
    {
        if(!Auth::check() || $this->character->user_id != Auth::user()->id) abort(404);

        return view('character.update_form', [
            'character' => $this->character,
            'queueOpen' => Settings::get('is_design_updates_open'),
            'request' => $this->character->designUpdate()->active()->first()
        ]);
    }

    /**
     * Opens a new design update approval request for a character.
     *
     * @param  App\Services\CharacterManager  $service
     * @param  string                         $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCharacterApproval($slug, CharacterManager $service)
    {
        if(!Auth::check() || $this->character->user_id != Auth::user()->id) abort(404);

        if($request = $service->createDesignUpdateRequest($this->character, Auth::user())) {
            flash('Successfully created new design update request draft.')->success();
            return redirect()->to($request->url);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }
}
