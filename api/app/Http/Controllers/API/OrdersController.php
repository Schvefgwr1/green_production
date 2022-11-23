<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function getOrders(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required'
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            $Orders =
                DB::table('orders_of_goods')
                    ->select('id_Order',
                        'orders_of_goods.Name_of_Shop',
                        'orders_of_goods.Date_jf_Delivery',
                        'orders_of_goods.Status_of_Order',
                        'orders_of_goods.id_Employee as Employee',
                        'orders_of_goods.id_Letter as Letter')
                    ->get();
            for($i = 0; $i < count($Orders); $i++) {
                $Goods =
//                    DB::table('goods_has_orders_of_goods')
//                        ->join('goods', 'goods_has_orders_of_goods.id_Good', '=', 'goods.id_Good')
//                        ->select('goods.Name_of_Good',
//                            'goods.Price_for_Good',
//                            'goods.Weight_of_Good',
//                            'goods.Type_of_Packaging')
//                        ->where('goods_has_orders_of_goods.id_Order', $Orders[$i]->id_Order);
                      DB::select('SELECT
	                                        goods.Name_of_Good,
                                            goods.Price_for_Good,
                                            goods.Weight_of_Good,
                                            goods.Type_of_Packaging
                                        FROM `goods_has_orders_of_goods` INNER JOIN `goods`
                                        ON goods_has_orders_of_goods.id_Good = goods.id_Good
                                        WHERE goods_has_orders_of_goods.id_Order = 1');
                $Employee = DB::table('Employees')->where('id_Employee', $Orders[$i]->Employee)->first();
                $Letter =
                    DB::table('Letter_to_Shop')
                        ->leftJoin('Reasons_Of_Letters', 'Letter_to_Shop.id_Reason', '=', 'Reasons_Of_Letters.id_Reason')
                        ->select('Letter_to_Shop.Data_of_Letter',
                            'Letter_to_Shop.Text',
                            'Letter_to_Shop.Status',
                            'Reasons_Of_Letters.Reasons_Of_Letters')
                        ->where('id_Letter', $Orders[$i]->Letter)->first();
                $Orders[$i]->Employee = $Employee->First_Name_and_Second_Name;
                $Orders[$i]->Letter = $Letter;
                $Orders[$i]->Goods = $Goods;
            }
            return response()->json([
                'Orders' => $Orders
            ], 200);
        }
        else
            return response()->json($this->codeVerification($input['access_code']), 401);
    }
}
