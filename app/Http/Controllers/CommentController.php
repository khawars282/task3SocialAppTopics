<?php

namespace App\Http\Controllers;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Token;
use App\Models\ReceivedFriendRequest;
use App\Models\SentFriendRequest;
use App\Notifications\CommentOnYourPost;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;
use App\Http\Providers\servce;
use Firebase\JWT\Key;
CommentFormRequest;
class CommentController extends Controller
{
    public function create(CommentFormRequest $request, $id)
    {
        // Token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => ' token not found'
            ]);
        }

        $decoded =(new servce)->decodeToken($token);
        $userId = $decoded->data;

        $validator =$request->validated();
        $validator = $request->safe()->only('content');
        if (!isset($validator)) {
            return response()->json(['error' => $validator->messages()], 403);
        }
        
        $getPost = Post::find($id);

        //user id of author this post
        $author = $getPost->user_id;

        //user
        $user = User::where('id', $author)->first();

       
        //user id commenter
        $commenter = $getComment->user_id;

        
        if ( $author == $userId || $commenter == $userId) {

            $commentCreated =  Comment::create([
                'user_id' => $userId,
                'post_id' => $id,
                'content' => $request->content,
            ]);
            return $commentCreated;
        } else {
            return response()->json([
                'message' => 'not allowed comment on this post'
            ], 404);
        }
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }


    
    // Update comment
    
    public function update(Request $request, $id)
    {
        //Token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => 'token not found'
            ]);
        }
        $validator =$request->validated();
        $validator = $request->safe()->only('content');
        
        if (!isset($validator)) {
            return response()->json(['error' => $validator->messages()], 403);
        }
         $decoded =(new servce)->decodeToken($token);
        $userId = $decoded->data;
        
        // comment
        $getComment = Comment::find($id);

        if (!$getComment) {
            return response([
                'message' => 'Comment not exists'
            ]);
        }

        //Get Post id
        $postId = $getComment->post_id;
        $getPost = Post::find($postId);

        $author = $getPost->user_id;

        
        //user id commenter
        $commenter = $getComment->user_id;

        
        if ( $author == $userId || $commenter == $userId) {

            $comment = Comment::where('id', $id)->where('user_id', $userId)->first();

            if ($comment) {
                $comment->content = $request->content;
                $comment->update();

                return $comment;
            } else {
                return response()->json([
                    'message' => 'Something wrong'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'you Not allowed update comment'
            ], 401);
        }
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }


  
    public function delete(Request $request, $id)
    {
        //Bearer Token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response()->json([
                'message' => 'token not found'
            ]);
        }
        $decoded =(new servce)->decodeToken($token);
        $userId = $decoded->data;
        //comment
        $getComment = Comment::find($id);

        if (!$getComment) {
            return response()->json([
                'message' => 'Comment does not exist'
            ]);
        }
        //Post id
        $postId = $getComment->post_id;
        $getPost = Post::find($postId);

        //user id author of this post
        $author = $getPost->user_id;

        //user id commenter
        $commenter = $getComment->user_id;

        
        if ( $author == $userId || $commenter == $userId) {

            $comment = Comment::where('id', $id)->where('user_id', $userId)->first();

            if ($comment) {
                $comment->delete();

                return response([
                    'message' => 'Comment deleted',
                    'comment' => $comment
                ]);
            } else {
                return response()->json([
                    'message' => 'You not have a comment on this post'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'You not allowed comment on this post'
            ], 401);
        }
        }
        catch(Throwable $ex)
        {
            return array('Massage'=>$ex->getMessage());
        }
    }


    public function showComments(Request $request)
    {
        //Bearer Token
        $token = $request->bearerToken();
        try{
        if (!isset($token)) {
            return response([
                'message' => 'Bearer token not found'
            ]);
        }
        $decoded =(new servce)->decodeToken($token);
        $userId = $decoded->data;

        $comments = Comment::where('user_id', $userId)->get();

        return $comments;
    }
    
    catch(Throwable $ex)
    {
        return array('Massage'=>$ex->getMessage());
    }
    }
}