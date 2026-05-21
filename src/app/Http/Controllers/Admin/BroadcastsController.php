<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\AdminBroadcastNotifier;
use Illuminate\Http\Request;

class BroadcastsController extends Controller
{
    public function index()
    {
        $telegramIds = $this->collectTelegramIds();

        return view('admin.broadcasts.index', [
            'recipientsCount' => count($telegramIds),
        ]);
    }

    public function send(Request $request, AdminBroadcastNotifier $notifier)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4096'],
        ]);

        $telegramIds = $this->collectTelegramIds();

        if ($telegramIds === []) {
            return back()
                ->withInput()
                ->withErrors(['message' => 'Нет клиентов с telegram_id для рассылки.']);
        }

        $ok = $notifier->send($telegramIds, $data['message']);

        if (! $ok) {
            return back()
                ->withInput()
                ->withErrors(['message' => 'Не удалось отправить рассылку. Проверьте ADMIN_BROADCAST_URL и логи.']);
        }

        return redirect('/admin/broadcasts')->with(
            'status',
            'Рассылка отправлена: '.count($telegramIds).' получателей.'
        );
    }

    /**
     * @return list<int>
     */
    private function collectTelegramIds(): array
    {
        return Customer::query()
            ->whereNotNull('telegram_id')
            ->where('telegram_id', '!=', '')
            ->pluck('telegram_id')
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
