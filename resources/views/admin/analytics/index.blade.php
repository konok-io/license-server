@extends('layouts.admin')
@section('title', 'Analytics Dashboard')

@push('styles')
<style>
    .metric-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; }
    .metric { background:#fff; border:1px solid var(--sls-line); border-radius:12px; padding:1.1rem 1.25rem; }
    .metric .val { font-size:1.7rem; font-weight:700; line-height:1; }
    .metric .lbl { color:var(--sls-muted); font-size:.75rem; text-transform:uppercase; letter-spacing:.06em; margin-top:.35rem; }
    .metric .accent { color: var(--sls-accent); }
    .chart-box { position:relative; height:300px; }
    .chart-box-sm { position:relative; height:260px; }
    .report-tabs .nav-link { color: var(--sls-muted); }
    .report-tabs .nav-link.active { color: var(--sls-accent); font-weight:600; }
</style>
@endpush

@section('content')
{{-- Headline metrics --}}
<div class="metric-grid mb-4" id="metricGrid">
    @php
        $metrics = [
            ['k'=>'active_licenses','l'=>'Active Licenses','a'=>true],
            ['k'=>'suspended_licenses','l'=>'Suspended'],
            ['k'=>'killed_licenses','l'=>'Killed'],
            ['k'=>'expired_licenses','l'=>'Expired'],
            ['k'=>'active_activations','l'=>'Live Installations','a'=>true],
            ['k'=>'active_customers','l'=>'Active Customers'],
            ['k'=>'verifications_today','l'=>'Verifies Today'],
            ['k'=>'kills_today','l'=>'Kills Today'],
        ];
    @endphp
    @foreach($metrics as $m)
    <div class="metric">
        <div class="val {{ ($m['a'] ?? false) ? 'accent' : '' }}" data-metric="{{ $m['k'] }}">{{ $stats[$m['k']] ?? 0 }}</div>
        <div class="lbl">{{ $m['l'] }}</div>
    </div>
    @endforeach
</div>

{{-- Trend charts --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-graph-up me-2"></i>Verification Trend</span>
                <select id="rangeSelect" class="form-select form-select-sm" style="width:auto">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>
            <div class="card-body"><div class="chart-box"><canvas id="verificationTrend"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart me-2"></i>License Status</div>
            <div class="card-body"><div class="chart-box"><canvas id="statusChart"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart me-2"></i>Activation Trend</div>
            <div class="card-body"><div class="chart-box-sm"><canvas id="activationTrend"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-diagram-3 me-2"></i>License Types</div>
            <div class="card-body"><div class="chart-box-sm"><canvas id="typeChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-hdd-network me-2"></i>Server Types (Live)</div>
            <div class="card-body"><div class="chart-box-sm"><canvas id="serverTypeChart"></canvas></div></div>
        </div>
    </div>
</div>

{{-- Widgets --}}
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-trophy me-2"></i>Top Customers</div>
            <div class="card-body p-0"><table class="table table-sm mb-0" id="topCustomers">
                <thead><tr><th class="ps-3">Customer</th><th>Company</th><th class="text-end pe-3">Licenses</th></tr></thead>
                <tbody></tbody>
            </table></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-hourglass-split me-2"></i>Expiring Soon (30 days)</div>
            <div class="card-body p-0"><table class="table table-sm mb-0" id="expiringSoon">
                <thead><tr><th class="ps-3">License</th><th>Customer</th><th>Expires</th><th class="text-end pe-3">Days</th></tr></thead>
                <tbody></tbody>
            </table></div>
        </div>
    </div>
</div>

{{-- Reports --}}
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs report-tabs" id="reportTabs">
            <li class="nav-item"><a class="nav-link active" data-report="activations" href="#"><i class="bi bi-box-arrow-in-right me-1"></i>Activation Report</a></li>
            <li class="nav-item"><a class="nav-link" data-report="verifications" href="#"><i class="bi bi-patch-check me-1"></i>Verification Report</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-auto"><input type="date" id="reportFrom" class="form-control form-control-sm"></div>
            <div class="col-auto"><input type="date" id="reportTo" class="form-control form-control-sm"></div>
            <div class="col-auto"><button class="btn btn-accent btn-sm" id="btnRunReport"><i class="bi bi-funnel"></i> Apply</button></div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm w-100" id="reportTable"><thead></thead><tbody></tbody></table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
$(function () {
    const PALETTE = {
        green:'#2e8b6f', amber:'#c98a1b', red:'#c0392b', grey:'#6b7a8d',
        blue:'#2e6ea8', purple:'#7d5ba6', teal:'#3aa6a0'
    };
    const charts = {};

    // ---- Chart builders ----
    function doughnut(id, labels, data, colors) {
        const ctx = document.getElementById(id);
        if (charts[id]) charts[id].destroy();
        charts[id] = new Chart(ctx, {
            type:'doughnut',
            data:{ labels, datasets:[{ data, backgroundColor: colors, borderWidth:0 }] },
            options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:12, font:{size:11} } } }, cutout:'62%' }
        });
    }

    function lineChart(id, labels, datasets) {
        const ctx = document.getElementById(id);
        if (charts[id]) charts[id].destroy();
        charts[id] = new Chart(ctx, {
            type:'line',
            data:{ labels, datasets },
            options:{ responsive:true, maintainAspectRatio:false, interaction:{ mode:'index', intersect:false },
                scales:{ x:{ ticks:{ maxTicksLimit:10, font:{size:10} }, grid:{ display:false } }, y:{ beginAtZero:true, ticks:{ precision:0 } } },
                plugins:{ legend:{ position:'top', labels:{ boxWidth:12, font:{size:11} } } } }
        });
    }

    function barChart(id, labels, data) {
        const ctx = document.getElementById(id);
        if (charts[id]) charts[id].destroy();
        charts[id] = new Chart(ctx, {
            type:'bar',
            data:{ labels, datasets:[{ label:'Activations', data, backgroundColor: PALETTE.green, borderRadius:4 }] },
            options:{ responsive:true, maintainAspectRatio:false,
                scales:{ x:{ ticks:{ maxTicksLimit:10, font:{size:10} }, grid:{ display:false } }, y:{ beginAtZero:true, ticks:{ precision:0 } } },
                plugins:{ legend:{ display:false } } }
        });
    }

    // ---- Load charts ----
    function loadCharts(days) {
        $.get('{{ route('admin.analytics.charts') }}', { days }, function (res) {
            const sd = res.status_distribution;
            doughnut('statusChart', sd.labels, sd.data,
                [PALETTE.grey, PALETTE.green, PALETTE.amber, PALETTE.grey, PALETTE.red, PALETTE.blue]);

            const td = res.type_distribution;
            doughnut('typeChart', td.labels, td.data, [PALETTE.blue, PALETTE.green, PALETTE.purple]);

            const st = res.server_type_distribution;
            doughnut('serverTypeChart', st.labels, st.data, [PALETTE.teal, PALETTE.green, PALETTE.amber, PALETTE.grey]);

            const vt = res.verification_trend;
            lineChart('verificationTrend', vt.labels, [
                { label:'Success', data:vt.success, borderColor:PALETTE.green, backgroundColor:'rgba(46,139,111,.1)', fill:true, tension:.3, pointRadius:0 },
                { label:'Failed / Kill', data:vt.failed, borderColor:PALETTE.red, backgroundColor:'rgba(192,57,43,.08)', fill:true, tension:.3, pointRadius:0 }
            ]);

            const at = res.activation_trend;
            barChart('activationTrend', at.labels, at.data);
        }).fail(slsHandleError);
    }

    // ---- Refresh metrics ----
    function loadStats() {
        $.get('{{ route('admin.analytics.stats') }}', function (s) {
            Object.entries(s).forEach(([k,v]) => $(`[data-metric="${k}"]`).text(v));
        });
    }

    // ---- Widgets ----
    function loadWidgets() {
        $.get('{{ route('admin.analytics.widgets') }}', function (res) {
            const tc = res.top_customers.map(c => `<tr><td class="ps-3">${c.name}</td><td>${c.company ?? '—'}</td><td class="text-end pe-3"><span class="badge-soft badge">${c.licenses}</span></td></tr>`).join('');
            $('#topCustomers tbody').html(tc || '<tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>');

            const es = res.expiring_soon.map(l => {
                const cls = l.days_left <= 7 ? 'text-danger fw-bold' : (l.days_left <= 14 ? 'text-warning' : '');
                return `<tr><td class="ps-3 mono">${l.license}…</td><td>${l.customer ?? '—'}</td><td>${l.expires_at}</td><td class="text-end pe-3 ${cls}">${l.days_left}</td></tr>`;
            }).join('');
            $('#expiringSoon tbody').html(es || '<tr><td colspan="4" class="text-center text-muted py-3">Nothing expiring soon</td></tr>');
        });
    }

    // ---- Reports ----
    let currentReport = 'activations';
    const reportCols = {
        activations: [['license','License'],['action','Action'],['success','OK'],['installation_id','Installation'],['domain','Domain'],['server_type','Server'],['ip_address','IP'],['created_at','Time']],
        verifications: [['license','License'],['result','Result'],['kill_directive','Kill'],['installation_id','Installation'],['domain','Domain'],['ip_address','IP'],['latency_ms','Latency'],['created_at','Time']]
    };

    function loadReport() {
        const url = currentReport === 'activations'
            ? '{{ route('admin.analytics.reports.activations') }}'
            : '{{ route('admin.analytics.reports.verifications') }}';
        const params = { from: $('#reportFrom').val(), to: $('#reportTo').val() };
        const cols = reportCols[currentReport];

        $('#reportTable thead').html('<tr>' + cols.map(c=>`<th>${c[1]}</th>`).join('') + '</tr>');

        $.get(url, params, function (res) {
            if (!res.data.length) { $('#reportTable tbody').html(`<tr><td colspan="${cols.length}" class="text-center text-muted py-4">No records in range</td></tr>`); return; }
            const rows = res.data.map(r => '<tr>' + cols.map(c => {
                let v = r[c[0]];
                if (c[0] === 'success') v = v ? '<span class="badge text-bg-success">OK</span>' : '<span class="badge text-bg-danger">Fail</span>';
                else if (c[0] === 'kill_directive') v = v ? '<i class="bi bi-x-octagon text-danger"></i>' : '—';
                else if (c[0] === 'license') v = v ? `<span class="mono">${v}…</span>` : '—';
                else if (c[0] === 'latency_ms') v = v != null ? `${v} ms` : '—';
                else v = v ?? '—';
                return `<td>${v}</td>`;
            }).join('') + '</tr>').join('');
            $('#reportTable tbody').html(rows);
        }).fail(slsHandleError);
    }

    $('#reportTabs .nav-link').on('click', function (e) {
        e.preventDefault();
        $('#reportTabs .nav-link').removeClass('active'); $(this).addClass('active');
        currentReport = $(this).data('report');
        loadReport();
    });
    $('#btnRunReport').on('click', loadReport);
    $('#rangeSelect').on('change', function () { loadCharts($(this).val()); });

    // Initial load
    loadCharts(30);
    loadWidgets();
    loadReport();
    setInterval(loadStats, 30000); // live-refresh metrics every 30s
});
</script>
@endpush
