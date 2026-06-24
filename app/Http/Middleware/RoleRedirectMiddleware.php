<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleRedirectMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if 'folder' is set in session
        if (!session()->has('folder')) {
            Log::info('Session "folder" not set. Proceeding without redirection.');
            return $next($request);
        }

        $folder = session()->get('folder');
        $currentPath = $request->path();

        // Log current path and redirection target
        Log::info('Current path: ' . $currentPath);
        Log::info('Intended redirection: ' . "{$folder}/dashboard");

        // Prevent redirect loop by checking current path
        if ($currentPath !== "{$folder}/dashboard") {
            Log::info('Redirecting to: ' . "{$folder}/dashboard");
            return redirect()->route('dashboard.folder', ['folder' => $folder]);
        }

        return $next($request);
    }
}