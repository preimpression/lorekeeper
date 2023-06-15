<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Console\Command;
use App\Models\Item\Item;

class ConvertItemCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert-item-credits {--drop-columns : Whether the old reference/artist columns should be dropped after moving data from them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert item credits and remove artist-related columns from items table';

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
     * @return int
     */
    public function handle()
    {
        $this->info('************************');
        $this->info('* CONVERT ITEM CREDITS *');
        $this->info('*   by Preimpression   *');
        $this->info('************************'."\n");

        $items = Item::all();

        if($items->count()) {
            $this->line('Updating '.$items->count().' item credits...');
            $bar = $this->output->createProgressBar(count($items));
            $bar->start();

            foreach($items as $item) {
                $data = $item->data ?? [];
                if(isset($data['credit-name']))
                    foreach($data['credit-name'] as $key => $name) {
                        $data['credits'][] = [
                            'name'  => $name,
                            'url'   => $data['credit-url'][$key],
                            'id'    => (int)$data['credit-id'][$key],
                            'role'  => $data['credit-role'][$key],
                        ];
                    }

                if(isset($item->reference_url) && $item->reference_url)
                    $data['credits'][] = ['name' => 'Reference',   'url' => $item->reference_url,   'id' => null,             'role' => null];

                if(isset($item->artist_id) && $item->artist_id)
                    $data['credits'][] = ['name' => null,          'url' => null,                   'id' => $item->artist_id, 'role' => 'Art'];

                if(isset($item->artist_url) && $item->artist_url){
                    $artist = checkAlias($item->artist_url, false); // Check to see if the artist exists on site
                    if(is_object($artist))  $data['credits'][]    = ['name' => null, 'url' => null,              'id' => $artist->id,   'role' => 'Art'];
                    else                    $data['credits'][]    = ['name' => null, 'url' => $item->artist_url, 'id' => null,          'role' => 'Art'];
                }

                $item->update([
                    'data' => json_encode([
                        'rarity'    => $data['rarity']  ?? null,
                        'uses'      => $data['uses']    ?? null,
                        'release'   => $data['release'] ?? null,
                        'prompts'   => $data['prompts'] ?? null,
                        'resell'    => $data['resell']  ?? null,
                        'credits'   => $data['credits'] ?? null,
                        ])
                ]);


                $bar->advance();
            }
            $bar->finish();
            $this->info("\n".'Item credits updated!');
        }
        else $this->line('No items.');

        if($this->option('drop-columns')) {
            // Drop alias columns from the impacted tables.
            Schema::table('items', function (Blueprint $table) {
                if(Schema::hasColumn('items', 'reference_url')) $table->dropColumn('reference_url'); else $this->line('Column "reference_url" skipped.');
                if(Schema::hasColumn('items', 'artist_alias')) $table->dropColumn('artist_alias'); else $this->line('Column "artist_alias" skipped.');
                if(Schema::hasColumn('items', 'artist_url')) $table->dropColumn('artist_url'); else $this->line('Column "artist_url" skipped.');
                if(Schema::hasColumn('items', 'artist_id')) $table->dropColumn('artist_id'); else $this->line('Column "artist_id" skipped.');
            });
            $this->info("Dropped old credit columns");
        }

        if(!$this->option('drop-columns')) $this->line("\nAfter checking that all data has been converted successfully,\nrun again with --drop-columns to drop old credit columns if desired.\n");

    }
}
