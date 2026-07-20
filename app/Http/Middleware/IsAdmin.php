<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        if (in_array(Auth::user()->role, ['admin', 'superadmin'])) {
            return $next($request);
        }

        if ($request->is('admin*') || $request->is('dashboard')) {
            return redirect('/')->with('status', 'Akses terhad untuk Admin sahaja.');
        }

        return $next($request);
    }
}
