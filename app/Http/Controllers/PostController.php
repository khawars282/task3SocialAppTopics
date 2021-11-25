<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Token;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;

use Firebase\JWT\Key;
use App\Http\Requests\PostsFormRequest;
use App\Http\Requests\PostsUpdateRequest;
use App\Http\Providers\servce;

class PostController extends Controller
{
    public function create(PostsFormRequest $request)
    {
        try{
        if (!isset($token)) {
            return response([
                'message' => 'token not found'
            ]);
        }
        $request->FromMiddleware=(array)$request->FromMiddleware;

        $userId = $request->FromMiddleware['data']; 

        $validator =$request->validated();
        $validator = $request->safe()->only('title', 'description');
        if (!isset($validator)) {
            return response()->json(['error' => $validator->messages()], 403);
        }
        //create new post   
        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
             'user_id' => $userId
        ]);

        //Post created, return success response
        return response()->json([
            'success' => true,
            'message' => 'Post created',
            'data' => $post
        ], Response::HTTP_OK);
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }

    public function showById(Request $request , $id)
    {
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => 'token not found'
            ]);
        }
        $decoded =(new servce)->decodeToken($token);
        $userId = $decoded->data;
        
        //find post
        $post = Post::where('user_id' , $userId)->where('id', $id)->first();

        //check no post
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'post not found.'
            ], 404);
        }
        return $post;
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }

    public function updateByTitle(PostsUpdateRequest $request, $title)
    {
        //token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => 'token not found'
            ]);
        }
        $decoded =(new servce)->decodeToken($token);
        $userId = $decoded->data;

        $validator =$request->validated();
        $validator = $request->safe()->only('title', 'description');
        if (!isset($validator)) {
            return response()->json(['error' => $validator->messages()], 403);
        }
        
        //find by title
        $post = Post::where('user_id' , $userId)->where('title', $title)->first();

        // update post
        $post = Post::update([
            'title' => $request->title,
            'description' => $request->description
        ]);

        //Post updated
        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $post
        ], Response::HTTP_OK);
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }


    public function destroyById(Request $request, $id)
    {
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => 'token not found'
            ]);
        }  
        $decoded =(new servce)->decodeToken($token);
        $userId = $decoded->data;

        $post = Post::where('user_id' , $userId)->where('id', $id)->first();
        
        //delete post
        $post->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Post deleted'
        ], Response::HTTP_OK);
        
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }        
    }
}
