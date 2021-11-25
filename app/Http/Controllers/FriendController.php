<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Token;
use App\Models\ReceivedFriendRequest;
use App\Models\SentFriendRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use App\Http\Providers\servce;

use Firebase\JWT\Key;

class FriendController extends Controller
{
    public function sendRequest(Request $request, $id)
    {
        //Bearer Token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => 'token not found'
            ]);
        }

        $userExists = User::where('id', $id)->first();

        if (!isset($userExists)) {
            return response([
                'message' => 'Request receiver does not exist'
            ]);
        }

        $decoded =(new servce)->decodeToken($request->token);
        $userId = $decoded->data;
       if ($userId == $id) {
            return response([
                'message' => 'You not send request yourself'
            ]);
        }
        $requestsSent = SentFriendRequest::all()->where('user_id', $userId)->where('receiver_id', $id)->first();
        $requestsReceived = ReceivedFriendRequest::all()->where('user_id', $userId)->where('sender_id', $id)->first();

        if ($requestsSent == null && $requestsReceived == null) {
            
            $saveFriendRequest1 = SentFriendRequest::create([
                'user_id' => $userId,
                'receiver_id' => $id,
                'status' => false
            ]);

            $saveFriendRequest2 = ReceivedFriendRequest::create([
                'sender_id' => $userId,
                'user_id' => $id,
                'status' => false
            ]);

            return response([
                'message' => 'Request sent to ' . $userExists->name. 'from'. $userId->name
            ]);
        } else {
            return response([
                'message' => 'Friend request is already pending'
            ]);
        }
            
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }

    public function showRequests(Request $request)
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
       

        $requestReceived =  ReceivedFriendRequest::all()->where('user_id', $userId);
        $requestSent =  SentFriendRequest::all()->where('user_id', $userId);

        if ((json_decode($requestReceived)) == null && (json_decode($requestSent)) == null) {
            return response([
                'message' => 'No friend requests'
            ]);
        } else {
            return response([
                'requests_sent' => $requestSent,
                'requests_received' => $requestReceived
            ]);
        }    
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }

    public function acceptRequestById(Request $request, $id)
    {
        //Get Bearer Token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => 'Token not found'
            ]);
        }

        $decoded =(new servce)->decodeToken($request->token);
        $userId = $decoded->data;
       

        $requestsReceived =  ReceivedFriendRequest::all()->where('user_id', $userId)->where('sender_id', $id)->first();

        $requestsSent =  SentFriendRequest::all()->where('user_id', $id)->where('receiver_id', $userId)->first();


        if (isset($requestsReceived)) {

            if ($requestsReceived->status ==  true && $requestsSent->status == true) {
                return response([
                    'message' => 'Already accepted'
                ]);
            }

            $requestsReceived->status = true;
            $requestsReceived->save();

            if (isset($requestsSent)) {
                $requestsSent->status = true;
                $requestsSent->save();
            }

            return response([
                'message' => 'Request accepted'
            ]);
        } else {
            return response([
                'message' => 'You not allowed perform this action'
            ]);
        }       
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }


    public function deleteRequestById(Request $request, $id)
    {
        //Bearer Token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }

        $decoded =(new servce)->decodeToken($request->token);
        $userId = $decoded->data;

        $requestSent =  SentFriendRequest::all()->where('user_id', $userId)->where('receiver_id', $id)->where('status', false)->first();

        $requestReceived =  ReceivedFriendRequest::all()->where('user_id', $userId)->where('sender_id', $id)->where('status', false)->first();

        

        if (isset($requestReceived)) {
            $requestReceived->delete();

            //Delete sent friend request
            $sentRequest =  SentFriendRequest::all()->where('user_id', $id)->first();
            $sentRequest->delete();

            return response([
                'message' => 'Request deleted'
            ]);
        }

        if (isset($requestSent)) {
            $requestSent->delete();


            $receivedRequest =  ReceivedFriendRequest::all()->where('user_id', $id)->first();
            $receivedRequest->delete();

            return response([
                'message' => 'You have unsent the request'
            ]);
        }

        return response([
            'message' => 'No such request exists'
        ]);    
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }


    
    public function removeFriendById(Request $request, $id)
    {
        //Bearer Token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => 'token not found'
            ]);
        }

        //Decode
        $decoded =(new servce)->decodeToken($request->token);
        $userId = $decoded->data;
       

        $requestSent = SentFriendRequest::all()->where('user_id', $userId)->where('receiver_id', $id)->where('status', true)->first();
        $requestReceived = ReceivedFriendRequest::all()->where('user_id', $userId)->where('sender_id', $id)->where('status', true)->first();


        if (isset($requestReceived)) {
            $requestReceived->delete();

            $sentRequest =  SentFriendRequest::all()->where('user_id', $id)->first();
            $sentRequest->delete();

            return response([
                'message' => 'You removed friend from your list'
            ]);
        }


        if (isset($requestSent)) {
            $requestSent->delete();

            $receivedRequest =  ReceivedFriendRequest::all()->where('user_id', $id)->first();
            $receivedRequest->delete();

            return response([
                'message' => 'You removed friend from list'
            ]);
        }

        return response([
            'message' => 'No friend exists'
        ]);   
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    } 
}
