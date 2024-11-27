<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $postId)
    {
        return response()->json([
            'status' => true,
            'message' => 'Comments data retrieved successfully',
            'data' => Comment::with(['commenter', 'post'])->postId($postId)->latest()->paginate(10),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $postId)
    {
        $validator = Validator::make($request->all(), [
            'body' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $validated['commenter_id'] = $request->user()->id;
        $validated['post_id'] = $postId;

        $comment = Comment::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Comment created successfully',
            'data' => $comment,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $postId, string $commentId)
    {
        $comment = Comment::with(['commenter', 'post'])->postId($postId)->find($commentId);

        if ($comment) {
            return response()->json([
                'status' => true,
                'message' => 'Comment data retrieved successfully',
                'data' => $comment,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Comment not found',
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $postId, string $commentId)
    {
        $comment = Comment::postId($postId)->find($commentId);

        Gate::authorize('update', $comment);

        if ($comment) {
            $validator = Validator::make($request->all(), [
                'body' => ['required', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $comment->update([
                'body' => $request->body,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Comment updated successfully',
                'data' => $comment,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Comment not found',
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $postId, string $commentId)
    {
        $comment = Comment::postId($postId)->find($commentId);

        Gate::authorize('delete', $comment);

        if ($comment) {
            $comment->delete();

            return response()->json([
                'status' => true,
                'message' => 'Comment deleted successfully',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Comment not found',
        ], 404);
    }
}
