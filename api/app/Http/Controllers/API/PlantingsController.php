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
}
