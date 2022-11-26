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

    public function setPosition(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required',
            'Name' => 'required',
            'id_Employee' => 'required',
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            if($this->codeVerification($input['access_code'])['status'] == 'root') {
                $Positions = DB::table('stock')->get();
                $Employee = DB::table('employees')->where('id_Employee', $input['id_Employee'])->first();
                if(($Employee) &&
                  (($Employee->Job_Title == 'Stockman') ||
                   ($Employee->Job_Title == 'Many Jobs'))) {
                    if($input['Best_Before_Date']) {
                        DB::insert(
                            'insert into stock (Name_of_Position, Best_Before_Date, id_Employee, id_Position) values (?, ?, ?, ?)',
                            [$input['Name'], $input['Best_Before_Date'], $input['id_Employee'], count($Positions) + 1]);
                    }
                    else {
                        DB::insert(
                            'insert into stock (Name_of_Position, id_Employee, id_Position) values (?, ?, ?)',
                            [$input['Name'], $input['id_Employee'], count($Positions) + 1]);
                    }
                    return response()->json([
                        'insert' => 'success'
                    ], 200);
                }
                else {
                    return response()->json([
                        'insert' => 'error',
                        'reason' => 'incorrect employee'
                    ], 403);
                }
            }
            else return response()->json([
                'insert' => 'error',
                'reason' => 'don`t have root'
            ], 402);

        }
        else
            return response()->json($this->codeVerification($input['access_code']), 401);
    }
}
