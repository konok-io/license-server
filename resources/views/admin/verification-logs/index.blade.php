@extends('layouts.admin')
@section('title', 'Verification Logs')

@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-patch-check me-2"></i>Verification History</div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Search install ID, domain, IP…"></div>
            <div class="col-md-3">
                <select id="filterResult" class="form-select form-select-sm">
                    <option value="">All results</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                    <option value="killed">Killed</option>
                    <option value="expired">Expired</option>
                    <option value="domain_mismatch">Domain mismatch</option>
                    <option value="install_mismatch">Install mismatch</option>
                    <option value="blacklisted">Blacklisted</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterKill" class="form-select form-select-sm">
                    <option value="">Any directive</option>
                    <option value="1">Kill issued</option>
                </select>
            </div>
        </div>
        <table id="tbl" class="table table-hover w-100">
            <thead><tr>
                <th>License</th><th>Result</th><th>Kill</th><th>Installation</th>
                <th>Domain</th><th>IP</th><th>Latency</th><th>Time</th>
            </tr></thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const resBadge = r => {
        const map = { success:'success', failed:'danger', killed:'danger', expired:'secondary', blacklisted:'dark' };
        return `<span class="badge text-bg-${map[r]||'warning'}">${r}</span>`;
    };
    const table = $('#tbl').DataTable({
        processing:true, serverSide:true,
        ajax:{ url:'{{ route('admin.verification-logs.data') }}', data:d=>{ d.search=$('#filterSearch').val(); d.result=$('#filterResult').val(); d.kill_directive=$('#filterKill').val(); } },
        columns:[
            { data:'license', defaultContent:'—', render:v=>v?`<span class="mono">${v}…</span>`:'—' },
            { data:'result', render:resBadge },
            { data:'kill_directive', render:v=>v?'<i class="bi bi-x-octagon text-danger"></i>':'—', className:'text-center' },
            { data:'installation_id', defaultContent:'—', render:v=>v?`<span class="mono small">${v}</span>`:'—' },
            { data:'domain', defaultContent:'—' },
            { data:'ip_address', defaultContent:'—' },
            { data:'latency_ms', defaultContent:'—', render:v=>v!=null?`${v} ms`:'—' },
            { data:'created_at' }
        ],
        order:[[7,'desc']], pageLength:20
    });
    $('#filterSearch, #filterResult, #filterKill').on('keyup change', ()=>table.ajax.reload());
});
</script>
@endpush
