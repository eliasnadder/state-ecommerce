<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // التحقق أن المستخدم موجود وأن نوعه admin
        if (!$user || $user->type !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return $next($request);
    }
}
