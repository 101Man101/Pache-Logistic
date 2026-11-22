<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Keep shared props lean for fast Inertia v3 navigation.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $branches = [];
        $activeBranch = null;

        if ($user !== null && Schema::hasTable('branches')) {
            $branches = Branch::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->get(['id', 'code', 'name', 'city', 'type'])
                ->toArray();

            $activeBranchId = (int) ($request->session()->get('active_branch_id') ?: ($user->branch_id ?? 0));
            $activeBranch = collect($branches)->firstWhere('id', $activeBranchId) ?? ($branches[0] ?? null);
        }

        return [
            ...parent::share($request),
            'name' => config('app.name', 'Pache Logistic'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'branch_id' => $user->branch_id,
                ] : null,
            ],
            'branches' => $branches,
            'activeBranch' => $activeBranch,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
