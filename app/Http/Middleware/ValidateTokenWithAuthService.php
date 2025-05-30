<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class ValidateTokenWithAuthService
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization') ?? $request->header('authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized - token missing in header'], 401);
        }

        $token = substr($authHeader, 7); // Extrae solo el token
        $cacheKey = "jwt_valid:$token";

        // Buscar en Redis
        if ($cached = Redis::connection('shared')->get($cacheKey)) {
            $request->merge(['auth_user' => json_decode($cached, true)]);
            return $next($request);
        }

        try {
            // Llamada al microservicio AUTH
            $response = Http::withHeaders([
                'Authorization' => $authHeader
            ])->get(env('AUTH_SERVICE_URL') . '/me');

            if ($response->status() !== 200) {
                if ($response->status() === 401 && $response->json('message') === 'token expired') {
                    return response()->json([
                        'error' => 'token expired',
                        'message' => 'token expired',
                        'redirect' => $response->json('redirect')
                    ], 401);
                }

                return response()->json(['error' => 'Unauthorized - invalid token'], 401);
            }

            // Token válido
            $response_json = $response->json();
            $data = $response_json['data'] ?? null;

            if (!$data || !isset($data['expires_at'])) {
                return response()->json(['error' => 'Invalid auth response (no expires_at)'], 500);
            }

            // Guardar en Redis con TTL
            $expiresAt = Carbon::parse($data['expires_at'])->setTimezone('UTC');
            $now = Carbon::now('UTC');
            $ttl = $expiresAt->timestamp - $now->timestamp; // segundos hasta expiración
            if($ttl > 120){
                $ttl = 120; // Limitar TTL a 2 minutos para evitar problemas de sincronización
            }
            // dd('Auth user data from service', $data, 'TTL:', $ttl, 'now:', now(), 'expires_at:', $expiresAt);
            if ($ttl > 0) {
                Redis::connection('shared')->setex($cacheKey, $ttl, json_encode($data));
            }

            $request->merge(['auth_user' => $data]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Auth service unavailable'], 503);
        }

        return $next($request);
    }
}
