<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class PaymentResultNotifier
{
    public function notify(int $telegramId, string $serviceName, bool $success): void
    {
        $url = (string) config('services.payment_result.webhook_url');
        if ($url === '') {
            return;
        }

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'telegram_id' => $telegramId,
                    'serviceName' => $serviceName,
                    'success' => $success,
                ]);

            if (! $response->successful()) {
                Log::warning('payment_result webhook non-2xx', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('payment_result webhook failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
