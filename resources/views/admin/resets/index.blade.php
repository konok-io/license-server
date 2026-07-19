@extends('layouts.admin')
@section('title', 'License Resets')

@section('content')
<div class="card">
    <div class="card-header"><i class="bi bi-arrow-counterclockwise me-2"></i>Reset History</div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Search reason, operator, IP…"></div>
        </div>
        <table id="tbl" class="table table-hover w-100">
            <thead><tr>
                <th>License</th><th>Reason</th><th>Cleared</th><th>Key Rotation</th>
                <th>Performed By</th><th>IP</th><th>Reset At</th>
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
        ajax:{ url:'{{ route('admin.resets.data') }}', data:d=>{ d.search=$('#filterSearch').val(); } },
        columns:[
            { data:'license', defaultContent:'—', render:v=>v?`<span class="mono">${v}…</span>`:'—' },
            { data:'reason', defaultContent:'—' },
            { data:'activations_cleared', className:'text-center' },
            { data:'key_rotation', render:v=>`<span class="mono small">${v}</span>` },
            { data:'performed_by', defaultContent:'—' },
            { data:'ip_address', defaultContent:'—' },
            { data:'reset_at' }
        ],
        order:[[6,'desc']], pageLength:20
    });
    $('#filterSearch').on('keyup', ()=>table.ajax.reload());
});
</script>
@endpush
