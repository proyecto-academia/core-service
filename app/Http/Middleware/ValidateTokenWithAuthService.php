<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ValidateTokenWithAuthService
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized - token missing'], 401);
        }

        try {
            // Llamada al microservicio AUTH
            $response = Http::withHeaders([
                'Authorization' => $authHeader
            ])->get(env('AUTH_SERVICE_URL') . '/me');


            if ($response->status() !== 200) {
                return response()->json(['error' => 'Unauthorized - invalid token'], 401);
            }

            // Podés almacenar los datos del usuario en el request
            $request->merge(['auth_user' => $response->json()]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Auth service unavailable'], 503);
        }

        return $next($request);
    }
}
