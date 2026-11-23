<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Modules\Shared\Domain\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if ($user === null) {
            abort(403, 'Akses ditolak. Silakan login terlebih dahulu.');
        }

        $allowed = array_map(static fn (string $role): UserRole => UserRole::from($role), $roles);
        if (! $user->hasAnyRole(...$allowed)) {
            abort(403, 'Peran akun Anda tidak memiliki izin mengakses modul ini.');
        }

        return $next($request);
    }
}
