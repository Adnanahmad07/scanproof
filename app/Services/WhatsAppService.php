<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiUrl;
    private string $apiKey;
    private string $fromNumber;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url', 'https://graph.facebook.com/v18.0');
        $this->apiKey = (string) (config('services.whatsapp.token') ?? '');
        $this->fromNumber = (string) (config('services.whatsapp.from') ?? '');
    }

    public function sendMessage(string $to, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('WhatsApp API key not configured', ['to' => $to]);
            return false;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->post("{$this->apiUrl}/{$this->fromNumber}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('WhatsApp message failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
