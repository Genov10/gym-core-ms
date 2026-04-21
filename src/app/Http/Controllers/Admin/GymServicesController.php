<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GymService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GymServicesController extends Controller
{
    public function index()
    {
        $services = GymService::query()
            ->orderByDesc('id')
            ->get();

        return view('admin.services.index', [
            'services' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            // 'is_periodical' => ['nullable', 'boolean'],
            'day_amount' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'visit_amount' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        $is_periodical = false;
        if ($data['day_amount'] > 0) {
            $is_periodical = true;
        }
        GymService::query()->create([
            'name' => $data['name'],
            'price' => $data['price'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_periodical' => $is_periodical,
            'day_amount' => $data['day_amount'] ?? null,
            'visit_amount' => $data['visit_amount'] ?? null,
            'created_at' => Carbon::now(),
        ]);

        return redirect('/admin/services')->with('status', 'Услуга добавлена.');
    }

    public function destroy(GymService $service)
    {
        $service->delete();

        return redirect('/admin/services')->with('status', 'Услуга удалена.');
    }
}

