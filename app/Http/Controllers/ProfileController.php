<?php

namespace App\Http\Controllers;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Providers\servce;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function showProfileById(Request $request, $id)
    {
        //Bearer Token
        $token = $request->bearerToken();

        if (!isset($token)) {
            return response()->json([
                'message' => 'token is not found'
            ]);
        }

        $decoded =(new servce)->decodeToken($request->token);
        $userId = $decoded->data;
       
        if ($userId == $id) {

            $user = User::find($id);

            if (isset($user)) {
                return $user;
            } else {
                return response()->json([
                    'message' => 'user No found'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'You are not authorized'
            ], 401);
        }
    }

    //update users data
    
    public function updateById(Request $request, $id)
    {
        //Bearer Token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => 'token not found'
            ]);
        }

        $decoded =(new servce)->decodeToken($request->token);
        $userId = $decoded->data;
       
        if ($userId == $id) {
            $user = User::find($id);
            $user->update($request->name);
            $user->update($request->email);
            $user->update($request->password);
            $user->save();

            return $user;
        } else {
            return response()->json([
                'message' => 'You are not authorized'
            ], 401);
        }
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }

    // delete users data by id
    
    public function deleteById(Request $request, $id)
    {
        // Bearer Token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response()->json([
                'message' => 'token not found'
            ]);
        }

        $decoded =(new servce)->decodeToken($request->token);
        $userId = $decoded->data;
       
        if ($userId == $id) {
            $user = User::destroy($id);

            if ($user == 1) {
                return response()->json([
                    'message' => 'User deleted'
                ]);
            } elseif ($user == 0) {
                return response()->json([
                    'message' => 'Already deleted'
                ]);
            } else {
                return response()->json([
                    'message' => 'user Not found'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'You not authorized '
            ], 401);
        }
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }

    //users searchBy name
    
    public function searchByName($name)
    {
        try{
        return User::where('name', 'like', '%' . $name . '%')->fisrt();
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }
}
