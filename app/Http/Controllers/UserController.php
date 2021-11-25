<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\key;
use App\Models\Token;
use App\Jobs\RegisterUserMail;
use App\Http\Requests\UsersFormRequest;
use App\Http\Requests\UsersloginRequest;
use App\Http\Requests\UsersGetRequest;
use App\Providers\servce;

class UserController extends Controller
{
    public function register(UsersFormRequest $req)
    {
    	 $validator = $req->validated();

        $validator = $req->safe()->only('name', 'email', 'password');
        try{
        if (!isset($validator)) {
            return response()->json(['error' => $validator->messages()], 403);
        }
        // create new user
        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => $req->password
        ]);

        $url =url('api/EmailConfirmation/'.$req['email']);
        RegisterUserMail::dispatch($req->email,$url);
         //response
        return response()->json([
            'success' => true,
            'message' => 'User created',
            'data' => $user
        ], Response::HTTP_OK);
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
    // confirmEmail 
    public function confirmEmail($email){
        $user= User::where('email', $email)->first();
        $user->email_verified_at =$user->email_verified_at =time();
        $user->save();
           dd($user);
           return $user;
    }

    public function authenticate(UsersloginRequest $request)
    {
        $validator =$request->validated();
        $validator = $request->safe()->only('email', 'password');
    try{
        if (!isset($validator)) {
            return response()->json(['error' => $validator->messages()], 403);
        }else{
            $user= User::where('email', $request->email)->first();
            
            $tokeexit = Token::where('user_id',$user->id)->first();
         
            if(!$tokeexit)
            {
                $token = (new servce)->createToken($user->id);
                $tokenData = Token::create([
                    'token' => $token,
                    'user_id' => $user->id
                ]);

                $response = [
                    'user' => $user,
                    'token' => $token,
                ];
            }else{
                $response = [
                    'user' => $user,
                    'token' => "already login",
                ];
            }
        
             return response($response, 201);
        }
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }

    function logout(Request $request)
    {

        //Decode Token
        
        $jwt = $request->bearerToken();
        $decoded =(new servce)->decodeToken($jwt);
        
        $userID = $decoded->data;
        
        $userExist = Token::where("user_id",$userID)->first();
        try{
        if($userExist){
        
            $userExist->delete();
        
        }else{
            return response()->json([
        
            "message" => " already logged out"
            
            ], 404);
            
        }
            return response()->json([
            
            "message" => "logout success"
            
            ], 200);
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
    public function get_user(UsersGetRequest $request)
    {
        
        $validator =$request->validated();
        $validator = $request->safe()->only('token');
        try{
        if (!isset($validator)) {
            return response()->json(['error' => $validator->messages()], 403);
        }
        $decoded =(new servce)->decodeToken($request->token);
        $userId = $decoded->data;
        $user= User::where('id', $userId)->first();
 
        return response()->json(['user' => $user]);
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }

}

