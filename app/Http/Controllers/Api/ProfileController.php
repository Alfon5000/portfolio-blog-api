<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Profile data retrieved successfully',
            'data' => auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'current_password' => ['sometimes', 'string', 'min:8', 'max:255', 'current_password'],
            'password' => ['sometimes', 'string', 'min:8', 'max:255', 'confirmed'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
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
            'message' => 'Profile updated successfully',
            'data' => $user,
        ]);
    }

    public function destroy()
    {
        $user = auth()->user();

        Storage::delete('images/users/' . $user->image);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profile deleted successfully',
            'data' => $user,
        ]);
    }
}
