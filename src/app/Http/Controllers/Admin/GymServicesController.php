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

    public function show(GymService $service)
    {
        return view('admin.services.show', [
            'service' => $service,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['nullable', 'decimal:0,2', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'day_amount' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'visit_amount' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        $is_periodical = ($data['day_amount'] ?? 0) > 0;

        GymService::query()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $this->normalizePrice($data['price'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_periodical' => $is_periodical,
            'day_amount' => $data['day_amount'] ?? null,
            'visit_amount' => $data['visit_amount'] ?? null,
            'created_at' => Carbon::now(),
        ]);

        return redirect('/admin/services')->with('status', 'Услуга добавлена.');
    }

    public function update(Request $request, GymService $service)
    {
        $data = $request->validate(array_merge([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['nullable', 'decimal:0,2', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'day_amount' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'visit_amount' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ], $this->salesPercentRules()));

        $is_periodical = ($data['day_amount'] ?? 0) > 0;

        $service->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $this->normalizePrice($data['price'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_periodical' => $is_periodical,
            'day_amount' => $data['day_amount'] ?? null,
            'visit_amount' => $data['visit_amount'] ?? null,
            'sales_default' => (int) $data['sales_default'],
            'sales_military_member' => (int) $data['sales_military_member'],
            'sales_student' => (int) $data['sales_student'],
        ]);

        return redirect('/admin/services/'.$service->id)->with('status', 'Услуга сохранена.');
    }

    public function deactivate(GymService $service)
    {
        $service->is_active = false;
        $service->save();

        return redirect('/admin/services')->with('status', 'Услуга деактивирована.');
    }

    /**
     * @return array<string, list<string>>
     */
    private function salesPercentRules(): array
    {
        return [
            'sales_default' => ['required', 'integer', 'min:0', 'max:100'],
            'sales_military_member' => ['required', 'integer', 'min:0', 'max:100'],
            'sales_student' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    private function normalizePrice(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(',', '.', trim((string) $value));

        return number_format((float) $normalized, 2, '.', '');
    }
}
