<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Users data retrieved successfully',
            'data' => User::latest()->paginate(10),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
            'is_admin' => ['required', 'boolean'],
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
            $request->file('image')->store('images/users');
            $validated['image'] = $request->file('image')->hashName();
        }

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with(['posts'])->find($id);

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'User data retrieved successfully',
                'data' => $user,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if ($user) {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'current_password' => ['sometimes', 'string', 'min:8', 'max:255', 'current_password'],
                'password' => ['sometimes', 'string', 'min:8', 'max:255', 'confirmed'],
                'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
                'is_admin' => ['required', 'boolean'],
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
                Storage::delete('images/users/' . $user->image);
                $request->file('image')->store('images/users');
                $validated['image'] = $request->file('image')->hashName();
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if ($user) {
            Storage::delete('images/users/' . $user->image);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }
}
