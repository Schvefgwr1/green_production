<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function authorizeUser(Request $request): JsonResponse
    {
        $validation = $request->validate([
            'login' => 'required',
            'password' => 'required',
            'task' => 'required'
        ]);
        $input = $request->all();
        if (User::where('login', $input['login'])->first()) {
            $User = User::where('login', $input['login'])->first();
            if($User->password == $input['password'])
                return response()->json([
                    'success' => true,
                    'message' => 'user auth',
                ], 200);
            else
                return response()->json([
                    'success' => false,
                    'message' => 'incorrect password',
                ], 400);
        } else {
            User::create(array_merge($input, ['login' => $input['login'], 'password' => $input['password'], 'root' => false]));
            return response()->json([
                'success' => true,
                'message' => 'new user'
            ], 200);
        }
    }
}
