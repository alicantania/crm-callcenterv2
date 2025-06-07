<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    /**
     * Listado de todas las empresas.
     * GET /api/companies
     */
    public function index()
    {
        return Company::with(['calls', 'sales', 'operator'])
            ->filter(request()->only('status', 'operator'))
            ->orderBy('follow_up_date', 'asc')
            ->paginate(20);
    }

    /**
     * Crear una nueva empresa.
     * POST /api/companies
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cif' => 'required|unique:companies|max:20',
            'name' => 'required|max:100',
            'status' => 'required|in:contactada,seguimiento,error',
            'follow_up_date' => 'nullable|date',
            'assigned_operator_id' => 'nullable|exists:users,id'
        ]);

        return Company::create($validated);
    }

    /**
     * Mostrar una empresa.
     * GET /api/companies/{company}
     */
    public function show(Company $company)
    {
        return $company->load(['calls', 'sales', 'operator']);
    }

    /**
     * Actualizar una empresa.
     * PUT /api/companies/{company}
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:contactada,seguimiento,error',
            'follow_up_date' => 'nullable|date',
            'assigned_operator_id' => 'nullable|exists:users,id'
        ]);

        $company->update($validated);
        return $company;
    }

    /**
     * Eliminar una empresa.
     * DELETE /api/companies/{company}
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return response()->noContent();
    }

    /**
     * Historial de llamadas de una empresa.
     * GET /api/companies/{company}/calls
     */
    public function calls(Company $company)
    {
        return $company->calls()->paginate(10);
    }
}
