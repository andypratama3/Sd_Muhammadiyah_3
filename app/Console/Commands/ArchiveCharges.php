<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveCharges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'charges:archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move charges data older than one month to the charges_arsip table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Define the date limit (one month ago)
        $dateLimit = Carbon::now()->subMonth();

        // Fetch records older than a month from 'charges'
        $charges = DB::table('charges')
            ->where('created_at', '<', $dateLimit)
            ->get();

        if ($charges->isEmpty()) {
            $this->info('No charges found to archive.');
            return;
        }

        // Move the data to 'charges_arsip'
        foreach ($charges as $charge) {
            DB::table('charges_arsip')->insert((array) $charge);

            // Delete the record from 'charges' after moving
            DB::table('charges')->where('id', $charge->id)->delete();
        }

        $this->info('Archived charges older than one month.');
    }
}
