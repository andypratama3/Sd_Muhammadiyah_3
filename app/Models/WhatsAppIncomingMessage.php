<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppIncomingMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_incoming_messages';
}
