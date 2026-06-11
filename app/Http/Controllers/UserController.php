<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    public function index()
    {
        $users = User::latest()->get();

        return $this->successResponse(
            $users,
            'Data fetched successfully'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:owner,finance'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return $this->successResponse(
            $user,
            'User created successfully',
            201
        );
    }

    public function show(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse(
                'User not found',
                null,
                404
            );
        }

        return $this->successResponse(
            $user,
            'User fetched successfully'
        );
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:owner,finance'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role
        ]);

        return $this->successResponse(
            $user,
            'User updated successfully'
        );
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return $this->successResponse(
            null,
            'User deleted successfully'
        );
    }
}
