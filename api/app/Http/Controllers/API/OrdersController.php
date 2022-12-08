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
                    /*DB::table('goods_has_orders_of_goods')
                        ->join('goods', 'goods.id_Good', '=', 'goods_has_orders_of_goods.id_Good')
                        ->select('goods.Name_of_Good',
                            'goods.Price_for_Good',
                            'goods.Weight_of_Good',
                            'goods.Type_of_Packaging')
                        ->where('goods_has_orders_of_goods.id_Order', $Orders[$i]->id_Order);*/
                      DB::select('SELECT
	                                        goods.Name_of_Good,
                                            goods.Price_for_Good,
                                            goods.Weight_of_Good,
                                            goods.Type_of_Packaging
                                        FROM `goods_has_orders_of_goods` INNER JOIN `goods`
                                        ON goods_has_orders_of_goods.id_Good = goods.id_Good
                                        WHERE goods_has_orders_of_goods.id_Order = ' .$Orders[$i]->id_Order);
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

    public function findOrder(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required',
            'Good' => 'required',
            'Employee' => 'required'
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            if ($this->codeVerification($input['access_code'])['status'] == 'root') {
                $queryResult = DB::select('call FindOrder(?, ?)', [$input['Good'], $input['Employee']]);
                $result = collect($queryResult);
                if(!$queryResult) {
                    return response()->json([
                        'result' => 'error',
                        'reason' => 'incorrect input'
                    ], 440);
                }
                return response()->json([
                    'result' => $result
                ], 200);
            } else return response()->json([
                'insert' => 'error',
                'reason' => 'don`t have root'
            ], 402);
        }
        else
            return response()->json($this->codeVerification($input['access_code']), 401);
    }

    public function callProcedure(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required',
            'Name' => 'required',
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            if ($this->codeVerification($input['access_code'])['status'] == 'root') {
                $queryResult = DB::select('call FindLettersToShop(?)', [$input['Name']]);
                $result = collect($queryResult);
                if(!$queryResult) {
                    return response()->json([
                        'result' => 'error',
                        'reason' => 'incorrect name'
                    ], 440);
                }
                return response()->json([
                    'result' => $result
                    ], 200);
            } else return response()->json([
                'insert' => 'error',
                'reason' => 'don`t have root'
            ], 402);
        }
        else
            return response()->json($this->codeVerification($input['access_code']), 401);
    }

    public function setOrder(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required',
            'Name_of_Shop' => 'required',
            'Date_jf_Delivery' => 'required',
            'Status_of_Order' => 'required',
            'Goods' => 'required',
            'id_Employee' => 'required',
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            if($this->codeVerification($input['access_code'])['status'] == 'root') {
                $Orders = DB::table('Orders_of_Goods')->get();
                $Employee = DB::table('employees')->where('id_Employee', $input['id_Employee'])->first();
                if(($Employee) &&
                (($Employee->Job_Title == 'Orders Manager') ||
                ($Employee->Job_Title == 'Many Jobs'))) {
                    for($i = 0; $i < count($input['Goods']); $i++) {
                        if(!DB::table('goods')->where('id_Good', $input['Goods'][$i])->first())
                            return response()->json([
                                'insert' => 'error',
                                'reason' => 'incorrect goods'
                            ], 405);
                    }
                    DB::insert(
                        'insert into orders_of_goods (
                             Name_of_Shop,
                             Date_jf_Delivery,
                             Status_of_Order,
                             id_Employee,
                             id_Order
                        ) values (?, ?, ?, ?, ?)', [
                            $input['Name_of_Shop'],
                            $input['Date_jf_Delivery'],
                            $input['Status_of_Order'],
                            $input['id_Employee'],
                            count($Orders) + 1
                        ]
                    );
                    for($i = 0; $i < count($input['Goods']); $i++) {
                        DB::insert('insert into goods_has_orders_of_goods (
                             id_Order,
                             id_Good
                        ) values (?, ?)', [
                                count($Orders) + 1,
                                $input['Goods'][$i]
                            ]
                        );
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

    public function setLetter(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required',
            'Data_of_Letter' => 'required',
            'Text' => 'required',
            'Status' => 'required',
            'id_Reason' => 'required',
            'id_Order' => 'required'
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            if($this->codeVerification($input['access_code'])['status'] == 'root') {
                $Letters = DB::table('letter_to_shop')->get();
                $Order = DB::table('orders_of_goods')->where('id_Order', $input['id_Order'])->first();
                if(($Order) && (!$Order->id_Letter)) {
                    if(DB::table('reasons_of_letters')->where('id_Reason', $input['id_Reason'])->first()) {
                        DB::insert(
                            'insert into letter_to_shop (
                                Data_of_Letter,
                                Text,
                                Status,
                                id_Reason,
                                id_Letter
                            ) values (?, ?, ?, ?, ?)',
                            [
                                $input['Data_of_Letter'],
                                $input['Text'],
                                $input['Status'],
                                $input['id_Reason'],
                                count($Letters) + 1
                            ]
                        );
                        DB::update(
                            'update orders_of_goods set id_Letter = ? where id_Order = ?',
                            [count($Letters) + 1, $input['id_Order']]
                        );
                        return response()->json([
                            'insert' => 'success'
                        ], 200);
                    }
                    else {
                        return response()->json([
                            'insert' => 'error',
                            'reason' => 'incorrect reason of letter'
                        ], 407);
                    }
                }
                else {
                    return response()->json([
                        'insert' => 'error',
                        'reason' => 'incorrect order'
                    ], 406);
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

    public function setReason(Request $request) : JsonResponse
    {
        $validation = $request->validate([
            'access_code' => 'required',
            'Reasons_Of_Letters' => 'required'
        ]);
        $input = $request->all();
        if($this->codeVerification($input['access_code'])['success']) {
            if($this->codeVerification($input['access_code'])['status'] == 'root') {
                $Reasons = DB::table('reasons_of_letters')->get();
                DB::insert(
                    'insert into reasons_of_letters (
                        Reasons_Of_Letters,
                        id_Reason
                    ) values (?, ?)',
                    [
                        $input['Reasons_Of_Letters'],
                        count($Reasons) + 1
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
