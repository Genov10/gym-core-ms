<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;

trait ChecksCustomerBan
{
    protected function bannedCustomerResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Customer is banned',
            'code' => 17,
        ], 403);
    }

    protected function denyBannedCustomer(?Customer $customer): ?JsonResponse
    {
        if ($customer?->is_banned) {
            return $this->bannedCustomerResponse();
        }

        return null;
    }
}
