<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArchiveCharges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:archive-charges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::commit();
        // Archive charges that are past due
        DB::table('charges')
            ->where('created_at', '<', now()->subDays(30))
            ->where('status', 'success');

        // Archive charges that are past due
        
    }
}
