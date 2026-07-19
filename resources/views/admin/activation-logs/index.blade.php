@extends('layouts.admin')
@section('title', 'Activation Logs')

@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-box-arrow-in-right me-2"></i>Activation History</div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Search install ID, domain, IP…"></div>
            <div class="col-md-3">
                <select id="filterAction" class="form-select form-select-sm">
                    <option value="">All actions</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="reactivate">Reactivate</option>
                    <option value="denied">Denied</option>
                </select>
            </div>
        </div>
        <table id="tbl" class="table table-hover w-100">
            <thead><tr>
                <th>License</th><th>Action</th><th>Result</th><th>Installation</th>
                <th>Domain</th><th>Server</th><th>IP</th><th>Time</th>
            </tr></thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#tbl').DataTable({
        processing:true, serverSide:true,
        ajax:{ url:'{{ route('admin.activation-logs.data') }}', data:d=>{ d.search=$('#filterSearch').val(); d.action=$('#filterAction').val(); } },
        columns:[
            { data:'license', defaultContent:'—', render:v=>v?`<span class="mono">${v}…</span>`:'—' },
            { data:'action', render:v=>`<span class="badge-soft badge">${v}</span>` },
            { data:'success', render:v=>v?'<span class="badge text-bg-success">OK</span>':'<span class="badge text-bg-danger">Denied</span>' },
            { data:'installation_id', defaultContent:'—', render:v=>v?`<span class="mono small">${v}</span>`:'—' },
            { data:'domain', defaultContent:'—' },
            { data:'server_type', defaultContent:'—' },
            { data:'ip_address', defaultContent:'—' },
            { data:'created_at' }
        ],
        order:[[7,'desc']], pageLength:20
    });
    $('#filterSearch, #filterAction').on('keyup change', ()=>table.ajax.reload());
});
</script>
@endpush
