<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use App\Models\User;

class JwtAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $auth = $request->header('Authorization');
        if (!$auth || !preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $token = $matches[1];
        try {
            $payload = JWT::decode($token, env('JWT_SECRET', 'change-me'), ['HS256']);
            $user = User::find($payload->sub);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Check if token jti is revoked
            if (property_exists($payload, 'jti')) {
                $exists = \App\Models\RevokedToken::where('jti', $payload->jti)->exists();
                if ($exists) {
                    return response()->json(['success' => false, 'message' => 'Token revoked'], 401);
                }
            }

            // Attach user to request
            $request->attributes->set('auth_user', $user);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unauthorized', 'err' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
