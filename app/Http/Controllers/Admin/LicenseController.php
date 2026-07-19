<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\License\KillLicenseRequest;
use App\Http\Requests\License\StoreLicenseRequest;
use App\Http\Requests\License\UpdateLicenseRequest;
use App\Models\Customer;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseController extends Controller
{
    public function __construct(private readonly LicenseService $service)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', License::class);

        $customers = Customer::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'company']);

        return view('admin.licenses.index', [
            'customers' => $customers,
            'counts'    => $this->service->dashboardCounts(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', License::class);

        $query = License::query()->with('customer:id,name,company');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('license_key_prefix', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        foreach (['status', 'type'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $total = License::count();
        $filtered = (clone $query)->count();

        $licenses = $query
            ->orderBy($request->input('sort', 'created_at'), $request->input('dir', 'desc'))
            ->offset((int) $request->input('start', 0))
            ->limit((int) $request->input('length', 15))
            ->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $licenses->map(fn (License $l): array => [
                'id'               => $l->id,
                'uuid'             => $l->uuid,
                'key_prefix'       => $l->license_key_prefix,
                'customer'         => $l->customer?->name,
                'type'             => $l->type->value,
                'type_label'       => $l->type->label(),
                'status'           => $l->status->value,
                'status_label'     => $l->status->label(),
                'activations'      => "{$l->activation_count}/{$l->max_activations}",
                'kill_switch'      => $l->kill_switch,
                'expires_at'       => $l->expires_at?->format('Y-m-d'),
                'last_verified_at' => $l->last_verified_at?->diffForHumans(),
            ]),
        ]);
    }

    public function store(StoreLicenseRequest $request): JsonResponse
    {
        $result = $this->service->issue($request->validated());

        return response()->json([
            'message'   => 'License issued successfully.',
            'license'   => $result['license'],
            // Plaintext key returned exactly once — the client must store it now.
            'plain_key' => $result['plain_key'],
        ], 201);
    }

    public function show(License $license): JsonResponse
    {
        $this->authorize('view', $license);

        $license->load('customer:id,name,company,email')
            ->loadCount(['activations', 'verifications', 'resets']);

        return response()->json(['license' => $license]);
    }

    public function update(UpdateLicenseRequest $request, License $license): JsonResponse
    {
        $license = $this->service->update($license, $request->validated());

        return response()->json([
            'message' => 'License updated successfully.',
            'license' => $license,
        ]);
    }

    public function destroy(License $license): JsonResponse
    {
        $this->authorize('delete', $license);

        $this->service->delete($license);

        return response()->json(['message' => 'License deleted successfully.']);
    }

    public function kill(KillLicenseRequest $request, License $license): JsonResponse
    {
        $license = $this->service->kill($license, $request->validated('reason'));

        return response()->json([
            'message' => 'Kill switch engaged. License will be blocked on next verification.',
            'license' => $license,
        ]);
    }

    public function reactivate(License $license): JsonResponse
    {
        $this->authorize('update', $license);

        $license = $this->service->reactivate($license);

        return response()->json([
            'message' => 'License reactivated.',
            'license' => $license,
        ]);
    }

    public function suspend(License $license): JsonResponse
    {
        $this->authorize('update', $license);

        $license = $this->service->suspend($license);

        return response()->json([
            'message' => 'License suspended.',
            'license' => $license,
        ]);
    }
}
