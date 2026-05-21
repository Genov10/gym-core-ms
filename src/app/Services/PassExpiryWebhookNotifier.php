<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class PassExpiryWebhookNotifier
{
    /**
     * @param  list<int>  $telegramIds
     */
    public function notify(array $telegramIds, string $configKey): bool
    {
        $url = (string) config("services.{$configKey}.url");
        if ($url === '') {
            Log::warning("{$configKey}.url is not configured");

            return false;
        }

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'telegram_ids' => $telegramIds,
                ]);

            if (! $response->successful()) {
                Log::warning('pass expiry notification non-2xx', [
                    'config_key' => $configKey,
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('pass expiry notification failed', [
                'config_key' => $configKey,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
