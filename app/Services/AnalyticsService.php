<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use App\Models\ActivationLog;
use App\Models\Customer;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\LicenseVerification;
use App\Models\VerificationLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Read-only aggregation for the analytics dashboard. Every method returns
 * plain arrays ready for JSON / Chart.js consumption. Queries are grouped and
 * indexed to stay cheap at scale.
 */
class AnalyticsService
{
    /* ----------------------------------------------------------------
     |  Headline statistics (stat cards)
     * ---------------------------------------------------------------- */

    /** @return array<string, int> */
    public function headlineStats(): array
    {
        $licenseByStatus = License::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->toArray();

        return [
            'total_customers'    => Customer::count(),
            'active_customers'   => Customer::where('is_active', true)->count(),
            'total_licenses'     => License::count(),
            'active_licenses'    => $licenseByStatus[LicenseStatus::Active->value] ?? 0,
            'suspended_licenses' => $licenseByStatus[LicenseStatus::Suspended->value] ?? 0,
            'killed_licenses'    => $licenseByStatus[LicenseStatus::Killed->value] ?? 0,
            'expired_licenses'   => $licenseByStatus[LicenseStatus::Expired->value] ?? 0,
            'active_activations' => LicenseActivation::query()->active()->count(),
            'verifications_today'=> LicenseVerification::whereDate('verified_at', today())->count(),
            'kills_today'        => VerificationLog::where('kill_directive', true)
                                        ->whereDate('created_at', today())->count(),
        ];
    }

    /* ----------------------------------------------------------------
     |  Distribution charts (pie / doughnut)
     * ---------------------------------------------------------------- */

    /** @return array{labels:array<string>, data:array<int>} */
    public function licenseStatusDistribution(): array
    {
        $counts = License::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->toArray();

        $labels = [];
        $data = [];
        foreach (LicenseStatus::cases() as $status) {
            $labels[] = $status->label();
            $data[] = $counts[$status->value] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /** @return array{labels:array<string>, data:array<int>} */
    public function licenseTypeDistribution(): array
    {
        $counts = License::query()
            ->selectRaw('type, COUNT(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type')
            ->toArray();

        $labels = [];
        $data = [];
        foreach (LicenseType::cases() as $type) {
            $labels[] = $type->label();
            $data[] = $counts[$type->value] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Server-type split of active installations (localhost / domain / vps).
     *
     * @return array{labels:array<string>, data:array<int>}
     */
    public function serverTypeDistribution(): array
    {
        $counts = LicenseActivation::query()
            ->active()
            ->selectRaw('server_type, COUNT(*) as aggregate')
            ->groupBy('server_type')
            ->pluck('aggregate', 'server_type')
            ->toArray();

        return [
            'labels' => array_map('ucfirst', array_keys($counts) ?: ['None']),
            'data'   => array_values($counts) ?: [0],
        ];
    }

    /* ----------------------------------------------------------------
     |  Time-series (line charts)
     * ---------------------------------------------------------------- */

    /**
     * Daily activation counts for the trailing $days window.
     *
     * @return array{labels:array<string>, data:array<int>}
     */
    public function activationTrend(int $days = 30): array
    {
        return $this->dailySeries(
            ActivationLog::query()->where('action', 'activate')->where('success', true),
            'created_at',
            $days,
        );
    }

    /**
     * Daily verification counts, split into success vs failed/kill.
     *
     * @return array{labels:array<string>, success:array<int>, failed:array<int>}
     */
    public function verificationTrend(int $days = 30): array
    {
        $start = today()->subDays($days - 1);

        $rows = LicenseVerification::query()
            ->where('verified_at', '>=', $start)
            ->selectRaw('DATE(verified_at) as day, result, COUNT(*) as aggregate')
            ->groupBy('day', 'result')
            ->get();

        $labels = [];
        $success = [];
        $failed = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $labels[] = $date;

            $dayRows = $rows->where('day', $date);
            $ok = (int) $dayRows->where('result', 'success')->sum('aggregate');
            $bad = (int) $dayRows->where('result', '!=', 'success')->sum('aggregate');

            $success[] = $ok;
            $failed[] = $bad;
        }

        return ['labels' => $labels, 'success' => $success, 'failed' => $failed];
    }

    /* ----------------------------------------------------------------
     |  Tabular reports
     * ---------------------------------------------------------------- */

    /**
     * Activation report: recent activation events with license + environment.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function activationReport(?Carbon $from = null, ?Carbon $to = null, int $limit = 500): Collection
    {
        return ActivationLog::query()
            ->with('license:id,license_key_prefix')
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ActivationLog $log): array => [
                'license'         => $log->license?->license_key_prefix,
                'action'          => $log->action,
                'success'         => $log->success,
                'installation_id' => $log->installation_id,
                'domain'          => $log->normalized_domain,
                'server_type'     => $log->server_type,
                'ip_address'      => $log->ip_address,
                'reason'          => $log->reason,
                'created_at'      => $log->created_at?->toDateTimeString(),
            ]);
    }

    /**
     * Verification report: recent verification events with the verdict result.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function verificationReport(?Carbon $from = null, ?Carbon $to = null, int $limit = 500): Collection
    {
        return VerificationLog::query()
            ->with('license:id,license_key_prefix')
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (VerificationLog $log): array => [
                'license'         => $log->license?->license_key_prefix,
                'result'          => $log->result,
                'kill_directive'  => $log->kill_directive,
                'installation_id' => $log->installation_id,
                'domain'          => $log->normalized_domain,
                'ip_address'      => $log->ip_address,
                'latency_ms'      => $log->latency_ms,
                'created_at'      => $log->created_at?->toDateTimeString(),
            ]);
    }

    /**
     * Top customers by license count (leaderboard widget).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function topCustomers(int $limit = 10): Collection
    {
        return Customer::query()
            ->withCount('licenses')
            ->orderByDesc('licenses_count')
            ->limit($limit)
            ->get()
            ->map(fn (Customer $c): array => [
                'name'     => $c->name,
                'company'  => $c->company,
                'licenses' => $c->licenses_count,
                'active'   => $c->is_active,
            ]);
    }

    /**
     * Licenses expiring within $days — an actionable renewal list.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function expiringSoon(int $days = 30): Collection
    {
        return License::query()
            ->with('customer:id,name')
            ->where('status', LicenseStatus::Active->value)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)])
            ->orderBy('expires_at')
            ->limit(50)
            ->get()
            ->map(fn (License $l): array => [
                'license'    => $l->license_key_prefix,
                'customer'   => $l->customer?->name,
                'expires_at' => $l->expires_at?->toDateString(),
                'days_left'  => (int) now()->diffInDays($l->expires_at, false),
            ]);
    }

    /* ----------------------------------------------------------------
     |  Shared helper
     * ---------------------------------------------------------------- */

    /**
     * Build a zero-filled daily count series over the trailing window.
     *
     * @param \Illuminate\Database\Eloquent\Builder<*> $query
     * @return array{labels:array<string>, data:array<int>}
     */
    private function dailySeries($query, string $column, int $days): array
    {
        $start = today()->subDays($days - 1);

        $counts = $query
            ->where($column, '>=', $start)
            ->selectRaw("DATE({$column}) as day, COUNT(*) as aggregate")
            ->groupBy('day')
            ->pluck('aggregate', 'day')
            ->toArray();

        $labels = [];
        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i)->toDateString();
            $labels[] = $date;
            $data[] = (int) ($counts[$date] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
