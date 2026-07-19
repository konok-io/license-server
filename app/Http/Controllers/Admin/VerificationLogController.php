<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\VerificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationLogController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', License::class);

        return view('admin.verification-logs.index');
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', License::class);

        $query = VerificationLog::query()->with('license:id,license_key_prefix');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('installation_id', 'like', "%{$search}%")
                    ->orWhere('normalized_domain', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        foreach (['result', 'license_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('kill_directive')) {
            $query->where('kill_directive', $request->boolean('kill_directive'));
        }

        $total = VerificationLog::count();
        $filtered = (clone $query)->count();

        $logs = $query
            ->orderByDesc('created_at')
            ->offset((int) $request->input('start', 0))
            ->limit((int) $request->input('length', 15))
            ->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $logs->map(fn (VerificationLog $log): array => [
                'id'              => $log->id,
                'license'         => $log->license?->license_key_prefix,
                'result'          => $log->result,
                'kill_directive'  => $log->kill_directive,
                'installation_id' => $log->installation_id,
                'domain'          => $log->normalized_domain,
                'ip_address'      => $log->ip_address,
                'latency_ms'      => $log->latency_ms,
                'created_at'      => $log->created_at?->format('Y-m-d H:i:s'),
            ]),
        ]);
    }
}
