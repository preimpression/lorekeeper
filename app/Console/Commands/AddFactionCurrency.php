<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use Settings;
use App\Models\User\User;
use App\Models\Currency\Currency;
use App\Services\CurrencyService;

class AddFactionCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add-faction-currency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds the world expansion faction currency.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('*********************************');
        $this->info('* ADD FACTION STANDING CURRENCY *');
        $this->info('*********************************'."\n");

        $this->info('This will create a new currency with which to track faction standing and add a site setting with its ID preentered.');
        $this->info('This command should only be run once.');

        if($this->confirm('Do you wish to continue?')) {
            // Currency
            if(!$this->confirm('Do you want to use your current site settings for user and character factions? If not, you will be asked whether you want to track faction standing for each.')) {
                $userOwned = $this->confirm('Do you want to track faction standing for users?');
                $characterOwned = $this->confirm('Do you want to track faction standing for characters?');
                $this->line('These settings can be changed by editing the "Faction Standing" currency.');
            }
            else {
                $userOwned = Settings::get('WE_user_factions') > 0 ? true : false;
                $characterOwned = Settings::get('WE_character_factions') > 0 ? true : false;
            }

            $this->line("Adding currency...\n");

            $data = [
                'is_user_owned' => $userOwned,
                'is_character_owned' => $characterOwned,
                'name' => 'Faction Standing',
                'abbreviation' => 'Standing',
                'description' => '<p>Standing in a given faction.</p>'
            ];

            $currency = (new CurrencyService)->createCurrency($data, User::find(Settings::get('admin_user')));
            $this->info("Added:   Faction Standing Currency");

            // Site Setting
            $this->line("Adding site setting...\n");

            if(!DB::table('site_settings')->where('key', 'WE_faction_currency')->exists()) {
                DB::table('site_settings')->insert([
                    [
                        'key' => 'WE_faction_currency',
                        'value' => $currency->id,
                        'description' => 'ID of the currency used for tracking WE faction standing.'
                    ]

                ]);
                $this->info("Added:   WE_faction_currency");
            }
            else $this->line("Skipped: WE_faction_currency");

            $this->line('Done!');
        }
    }
}
