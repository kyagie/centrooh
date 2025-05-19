<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            !$request->user() ||
            !$request->user()->agent ||
            $request->user()->role !== 'agent'
        ) {
            return response()->json([
                'message' => 'You must be an agent to access this resource.'
            ], 403);
        }

        return $next($request);
    }
}
