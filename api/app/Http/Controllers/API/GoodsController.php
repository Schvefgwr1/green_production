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
}
