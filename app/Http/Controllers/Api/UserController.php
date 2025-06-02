<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Listado de todos los usuarios.
     * GET /api/users
     */
    public function index(): JsonResponse
    {
        return response()->json(User::all());
    }
}
