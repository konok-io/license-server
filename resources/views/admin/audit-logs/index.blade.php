@extends('layouts.admin')
@section('title', 'Audit Trail')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-journal-text me-2"></i>Immutable Audit Trail</span>
        <button class="btn btn-outline-primary btn-sm" id="btnVerifyChain">
            <i class="bi bi-shield-check"></i> Verify Chain Integrity
        </button>
    </div>
    <div class="card-body">
        <div id="chainResult"></div>
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Search description, actor, IP…"></div>
            <div class="col-md-3">
                <select id="filterEvent" class="form-select form-select-sm">
                    <option value="">All events</option>
                    @foreach(App\Enums\AuditEvent::cases() as $ev)
                        <option value="{{ $ev->value }}">{{ $ev->value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterActorType" class="form-select form-select-sm">
                    <option value="">All actors</option>
                    <option value="admin">Admin</option>
                    <option value="system">System</option>
                    <option value="api_client">API Client</option>
                </select>
            </div>
        </div>
        <table id="tbl" class="table table-hover w-100">
            <thead><tr>
                <th>#</th><th>Event</th><th>Description</th><th>Actor</th>
                <th>IP</th><th>Chain</th><th>Time</th><th class="text-end"></th>
            </tr></thead>
        </table>
    </div>
</div>

{{-- Detail modal --}}
<div class="modal fade" id="auditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Audit Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="auditBody"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#tbl').DataTable({
        processing:true, serverSide:true,
        ajax:{ url:'{{ route('admin.audit-logs.data') }}', data:d=>{ d.search=$('#filterSearch').val(); d.event=$('#filterEvent').val(); d.actor_type=$('#filterActorType').val(); } },
        columns:[
            { data:'id' },
            { data:'event', render:v=>`<span class="badge-soft badge">${v}</span>` },
            { data:'description', defaultContent:'—' },
            { data:'actor', defaultContent:'—' },
            { data:'ip_address', defaultContent:'—' },
            { data:'chain_valid', className:'text-center', render:v=>v
                ? '<i class="bi bi-shield-check text-success" title="Intact"></i>'
                : '<i class="bi bi-shield-exclamation text-danger" title="Tampered"></i>' },
            { data:'created_at' },
            { data:'id', orderable:false, className:'text-end', render:id=>`<button class="btn btn-sm btn-outline-secondary btn-view" data-id="${id}"><i class="bi bi-eye"></i></button>` }
        ],
        order:[[0,'desc']], pageLength:20
    });
    $('#filterSearch, #filterEvent, #filterActorType').on('keyup change', ()=>table.ajax.reload());

    // Chain verification
    $('#btnVerifyChain').on('click', function () {
        const btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Verifying…');
        $.get('{{ route('admin.audit-logs.verify-chain') }}', function (res) {
            const cls = res.intact ? 'alert-success' : 'alert-danger';
            const icon = res.intact ? 'bi-shield-check' : 'bi-shield-exclamation';
            let html = `<div class="alert ${cls}"><i class="bi ${icon} me-1"></i>${res.message}`;
            if (!res.intact) html += `<br><small>Broken at record IDs: ${res.broken_links.join(', ')}</small>`;
            html += '</div>';
            $('#chainResult').html(html);
        }).fail(slsHandleError).always(()=>btn.prop('disabled', false).html('<i class="bi bi-shield-check"></i> Verify Chain Integrity'));
    });

    // Detail
    const modal = new bootstrap.Modal('#auditModal');
    $('#tbl').on('click', '.btn-view', function () {
        $.get(`{{ url('admin/audit-logs') }}/${$(this).data('id')}`, function (res) {
            const a = res.audit;
            const kv = (label, val) => `<div class="col-md-6 mb-2"><div class="text-muted small">${label}</div><div class="mono small">${val ?? '—'}</div></div>`;
            const json = obj => obj ? `<pre class="bg-light p-2 rounded small mb-0">${JSON.stringify(obj, null, 2)}</pre>` : '—';
            $('#auditBody').html(`
                <div class="row">
                    ${kv('Event', a.event)}${kv('Actor', a.actor + ' (' + a.actor_type + ')')}
                    ${kv('IP Address', a.ip_address)}${kv('Timestamp', a.created_at)}
                    ${kv('Chain Status', a.chain_valid ? '<span class="text-success">Intact</span>' : '<span class="text-danger">Tampered</span>')}
                    ${kv('UUID', a.uuid)}
                </div>
                <hr>
                <div class="mb-2"><strong>Description:</strong> ${a.description ?? '—'}</div>
                <div class="row">
                    <div class="col-md-6"><div class="text-muted small mb-1">Old values</div>${json(a.old_values)}</div>
                    <div class="col-md-6"><div class="text-muted small mb-1">New values</div>${json(a.new_values)}</div>
                </div>
                <hr>
                <div class="text-muted small">Previous hash</div><div class="mono small text-break">${a.previous_hash ?? '(genesis)'}</div>
                <div class="text-muted small mt-2">Record hash</div><div class="mono small text-break">${a.hash}</div>
            `);
            modal.show();
        }).fail(slsHandleError);
    });
});
</script>
@endpush
