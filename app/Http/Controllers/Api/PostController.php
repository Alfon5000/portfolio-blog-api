<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Posts data retrieved successfully',
            'data' => Post::with(['author', 'tags', 'comments', 'likers'])->latest()->paginate(10),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'author_id' => ['required'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'tags' => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('image')) {
            $request->file('image')->store('images/posts');
            $validated['image'] = $request->file('image')->hashName();
        }

        $post = Post::create($validated);
        $post->tags()->attach($validated['tags']);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'data' => $post,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::with(['author', 'tags', 'comments', 'likers'])->find($id);

        if ($post) {
            return response()->json([
                'success' => true,
                'message' => 'Post data retrieved successfully',
                'data' => $post,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Post not found',
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::find($id);

        Gate::authorize('update', $post);

        if ($post) {
            $validator = Validator::make($request->all(), [
                'author_id' => ['required'],
                'title' => ['required', 'string', 'max:255'],
                'body' => ['required', 'string', 'max:255'],
                'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
                'tags' => ['sometimes', 'array'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            if ($request->hasFile('image')) {
                Storage::delete('images/posts/' . $post->image);
                $request->file('image')->store('images/posts');
                $validated['image'] = $request->file('image')->hashName();
            }

            $post->update($validated);

            if ($validated['tags']) {
                $post->tags()->sync($validated['tags']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data' => $post,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Post not found',
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::find($id);

        Gate::authorize('delete', $post);

        if ($post) {
            Storage::delete('images/posts/' . $post->image);
            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Post not found',
        ], 404);
    }

    public function like(Request $request, string $id)
    {
        $post = Post::find($id);

        if ($post) {
            $post->likes()->attach($request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Post liked successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Post not found',
        ], 404);
    }

    public function unlike(Request $request, string $id)
    {
        $post = Post::find($id);

        if ($post) {
            $post->likes()->detach($request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Post unliked successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Post not found',
        ], 404);
    }
}
