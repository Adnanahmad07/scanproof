<?php

namespace App\Notifications\Channels;

use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function __construct(
        private WhatsAppService $whatsApp,
    ) {}

    public function send(User $notifiable, Notification $notification): void
    {
        $message = $notification->toWhatsApp($notifiable);

        if ($notifiable->phone && $message) {
            $this->whatsApp->sendMessage($notifiable->phone, $message);
        }
    }
}
