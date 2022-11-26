<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    public function setEmployee(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required',
            'Name' => 'required',
            'Job_Title' => 'required',
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            if($this->codeVerification($input['access_code'])['status'] == 'root') {
                $Employees = DB::table('employees')->get();
                DB::insert(
                    'insert into employees (First_Name_and_Second_Name, Job_Title, id_Employee) values (?, ?, ?)',
                    [$input['Name'], $input['Job_Title'], count($Employees) + 1]);
                return response()->json([
                    'insert' => 'success'
                ], 200);
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
