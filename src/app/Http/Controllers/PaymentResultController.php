<?php

namespace App\Http\Controllers;

use App\Models\PaymentOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentResultController extends Controller
{
    /**
     * Сторінка після редиректу WayForPay (returnUrl). БД тут не змінюємо — лише показ результату.
     */
    public function __invoke(Request $request): View
    {
        $transactionStatus = (string) ($request->input('transactionStatus') ?? $request->query('transactionStatus') ?? '');
        $orderReference = (string) ($request->input('orderReference') ?? $request->query('orderReference') ?? '');

        $success = strcasecmp($transactionStatus, 'Approved') === 0;

        if (! $success && $orderReference !== '') {
            $order = PaymentOrder::query()->where('order_reference', $orderReference)->first();
            if ($order && $order->status === 'approved') {
                $success = true;
            }
        }

        return view('payment.result', [
            'success' => $success,
            'orderReference' => $orderReference,
        ]);
    }
}
