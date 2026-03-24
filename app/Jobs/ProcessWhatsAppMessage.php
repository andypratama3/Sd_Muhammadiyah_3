<?php

namespace App\Jobs;
 
use App\Services\WhatsAppBotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
 
class ProcessWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
 
    public $tries = 3;
    public $timeout = 30;
 
    public function __construct(
        private string $phone,
        private string $messageText,
        private string $profileName
    ) {}
 
    public function handle(WhatsAppBotService $botService): void
    {
        $botService->handle($this->phone, $this->messageText, $this->profileName);
    }
}
 