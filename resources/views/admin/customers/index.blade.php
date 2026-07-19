@extends('layouts.admin')
@section('title', 'Customers')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-people me-2"></i>Customer Directory</span>
        @can('create', App\Models\Customer::class)
        <button class="btn btn-accent btn-sm" id="btnNewCustomer">
            <i class="bi bi-plus-lg"></i> New Customer
        </button>
        @endcan
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Search name, company, email…">
            </div>
            <div class="col-md-3">
                <select id="filterActive" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>

        <table id="customersTable" class="table table-hover w-100">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>Licenses</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@include('admin.customers.form-modal')
@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#customersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.customers.data') }}',
            data: function (d) {
                d.search    = $('#filterSearch').val();
                d.is_active = $('#filterActive').val();
            }
        },
        columns: [
            { data: 'name' },
            { data: 'company', defaultContent: '—' },
            { data: 'email' },
            { data: 'country' },
            { data: 'licenses_count', className: 'text-center' },
            { data: 'is_active', render: v => v
                ? '<span class="badge text-bg-success">Active</span>'
                : '<span class="badge text-bg-secondary">Inactive</span>' },
            { data: 'id', orderable: false, className: 'text-end table-actions', render: (id, t, row) => `
                <button class="btn btn-outline-secondary btn-edit" data-id="${id}"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-outline-danger btn-delete" data-id="${id}" data-name="${row.name}"><i class="bi bi-trash"></i></button>
            ` }
        ],
        order: [[0, 'asc']],
        pageLength: 15,
        language: { search: '', searchPlaceholder: 'Quick filter' }
    });

    // Live filters
    $('#filterSearch, #filterActive').on('keyup change', () => table.ajax.reload());

    const modal = new bootstrap.Modal('#customerModal');

    // New
    $('#btnNewCustomer').on('click', function () {
        $('#customerForm')[0].reset();
        $('#customerId').val('');
        $('#customerModalLabel').text('New Customer');
        $('.invalid-feedback').text('');
        modal.show();
    });

    // Edit — fetch record
    $('#customersTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.get(`{{ url('admin/customers') }}/${id}`, function (res) {
            const c = res.customer;
            $('#customerId').val(c.id);
            $('#name').val(c.name);
            $('#company').val(c.company);
            $('#email').val(c.email);
            $('#phone').val(c.phone);
            $('#country').val(c.country);
            $('#notes').val(c.notes);
            $('#is_active').prop('checked', c.is_active);
            $('#customerModalLabel').text('Edit Customer');
            $('.invalid-feedback').text('');
            modal.show();
        }).fail(slsHandleError);
    });

    // Save (create or update)
    $('#customerForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#customerId').val();
        const url = id ? `{{ url('admin/customers') }}/${id}` : '{{ route('admin.customers.store') }}';
        const method = id ? 'PUT' : 'POST';
        $('.invalid-feedback').text('');

        $.ajax({
            url, method,
            data: $(this).serialize(),
            success: function (res) {
                modal.hide();
                slsToast(res.message);
                table.ajax.reload(null, false);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    Object.entries(xhr.responseJSON.errors).forEach(([f, msgs]) => {
                        $(`#err_${f}`).text(msgs[0]);
                    });
                } else { slsHandleError(xhr); }
            }
        });
    });

    // Delete
    $('#customersTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        if (!confirm(`Delete customer "${$(this).data('name')}"? This cannot be undone.`)) return;
        $.ajax({
            url: `{{ url('admin/customers') }}/${id}`,
            method: 'DELETE',
            success: res => { slsToast(res.message); table.ajax.reload(null, false); },
            error: slsHandleError
        });
    });
});
</script>
@endpush
