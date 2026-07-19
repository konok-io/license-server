<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AuditLog::class);

        return view('admin.audit-logs.index');
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search): void {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('actor_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        foreach (['event', 'actor_type'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $total = AuditLog::count();
        $filtered = (clone $query)->count();

        $logs = $query
            ->orderByDesc('id')
            ->offset((int) $request->input('start', 0))
            ->limit((int) $request->input('length', 15))
            ->get();

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $logs->map(fn (AuditLog $log): array => [
                'id'           => $log->id,
                'event'        => $log->event->value,
                'description'  => $log->description,
                'actor'        => $log->actor_name,
                'actor_type'   => $log->actor_type,
                'ip_address'   => $log->ip_address,
                'chain_valid'  => $log->isChainValid(),
                'created_at'   => $log->created_at?->format('Y-m-d H:i:s'),
            ]),
        ]);
    }

    public function show(AuditLog $auditLog): JsonResponse
    {
        $this->authorize('view', $auditLog);

        return response()->json([
            'audit' => [
                'id'            => $auditLog->id,
                'uuid'          => $auditLog->uuid,
                'event'         => $auditLog->event->value,
                'description'   => $auditLog->description,
                'actor'         => $auditLog->actor_name,
                'actor_type'    => $auditLog->actor_type,
                'ip_address'    => $auditLog->ip_address,
                'old_values'    => $auditLog->old_values,
                'new_values'    => $auditLog->new_values,
                'meta'          => $auditLog->meta,
                'previous_hash' => $auditLog->previous_hash,
                'hash'          => $auditLog->hash,
                'chain_valid'   => $auditLog->isChainValid(),
                'created_at'    => $auditLog->created_at?->toIso8601String(),
            ],
        ]);
    }

    /** Verify the integrity of the entire audit chain. */
    public function verifyChain(): JsonResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        $broken = [];
        $previousHash = null;

        AuditLog::query()->orderBy('id')->chunk(500, function ($chunk) use (&$broken, &$previousHash): void {
            foreach ($chunk as $log) {
                if ($log->previous_hash !== $previousHash || ! $log->isChainValid()) {
                    $broken[] = $log->id;
                }
                $previousHash = $log->hash;
            }
        });

        return response()->json([
            'intact'       => $broken === [],
            'broken_links' => $broken,
            'message'      => $broken === []
                ? 'Audit chain is intact and tamper-free.'
                : 'Audit chain integrity check FAILED.',
        ]);
    }
}
