<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Analytics dashboard: headline stats, chart data feeds, and tabular reports.
 * The dashboard view loads once; charts and reports hydrate over AJAX.
 */
class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics)
    {
        // Reuse the license view permission for dashboard access.
    }

    public function index(): View
    {
        $this->authorize('viewAny', License::class);

        return view('admin.analytics.index', [
            'stats' => $this->analytics->headlineStats(),
        ]);
    }

    /** GET /admin/analytics/stats */
    public function stats(): JsonResponse
    {
        $this->authorize('viewAny', License::class);

        return response()->json($this->analytics->headlineStats());
    }

    /** GET /admin/analytics/charts */
    public function charts(Request $request): JsonResponse
    {
        $this->authorize('viewAny', License::class);

        $days = (int) $request->integer('days', 30);
        $days = max(7, min($days, 180));

        return response()->json([
            'status_distribution'      => $this->analytics->licenseStatusDistribution(),
            'type_distribution'        => $this->analytics->licenseTypeDistribution(),
            'server_type_distribution' => $this->analytics->serverTypeDistribution(),
            'activation_trend'         => $this->analytics->activationTrend($days),
            'verification_trend'       => $this->analytics->verificationTrend($days),
        ]);
    }

    /** GET /admin/analytics/reports/activations */
    public function activationReport(Request $request): JsonResponse
    {
        $this->authorize('viewAny', License::class);

        [$from, $to] = $this->dateRange($request);

        return response()->json([
            'data' => $this->analytics->activationReport($from, $to),
        ]);
    }

    /** GET /admin/analytics/reports/verifications */
    public function verificationReport(Request $request): JsonResponse
    {
        $this->authorize('viewAny', License::class);

        [$from, $to] = $this->dateRange($request);

        return response()->json([
            'data' => $this->analytics->verificationReport($from, $to),
        ]);
    }

    /** GET /admin/analytics/widgets */
    public function widgets(): JsonResponse
    {
        $this->authorize('viewAny', License::class);

        return response()->json([
            'top_customers' => $this->analytics->topCustomers(),
            'expiring_soon' => $this->analytics->expiringSoon(),
        ]);
    }

    /** @return array{0:?Carbon, 1:?Carbon} */
    private function dateRange(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')->toString())->startOfDay() : null;
        $to   = $request->filled('to') ? Carbon::parse($request->string('to')->toString())->endOfDay() : null;

        return [$from, $to];
    }
}
