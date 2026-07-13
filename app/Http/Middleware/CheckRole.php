<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Redirect user to their correct home dashboard based on their role.
     */
    private function redirectToDashboard(string $role, string $errorMessage): Response
    {
        $route = match ($role) {
            'admin', 'doctor' => 'admin.dashboard',
            'receptionist'    => 'receptionist.dashboard',
            'patient'         => 'patient.dashboard',
            default           => 'home',
        };

        return redirect()->route($route)->with('error', $errorMessage);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Not authenticated at all
        if (! Auth::check()) {
            if ($request->is('receptionist/*') || $request->is('receptionist')) {
                return redirect()->route('receptionist.login');
            }

            if ($request->is('admin/*') || $request->is('admin')) {
                return redirect()->route('login');
            }

            return redirect()->route('patient.login');
        }

        $user = Auth::user();

        // User is authenticated but does not have the required role
        if (! in_array($user->role, $roles, true)) {
            return $this->redirectToDashboard(
                $user->role,
                'Bạn không có quyền truy cập trang này.'
            );
        }

        return $next($request);
    }
}
