<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeesController extends Controller
{
    public function getEmployees(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required'
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            $Employees = DB::table('employees')->get();
            return response()->json([
                'employees' => $Employees
            ], 200);
        }
        else
            return response()->json($this->codeVerification($input['access_code']), 401);
    }
}
