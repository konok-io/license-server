@extends('layouts.admin')
@section('title', 'Blacklist')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-slash-circle me-2"></i>Blacklist Entries</span>
        @can('create', App\Models\LicenseBlacklist::class)
        <button class="btn btn-accent btn-sm" id="btnNew"><i class="bi bi-plus-lg"></i> Add Entry</button>
        @endcan
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Search reason, install ID, domain, IP…"></div>
            <div class="col-md-3">
                <select id="filterActive" class="form-select form-select-sm">
                    <option value="">All entries</option>
                    <option value="1">Active</option>
                    <option value="0">Lifted</option>
                </select>
            </div>
        </div>
        <table id="tbl" class="table table-hover w-100">
            <thead><tr>
                <th>License</th><th>Installation</th><th>Domain</th><th>IP</th>
                <th>Reason</th><th>Status</th><th>Added By</th><th>When</th>
                <th class="text-end">Actions</th>
            </tr></thead>
        </table>
    </div>
</div>

{{-- Add entry modal --}}
<div class="modal fade" id="blModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="blForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Blacklist Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Provide at least one target. Blacklisting takes effect on the next verification.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">License</label>
                            <select class="form-select" name="license_id" id="license_id">
                                <option value="">None</option>
                                @foreach($licenses as $l)
                                    <option value="{{ $l->id }}">{{ $l->license_key_prefix }}…</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-block" id="err_license_id"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Installation ID</label>
                            <input type="text" class="form-control" name="installation_id" id="bl_install">
                            <div class="invalid-feedback d-block" id="err_installation_id"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Domain</label>
                            <input type="text" class="form-control" name="domain" id="bl_domain">
                            <div class="invalid-feedback d-block" id="err_domain"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IP Address</label>
                            <input type="text" class="form-control" name="ip_address" id="bl_ip">
                            <div class="invalid-feedback d-block" id="err_ip_address"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="reason" id="bl_reason" rows="2" required></textarea>
                            <div class="invalid-feedback d-block" id="err_reason"></div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="kill_license" id="kill_license" value="1" checked>
                                <label class="form-check-label" for="kill_license">Also kill the linked license immediately</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Add to blacklist</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#tbl').DataTable({
        processing:true, serverSide:true,
        ajax:{ url:'{{ route('admin.blacklists.data') }}', data:d=>{ d.search=$('#filterSearch').val(); d.is_active=$('#filterActive').val(); } },
        columns:[
            { data:'license', defaultContent:'—', render:v=>v?`<span class="mono">${v}…</span>`:'—' },
            { data:'installation_id', defaultContent:'—', render:v=>v?`<span class="mono small">${v}</span>`:'—' },
            { data:'domain', defaultContent:'—' },
            { data:'ip_address', defaultContent:'—' },
            { data:'reason' },
            { data:'is_active', render:v=>v?'<span class="badge text-bg-danger">Active</span>':'<span class="badge text-bg-secondary">Lifted</span>' },
            { data:'created_by', defaultContent:'—' },
            { data:'blacklisted_at' },
            { data:'id', orderable:false, className:'text-end table-actions', render:(id,t,row)=>{
                let b = '';
                @can('update', App\Models\LicenseBlacklist::class)
                if (row.is_active) b += `<button class="btn btn-outline-success btn-lift" data-id="${id}" title="Lift"><i class="bi bi-unlock"></i></button> `;
                @endcan
                @can('delete', App\Models\LicenseBlacklist::class)
                b += `<button class="btn btn-outline-danger btn-del" data-id="${id}" title="Remove"><i class="bi bi-trash"></i></button>`;
                @endcan
                return b || '—';
            } }
        ],
        order:[[7,'desc']], pageLength:15
    });
    $('#filterSearch, #filterActive').on('keyup change', ()=>table.ajax.reload());

    const modal = new bootstrap.Modal('#blModal');
    $('#btnNew').on('click', ()=>{ $('#blForm')[0].reset(); $('.invalid-feedback').text(''); modal.show(); });

    $('#blForm').on('submit', function (e) {
        e.preventDefault();
        $('.invalid-feedback').text('');
        $.ajax({
            url:'{{ route('admin.blacklists.store') }}', method:'POST', data:$(this).serialize(),
            success: res => { modal.hide(); slsToast(res.message); table.ajax.reload(null,false); },
            error: xhr => xhr.status===422
                ? Object.entries(xhr.responseJSON.errors).forEach(([f,m])=>$(`#err_${f}`).text(m[0]))
                : slsHandleError(xhr)
        });
    });

    $('#tbl').on('click', '.btn-lift', function () {
        const id = $(this).data('id');
        if (!confirm('Lift this blacklist entry?')) return;
        $.post(`{{ url('admin/blacklists') }}/${id}/lift`, {})
            .done(res=>{ slsToast(res.message); table.ajax.reload(null,false); }).fail(slsHandleError);
    });

    $('#tbl').on('click', '.btn-del', function () {
        const id = $(this).data('id');
        if (!confirm('Permanently remove this entry?')) return;
        $.ajax({ url:`{{ url('admin/blacklists') }}/${id}`, method:'DELETE',
            success: res=>{ slsToast(res.message); table.ajax.reload(null,false); }, error: slsHandleError });
    });
});
</script>
@endpush
