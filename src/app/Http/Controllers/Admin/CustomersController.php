<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;

class CustomersController extends Controller
{
    public function index()
    {
        $customers = Customer::query()
            ->orderByDesc('id')
            ->get();

        return view('admin.customers.index', [
            'customers' => $customers,
        ]);
    }
}
