<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Models\User;
use App\Http\Providers\servce;

use Illuminate\Support\Facades\Validator;

use Firebase\JWT\JWT;

use Firebase\JWT\Key;

class EnsureTokenIsValid
{

    public function handle(Request $request, Closure $next)
    {
    try{
        $decoded=(new srevce)->decodeToken($request->bearerToken());
        $request=$request->merge(array('FromMiddleware'=>$decoded));
        return $next($request);
        } catch(Exception $e) {
            
        }

    }

     
}

