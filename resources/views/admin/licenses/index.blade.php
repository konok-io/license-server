@extends('layouts.admin')
@section('title', 'Licenses')

@section('content')
{{-- Stat cards --}}
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['label' => 'Active',    'key' => 'active',    'icon' => 'bi-check-circle',  'color' => '#2e8b6f'],
            ['label' => 'Suspended', 'key' => 'suspended', 'icon' => 'bi-pause-circle',  'color' => '#c98a1b'],
            ['label' => 'Killed',    'key' => 'killed',    'icon' => 'bi-x-octagon',     'color' => '#c0392b'],
            ['label' => 'Expired',   'key' => 'expired',   'icon' => 'bi-clock-history', 'color' => '#6b7a8d'],
        ];
    @endphp
    @foreach($cards as $c)
    <div class="col-md-3">
        <div class="card stat-card h-100" style="border-left-color: {{ $c['color'] }}">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="stat-value">{{ $counts[$c['key']] ?? 0 }}</div>
                    <div class="stat-label">{{ $c['label'] }}</div>
                </div>
                <i class="bi {{ $c['icon'] }}" style="font-size: 2rem; color: {{ $c['color'] }}; opacity:.6"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-key me-2"></i>Issued Licenses</span>
        @can('create', App\Models\License::class)
        <button class="btn btn-accent btn-sm" id="btnNewLicense"><i class="bi bi-plus-lg"></i> Issue License</button>
        @endcan
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4"><input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Search key, UUID, customer…"></div>
            <div class="col-md-3">
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach(App\Enums\LicenseStatus::cases() as $s)
                        <option value="{{ $s->value }}">{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterType" class="form-select form-select-sm">
                    <option value="">All types</option>
                    @foreach(App\Enums\LicenseType::cases() as $t)
                        <option value="{{ $t->value }}">{{ $t->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <table id="licensesTable" class="table table-hover w-100">
            <thead>
                <tr>
                    <th>Key</th><th>Customer</th><th>Type</th><th>Status</th>
                    <th>Activations</th><th>Expires</th><th>Last Verified</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@include('admin.licenses.form-modal')
@include('admin.licenses.action-modals')
@endsection

@push('scripts')
<script>
$(function () {
    const statusBadge = s => {
        const map = { active:'success', suspended:'warning', killed:'danger', expired:'secondary', pending:'info', reset:'info' };
        return `<span class="badge text-bg-${map[s] || 'secondary'}">${s}</span>`;
    };

    const table = $('#licensesTable').DataTable({
        processing: true, serverSide: true,
        ajax: {
            url: '{{ route('admin.licenses.data') }}',
            data: d => { d.search=$('#filterSearch').val(); d.status=$('#filterStatus').val(); d.type=$('#filterType').val(); }
        },
        columns: [
            { data: 'key_prefix', render: v => `<span class="mono">${v}…</span>` },
            { data: 'customer', defaultContent: '—' },
            { data: 'type_label' },
            { data: 'status', render: statusBadge },
            { data: 'activations', className: 'text-center' },
            { data: 'expires_at', defaultContent: '—' },
            { data: 'last_verified_at', defaultContent: 'Never' },
            { data: 'id', orderable:false, className:'text-end table-actions', render: (id,t,row) => {
                let btns = `<button class="btn btn-outline-secondary btn-edit" data-id="${id}" title="Edit"><i class="bi bi-pencil"></i></button>`;
                @can('reset', App\Models\License::class)
                btns += ` <button class="btn btn-outline-primary btn-reset" data-id="${id}" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></button>`;
                @endcan
                @can('kill', App\Models\License::class)
                if (row.status !== 'killed')
                    btns += ` <button class="btn btn-outline-danger btn-kill" data-id="${id}" title="Kill"><i class="bi bi-x-octagon"></i></button>`;
                else
                    btns += ` <button class="btn btn-outline-success btn-reactivate" data-id="${id}" title="Reactivate"><i class="bi bi-arrow-clockwise"></i></button>`;
                @endcan
                return btns;
            } }
        ],
        order: [[0,'desc']], pageLength: 15
    });

    $('#filterSearch, #filterStatus, #filterType').on('keyup change', () => table.ajax.reload());

    const formModal   = new bootstrap.Modal('#licenseModal');
    const keyModal    = new bootstrap.Modal('#keyRevealModal');
    const killModal   = new bootstrap.Modal('#killModal');
    const resetModal  = new bootstrap.Modal('#resetModal');

    // Issue
    $('#btnNewLicense').on('click', function () {
        $('#licenseForm')[0].reset();
        $('#licenseId').val('');
        $('#licenseModalLabel').text('Issue License');
        $('#customerRow').show();
        $('#statusRow').hide();          // status not set at issue time
        $('.invalid-feedback').text('');
        formModal.show();
    });

    // Edit
    $('#licensesTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.get(`{{ url('admin/licenses') }}/${id}`, function (res) {
            const l = res.license;
            $('#licenseId').val(l.id);
            $('#type').val(l.type);
            $('#status').val(l.status);
            $('#max_activations').val(l.max_activations);
            $('#grace_days').val(l.grace_days);
            $('#verification_interval_hours').val(l.verification_interval_hours);
            $('#version').val(l.version);
            $('#licenseModalLabel').text('Edit License');
            $('#customerRow').hide();       // customer immutable after issue
            $('#statusRow').show();         // status editable only on edit
            $('.invalid-feedback').text('');
            formModal.show();
        }).fail(slsHandleError);
    });

    // Save
    $('#licenseForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#licenseId').val();
        const url = id ? `{{ url('admin/licenses') }}/${id}` : '{{ route('admin.licenses.store') }}';
        const method = id ? 'PUT' : 'POST';
        $('.invalid-feedback').text('');
        $.ajax({
            url, method, data: $(this).serialize(),
            success: function (res) {
                formModal.hide();
                table.ajax.reload(null, false);
                if (res.plain_key) {
                    $('#revealedKey').val(res.plain_key);
                    keyModal.show();
                } else {
                    slsToast(res.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    Object.entries(xhr.responseJSON.errors).forEach(([f, m]) => $(`#err_${f}`).text(m[0]));
                } else { slsHandleError(xhr); }
            }
        });
    });

    $('#btnCopyKey').on('click', function () {
        navigator.clipboard.writeText($('#revealedKey').val());
        slsToast('License key copied to clipboard.');
    });

    // Kill flow
    let killId = null;
    $('#licensesTable').on('click', '.btn-kill', function () { killId = $(this).data('id'); $('#killReason').val(''); $('#err_reason').text(''); killModal.show(); });
    $('#killForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: `{{ url('admin/licenses') }}/${killId}/kill`, method: 'POST',
            data: { reason: $('#killReason').val() },
            success: res => { killModal.hide(); slsToast(res.message); table.ajax.reload(null,false); },
            error: xhr => xhr.status===422 ? $('#err_reason').text(xhr.responseJSON.errors.reason[0]) : slsHandleError(xhr)
        });
    });

    // Reactivate
    $('#licensesTable').on('click', '.btn-reactivate', function () {
        const id = $(this).data('id');
        if (!confirm('Reactivate this license and clear the kill switch?')) return;
        $.post(`{{ url('admin/licenses') }}/${id}/reactivate`, {})
            .done(res => { slsToast(res.message); table.ajax.reload(null,false); })
            .fail(slsHandleError);
    });

    // Reset flow
    let resetId = null;
    $('#licensesTable').on('click', '.btn-reset', function () { resetId = $(this).data('id'); $('#resetReason').val(''); $('#err_reset_reason').text(''); resetModal.show(); });
    $('#resetForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: `{{ url('admin/licenses') }}/${resetId}/reset`, method: 'POST',
            data: { reason: $('#resetReason').val() },
            success: res => { resetModal.hide(); slsToast(res.message); table.ajax.reload(null,false); },
            error: xhr => xhr.status===422 ? $('#err_reset_reason').text(xhr.responseJSON.errors.reason[0]) : slsHandleError(xhr)
        });
    });
});
</script>
@endpush
