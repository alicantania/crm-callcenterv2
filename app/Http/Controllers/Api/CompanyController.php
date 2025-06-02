<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    /**
     * Listado de todas las empresas.
     * GET /api/companies
     */
    public function index(): JsonResponse
    {
        return response()->json(Company::all());
    }

    /**
     * Historial de llamadas de una empresa.
     * GET /api/companies/{company}/calls
     */
    public function calls(int $companyId): JsonResponse
    {
        $company = Company::with('calls')->findOrFail($companyId);
        return response()->json($company->calls);
    }
}
