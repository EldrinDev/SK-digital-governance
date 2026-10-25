<?php

namespace App\Http\Middleware;

use App\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only let accounts with one of the given roles through, e.g. `role:officer`.
 */
class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowedRoles = array_map(fn (string $role): UserRole => UserRole::from($role), $roles);

        abort_unless(in_array($request->user()?->role, $allowedRoles, true), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
