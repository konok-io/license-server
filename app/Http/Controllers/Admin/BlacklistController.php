<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blacklist\StoreBlacklistRequest;
use App\Models\License;
use App\Models\LicenseBlacklist;
use App\Services\BlacklistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlacklistController extends Controller
{
    public function __construct(private readonly BlacklistService $service)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', LicenseBlacklist::class);

        $licenses = License::query()->orderByDesc('created_at')->limit(200)
            ->get(['id', 'license_key_prefix']);

        return view('admin.blacklists.index', ['licenses' => $licenses]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LicenseBlacklist::class);

        $query = LicenseBlacklist::query()->with('license:id,license_key_prefix');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('installation_id', 'like', "%{$search}%")
                    ->orWhere('normalized_domain', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $total = LicenseBlacklist::count();
        $filtered = (clone $query)->count();

        $entries = $query
            ->orderByDesc('blacklisted_at')
            ->offset((int) $request->input('start', 0))
            ->limit((int) $request->input('length', 15))
            ->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $entries->map(fn (LicenseBlacklist $e): array => [
                'id'              => $e->id,
                'license'         => $e->license?->license_key_prefix,
                'installation_id' => $e->installation_id,
                'domain'          => $e->normalized_domain,
                'ip_address'      => $e->ip_address,
                'reason'          => $e->reason,
                'is_active'       => $e->is_active,
                'created_by'      => $e->created_by_name,
                'blacklisted_at'  => $e->blacklisted_at?->format('Y-m-d H:i:s'),
            ]),
        ]);
    }

    public function store(StoreBlacklistRequest $request): JsonResponse
    {
        $entry = $this->service->add(
            $request->validated(),
            $request->boolean('kill_license', true),
        );

        return response()->json([
            'message' => 'Blacklist entry created.',
            'entry'   => $entry,
        ], 201);
    }

    public function lift(LicenseBlacklist $blacklist): JsonResponse
    {
        $this->authorize('update', $blacklist);

        $entry = $this->service->lift($blacklist);

        return response()->json([
            'message' => 'Blacklist entry lifted.',
            'entry'   => $entry,
        ]);
    }

    public function destroy(LicenseBlacklist $blacklist): JsonResponse
    {
        $this->authorize('delete', $blacklist);

        $this->service->delete($blacklist);

        return response()->json(['message' => 'Blacklist entry removed.']);
    }
}
