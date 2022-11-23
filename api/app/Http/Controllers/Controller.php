<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Psy\Util\Json;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function codeVerification($code): array
    {
        if(User::where('code', $code)->first()) {
            if(!User::where('code', $code)->first()->root)
                return [
                    'success' => true,
                    'status' => 'user'
                ];
            else return [
                'success' => true,
                'status' => 'root'
            ];

        }
        else
            return [
                'success' => false,
                'status' => 'no verification'
            ];
    }
}
