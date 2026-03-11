<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Hash;

class CheckDefaultPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $defaultPassword = '12345678';

        // Check for Student role
        if ($user->hasRole('Student')) {
            // Check if student has default password OR must_change_password flag
            if (Hash::check($defaultPassword, $user->password) || $user->must_change_password) {
                $allowedRoutes = [
                    'student.change-password',
                    'student.update-password',
                    'logout',
                    'logout.get'
                ];
                
                $currentRoute = $request->route() ? $request->route()->getName() : null;
                
                if (!in_array($currentRoute, $allowedRoutes) &&
                    !$request->is('student/change-password') &&
                    !$request->is('student/update-password') &&
                    !$request->is('logout')) {
                    return redirect()->route('student.change-password');
                }
            }
            // Return early for students to prevent the general must_change_password check
            return $next($request);
        }

        // Check for Teacher role - only redirect if BOTH conditions are true:
        // 1. Password is still default (12345678)
        // 2. Email is still placeholder (teacher_*@placeholder.co.zw)
        if ($user->hasRole('Teacher')) {
            $isDefaultPassword = Hash::check($defaultPassword, $user->password);
            $isPlaceholderEmail = str_contains($user->email, '@placeholder.co.zw');
            
            if ($isDefaultPassword && $isPlaceholderEmail) {
                $allowedRoutes = [
                    'teacher.change-password',
                    'teacher.update-password',
                    'logout',
                    'logout.get'
                ];
                
                $currentRoute = $request->route() ? $request->route()->getName() : null;
                
                if (!in_array($currentRoute, $allowedRoutes) &&
                    !$request->is('teacher/change-password') &&
                    !$request->is('teacher/update-password') &&
                    !$request->is('logout')) {
                    return redirect()->route('teacher.change-password')
                        ->with('warning', 'Please complete your profile and change your default password to continue.');
                }
            }
            // Return early for teachers to prevent the general must_change_password check
            return $next($request);
        }

        // Check must_change_password flag for other users (Admin, Parents, etc.)
        if ($user->must_change_password) {
            $allowedRoutes = [
                'user.force-change-password',
                'user.force-change-password.update',
                'logout',
                'logout.get'
            ];
            
            $currentRoute = $request->route() ? $request->route()->getName() : null;
            
            if (!in_array($currentRoute, $allowedRoutes) && 
                !$request->is('user/force-change-password') &&
                !$request->is('logout')) {
                return redirect()->route('user.force-change-password')
                    ->with('warning', 'Please change your default password to continue.');
            }
        }

        return $next($request);
    }
}
