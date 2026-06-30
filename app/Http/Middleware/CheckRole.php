<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            if ($request->is('admin/*') || $request->is('admin')) {
                return redirect()->route('login');
            }

            return redirect()->route('patient.login');
        }

        $user = Auth::user();

        if (! in_array($user->role, $roles, true)) {
            if ($user->role === 'patient') {
                return redirect()
                    ->route('patient.dashboard')
                    ->with('error', 'Bạn không có quyền truy cập khu vực quản trị.');
            }

            if (in_array($user->role, ['admin', 'doctor', 'receptionist'], true)) {
                return redirect()
                    ->route('admin.dashboard')
                    ->with('error', 'Bạn không có quyền truy cập trang này.');
            }

            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
