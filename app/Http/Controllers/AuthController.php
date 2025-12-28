<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Firebase\JWT\JWT;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $v = Validator::make($request->all(), [
            'phone' => 'nullable|string',
            'username' => 'required|string|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $user = User::create([
            'phone' => $request->input('phone'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password_hash' => password_hash($request->input('password'), PASSWORD_BCRYPT),
        ]);

        $created = User::find($user);

        $token = $this->generateToken($created);

        return response()->json(['success' => true, 'data' => ['token' => $token, 'user' => $created]]);
    }

    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'phone' => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'required|string',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $identifier = $request->input('phone') ?: $request->input('username');

        if ($request->has('phone')) {
            $user = User::where('phone', $request->input('phone'))->first();
        } else {
            $user = User::where('username', $request->input('username'))->first();
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        if (!$user->checkPassword($request->input('password'))) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        $token = $this->generateToken($user);

        return response()->json(['success' => true, 'data' => ['token' => $token, 'user' => $user]]);
    }

    public function refresh(Request $request)
    {
        $auth = $request->header('Authorization');
        if (!$auth || !preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $token = $matches[1];
        try {
            $payload = JWT::decode($token, env('JWT_SECRET', 'change-me'), ['HS256']);
            // check if token already revoked
            if (property_exists($payload, 'jti') && \App\Models\RevokedToken::where('jti', $payload->jti)->exists()) {
                return response()->json(['success' => false, 'message' => 'Token revoked'], 401);
            }

            $user = User::find($payload->sub);
            if (!$user) return response()->json(['success' => false, 'message' => 'User not found'], 404);

            // Revoke old token
            if (property_exists($payload, 'jti')) {
                \App\Models\RevokedToken::create(['jti' => $payload->jti, 'expires_at' => date('Y-m-d H:i:s', $payload->exp ?? time())]);
            }

            // Issue new token
            $newToken = $this->generateToken($user);
            return response()->json(['success' => true, 'data' => ['token' => $newToken]]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unauthorized', 'err' => $e->getMessage()], 401);
        }
    }

    public function logout(Request $request)
    {
        $auth = $request->header('Authorization');
        if (!$auth || !preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $token = $matches[1];
        try {
            $payload = JWT::decode($token, env('JWT_SECRET', 'change-me'), ['HS256']);
            if (property_exists($payload, 'jti')) {
                \App\Models\RevokedToken::create(['jti' => $payload->jti, 'expires_at' => date('Y-m-d H:i:s', $payload->exp ?? time())]);
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    public function profile(Request $request)
    {
        $user = $request->attributes->get('auth_user');
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function verifyPhone(Request $request)
    {
        $v = Validator::make($request->all(), [
            'phone' => 'required_without:user_id',
            'user_id' => 'required_without:phone',
            'code' => 'required|string'
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        // For now: accept any code and mark user as verified
        if ($request->has('user_id')) {
            $user = User::find($request->input('user_id'));
        } else {
            $user = User::where('phone', $request->input('phone'))->first();
        }
        if (!$user) return response()->json(['success' => false, 'message' => 'User not found'], 404);

        $user->is_verified = true;
        $user->save();

        return response()->json(['success' => true, 'data' => ['user' => $user]]);
    }

    protected function generateToken(User $user)
    {
        $jti = bin2hex(random_bytes(16));
        $payload = [
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24 * 7, // 7 days
            'jti' => $jti
        ];
        $secret = env('JWT_SECRET', 'change-me');
        return JWT::encode($payload, $secret, 'HS256');
    }
}
