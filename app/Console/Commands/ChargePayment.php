<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ChargePayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:charge-payment';

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
        $siswas = Siswa::all();

        foreach ($siswas as $siswa) {
            DB::commit();

            DB::table('charges')->insert([
                'name' => $siswa->name,
                'order_id' => $siswa->id,
                'gross_amount' => $siswa->spp,
                'payment_type' => 'bank_transfer',
                'bank' => 'bni',
                'va_number' => $siswa->va,
                'transaction_id' => rand(),
                'transaction_time' => now(),
                'fraud_status' => 'accept',
            ]);

        DB::endTransaction();
        }
    }
}
