<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function GenerateAccessCode(): int
    {
        $Users = User::get();
        $flag = 0;
        $token = -2;
        while($flag == 0) {
            $token = rand(0,9999);
            $flag_in = 0;
            for($i = 0; $i < count($Users); $i++)
                if($token == $Users[$i]->access_code)
                    $flag_in= 1;
            if($flag_in == 0)
                $flag = 1;
        }
        return $token;
    }

    public function authorizeUser(Request $request): JsonResponse
    {
        $validation = $request->validate([
            'login' => 'required',
            'password' => 'required'
        ]);
        $input = $request->all();
        if (User::where('login', $input['login'])->first()) {
            $User = User::where('login', $input['login'])->first();
            if($User->password == $input['password']) {
                $code = $this->GenerateAccessCode();
                User::where('login', $input['login'])->update(['code' => $code]);
                $User = User::where('login', $input['login'])->first();
                if($User->root)
                    $user_status = 'root';
                else
                    $user_status = 'user';
                return response()->json([
                    'success' => true,
                    'message' => 'user auth',
                    'status' => $user_status,
                    'access_code' => $User->code
                ], 200);
            }
            else
                return response()->json([
                    'success' => false,
                    'message' => 'incorrect password',
                ], 400);
        } else {
            $code = $this->GenerateAccessCode();
            User::create(['login' => $input['login'], 'password' => $input['password'], 'code' => $code, 'root' => false]);
            return response()->json([
                'success' => true,
                'message' => 'new user',
                'status' => 'user',
                'access_code' => $code
            ], 200);
        }
    }
}
