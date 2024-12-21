<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function register(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users,email',
            'role' => 'in:client,admin,super_admin',
            'password' => 'required|string|min:8'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $req->get('name'),
            'email' => $req->get('email'),
            'role' => $req->get('role') ?? 'client',
            'password' => Hash::make($req->get('password')),
        ]);

        $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);
        return response()->json(compact('user', 'token'), 201);
    }

    public function login(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'email' => 'required|string|max:255,email',
            'password' => 'required|string|min:8'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $credentials = $req->only('email', 'password');

        try {
            $user = User::where('email', $req->get('email'))->first();
            if (!$user || !Hash::check($req->get('password'), $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password.',
                ], 401);
            }
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);
            return response()->json(compact('token'));
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    public function getUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        return response()->json(compact('user'));
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid or missing token'], 400);
        }
    }
}
