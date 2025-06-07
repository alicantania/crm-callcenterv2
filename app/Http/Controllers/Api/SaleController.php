<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        return Sale::with(['company', 'product', 'user'])
            ->filter(request()->only('status', 'date_from', 'date_to'))
            ->orderBy('sale_date', 'desc')
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pendiente,completada,cancelada',
            'sale_date' => 'required|date'
        ]);

        return Sale::create($validated + ['user_id' => auth()->id()]);
    }

    public function show(Sale $sale)
    {
        return $sale->load(['company', 'product', 'user']);
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pendiente,completada,cancelada',
            'amount' => 'sometimes|numeric|min:0'
        ]);

        $sale->update($validated);
        return $sale;
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();
        return response()->noContent();
    }
}
