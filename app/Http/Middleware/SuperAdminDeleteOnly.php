<?php

namespace App\Http\Middleware;

use Closure;

class SuperAdminDeleteOnly
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('delete')) {
            $user = auth()->user();
            $isSuperAdmin = $user && $user->is_super_admin;

            $libraryBookDeleteRoutes = ['admin.library.books.destroy'];
            $canDeleteLibraryBooks = $user
                && in_array(optional($request->route())->getName(), $libraryBookDeleteRoutes)
                && ($user->hasRole('Admin') || $user->can('sidebar-library-records'));

            if (!$isSuperAdmin && !$canDeleteLibraryBooks) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Only the Super Admin can delete records.'], 403);
                }
                return redirect()->back()->with('error', 'Only the Super Admin can delete records.');
            }
        }

        return $next($request);
    }
}
