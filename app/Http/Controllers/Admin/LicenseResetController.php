<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reset\StoreResetRequest;
use App\Models\License;
use App\Models\LicenseReset;
use App\Services\LicenseResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseResetController extends Controller
{
    public function __construct(private readonly LicenseResetService $service)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', License::class);

        return view('admin.resets.index');
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', License::class);

        $query = LicenseReset::query()->with('license:id,license_key_prefix');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('performed_by_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('license_id')) {
            $query->where('license_id', $request->integer('license_id'));
        }

        $total = LicenseReset::count();
        $filtered = (clone $query)->count();

        $resets = $query
            ->orderByDesc('reset_at')
            ->offset((int) $request->input('start', 0))
            ->limit((int) $request->input('length', 15))
            ->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $resets->map(fn (LicenseReset $r): array => [
                'id'                  => $r->id,
                'license'             => $r->license?->license_key_prefix,
                'reason'              => $r->reason,
                'activations_cleared' => $r->activations_cleared,
                'key_rotation'        => "{$r->old_rsa_key_version} → {$r->new_rsa_key_version}",
                'performed_by'        => $r->performed_by_name,
                'ip_address'          => $r->ip_address,
                'reset_at'            => $r->reset_at?->format('Y-m-d H:i:s'),
            ]),
        ]);
    }

    /** Trigger a reset for a specific license. */
    public function store(StoreResetRequest $request, License $license): JsonResponse
    {
        $reset = $this->service->reset($license, $request->validated('reason'));

        return response()->json([
            'message' => 'License reset successfully. RSA key rotated and activations cleared.',
            'reset'   => $reset,
        ], 201);
    }
}
