<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): mixed
    {
        // Not logged in at all
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userJobTitle = Auth::user()->job_title;  // ← your column is job_title

        foreach ($roles as $role) {
            if (str_contains(strtolower($userJobTitle), strtolower($role))) {
                return $next($request);
            }
        }

        // Logged in but wrong role
        abort(403, 'You do not have permission to access this page.');
    }
}