<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AdminBroadcastNotifier
{
    /**
     * @param  list<int>  $telegramIds
     */
    public function send(array $telegramIds, string $message): bool
    {
        $url = (string) config('services.admin_broadcast.url');
        if ($url === '') {
            Log::warning('admin_broadcast.url is not configured');

            return false;
        }

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'telegram_ids' => $telegramIds,
                    'message' => $message,
                ]);

            if (! $response->successful()) {
                Log::warning('admin broadcast non-2xx', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('admin broadcast failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
