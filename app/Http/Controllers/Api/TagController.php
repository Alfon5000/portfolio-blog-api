<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Tags data retrieved successfully',
            'data' => Tag::with(['posts'])->latest()->paginate(10),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'unique:tags,name'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tag = Tag::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'data' => $tag,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tag = Tag::with(['posts'])->find($id);

        if ($tag) {
            return response()->json([
                'success' => true,
                'message' => 'Tag data retrieved successfully',
                'data' => $tag,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tag not found',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $tag = Tag::find($id);

        if ($tag) {

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255', 'unique:tags,name,' . $tag->id],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $tag->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Tag updated successfully',
                'data' => $tag,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tag not found',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tag = Tag::find($id);

        if ($tag) {
            $tag->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tag deleted successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tag not found',
        ], 404);
    }
}
