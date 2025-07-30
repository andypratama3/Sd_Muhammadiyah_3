<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncDataSiswaAttendancesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $siswa;
    protected $cloud_id;
    protected $token;
    protected $base_url;
    protected $endpoints = [
        'get_info' => '/api/get_userinfo',
        'set_user' => '/api/set_userinfo',
        'register' => '/api/reg_online',
        'delete' => '/api/delete_userinfo',
        "set_time" => '/api/set_time',
    ];
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->siswa = $siswa;
        $this->cloud_id = config('fingerspot.cloud_id');
        $this->token = config('fingerspot.token_fingerspot');
        $this->base_url = 'https://developer.fingerspot.io';

        // Validate required configuration
        if (empty($this->cloud_id) || empty($this->token)) {
            throw new \RuntimeException('FingerSpot configuration is incomplete. Please check cloud_id and token settings.');
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $siswa = $this->siswa;

        $registerSiswa = $this->base_url . $this->endpoints['register'];

        

    }
}
