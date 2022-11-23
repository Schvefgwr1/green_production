<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function getPositions(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required'
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            $Positions =
                DB::table('stock')
                    ->join('employees', 'employees.id_Employee', '=', 'stock.id_Employee')
                    ->select('stock.*', 'employees.First_Name_and_Second_Name', 'employees.Job_Title')
                    ->get();
            return response()->json([
                'positions' => $Positions
            ], 200);
        }
        else
            return response()->json($this->codeVerification($input['access_code']), 401);
    }
}
