<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsController extends Controller
{
    public function getGoods(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required'
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            $Goods = DB::table('goods')->get();
            return response()->json([
                'goods' => $Goods
            ], 200);
        }
        else
            return response()->json($this->codeVerification($input['access_code']), 401);
    }

    public function setGood(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required',
            'Name_of_Good' => 'required',
            'Price_for_Good' => 'required',
            'Weight_of_Good' => 'required',
            'Type_of_Packaging' => 'required'
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            if($this->codeVerification($input['access_code'])['status'] == 'root') {
                $Goods = DB::table('goods')->get();
                DB::insert(
                    'insert into goods (
                        id_Good,
                        Name_of_Good,
                        Price_for_Good,
                        Weight_of_Good,
                        Type_of_Packaging
                    ) values (?, ?, ?, ?, ?)',
                    [
                        count($Goods) + 1,
                        $input['Name_of_Good'],
                        $input['Price_for_Good'],
                        $input['Weight_of_Good'],
                        $input['Type_of_Packaging']
                    ]
                );
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
