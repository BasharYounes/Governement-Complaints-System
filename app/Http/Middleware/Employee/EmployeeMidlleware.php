<?php

namespace App\Http\Middleware\Employee;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeMidlleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->bearerToken()) {
            return response()->json([
                "status" => false,
                "message" => " Login to access this resource"
            ], 401);
        }else{
            if (!auth()->guard('employee-api')->check()) {
                return response()->json([
                    "status" => false,
                    "message" => "Unauthorized Access"
                ], 401);
            }
            // ensure the default auth guard used by other middleware (eg. Spatie) is the employee guard
            auth()->shouldUse('employee-api');
        }
        return $next($request);

    }
}
