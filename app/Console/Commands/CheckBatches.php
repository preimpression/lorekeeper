<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BatchService;

class CheckBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-batches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if there are any batches to trigger.';

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
        //
        (new BatchService)->updateQueue();
    }
}
