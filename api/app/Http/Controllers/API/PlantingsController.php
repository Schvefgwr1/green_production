<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlantingsController extends Controller
{
    public function getPlantings(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required'
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            $Plantings =
                DB::table('information_about_planting')
                    ->join('employees', 'employees.id_Planting', '=', 'information_about_planting.id_Planting')
                    ->select('information_about_planting.Temperature',
                        'information_about_planting.id_Planting',
                        'information_about_planting.Illumination',
                        'information_about_planting.Wet',
                        'information_about_planting.Content_of_dangerous_bacteria',
                        'information_about_planting.Sufficient_fertilizer_content',
                        'employees.First_Name_and_Second_Name',
                        'employees.Job_Title')
                    ->get();
            return response()->json([
                'Plantings' => $Plantings
            ], 200);
        }
        else
            return response()->json($this->codeVerification($input['access_code']), 401);
    }

    public function setPlanting(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required',
            'Temperature' => 'required',
            'Illumination' => 'required',
            'Wet' => 'required',
            'Content_of_dangerous_bacteria' => 'required',
            'Sufficient_fertilizer_content' => 'required',
            'id_Employee' => 'required'
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            if($this->codeVerification($input['access_code'])['status'] == 'root') {
                $Plantings = DB::table('information_about_planting')->get();
                $Employee = DB::table('employees')->where('id_Employee', $input['id_Employee'])->first();
                if(($Employee) &&
                (($Employee->Job_Title == 'Biologist') || ($Employee->Job_Title == 'Many Jobs')) &&
                (!$Employee->id_Planting)) {
                    DB::insert(
                        'insert into information_about_planting (
                            Temperature,
                            Illumination,
                            Wet,
                            Content_of_dangerous_bacteria,
                            Sufficient_fertilizer_content,
                            id_Planting
                        ) values (?, ?, ?, ?, ?, ?)',
                        [
                            $input['Temperature'],
                            $input['Illumination'],
                            $input['Wet'],
                            $input['Content_of_dangerous_bacteria'],
                            $input['Sufficient_fertilizer_content'],
                            count($Plantings) + 1
                        ]
                    );
                    DB::update(
                        'update employees set id_Planting = ? where id_Employee = ?',
                        [count($Plantings) + 1, $input['id_Employee']]
                    );
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
