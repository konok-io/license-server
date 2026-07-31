<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>

    <!-- Shield Design System CSS -->
    <link href="{{ asset('css/shield-design.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        /* Admin Override Styles - Keep Bootstrap classes with shield theme */
        body { background: var(--bg-primary); color: var(--text-primary); }
        
        /* Sidebar */
        .sls-sidebar {
            position: fixed; top: 0; bottom: 0; left: 0; width: 260px;
            background: var(--bg-card); color: var(--text-secondary); padding: 0; overflow-y: auto;
            border-right: 1px solid var(--border-color);
        }
        .sls-brand {
            display: flex; align-items: center; gap: .6rem;
            padding: 1.15rem 1.25rem; border-bottom: 1px solid var(--border-color);
            color: var(--accent-gold); font-weight: 700; letter-spacing: 1px;
            font-family: var(--font-mono);
        }
        .sls-brand i { font-size: 1.5rem; }
        .sls-nav { padding: .75rem .6rem; }
        .sls-nav .nav-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .12em; color: var(--text-muted); padding: .9rem .8rem .35rem; }
        .sls-nav a {
            display: flex; align-items: center; gap: .7rem;
            padding: .58rem .8rem; margin: .1rem 0; border-radius: var(--radius-sm);
            color: var(--text-secondary); text-decoration: none; font-size: .92rem; transition: all .15s;
        }
        .sls-nav a i { font-size: 1.05rem; width: 20px; text-align: center; }
        .sls-nav a:hover { background: var(--accent-gold-subtle); color: var(--accent-gold); }
        .sls-nav a.active { background: var(--accent-gold); color: var(--bg-primary); font-weight: 600; }

        /* Main Content */
        .sls-main { margin-left: 260px; min-height: 100vh; }
        .sls-topbar {
            background: var(--bg-card); border-bottom: 1px solid var(--border-color);
            padding: .85rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
        }
        .sls-topbar h1 { font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--text-primary); }
        .sls-content { padding: 1.5rem; }

        /* Cards */
        .card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); }
        .card-header { background: transparent; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-primary); }
        .card-body { color: var(--text-primary); }

        /* Stat Cards */
        .stat-card { border-left: 3px solid var(--accent-gold); background: var(--bg-card); }
        .stat-card .stat-value { font-size: 1.6rem; font-weight: 700; color: var(--accent-gold); }
        .stat-card .stat-label { color: var(--text-secondary); font-size: .82rem; text-transform: uppercase; letter-spacing: .05em; }

        /* Tables */
        .table { color: var(--text-primary); }
        .table > thead { background: var(--bg-card); }
        .table th { font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: var(--text-secondary); border-bottom: 2px solid var(--border-color); }
        .table td { vertical-align: middle; font-size: .9rem; border-bottom: 1px solid var(--border-color-light); }
        .table tbody tr:hover { background: var(--bg-card-hover); }
        
        table.dataTable thead th { 
            font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: var(--text-secondary); 
            border-bottom: 2px solid var(--border-color); background: var(--bg-card);
        }
        table.dataTable tbody td { vertical-align: middle; font-size: .9rem; background: var(--bg-card); }
        table.dataTable tbody tr:hover > * { background: var(--bg-card-hover) !important; }
        .dataTables_wrapper .dataTables_length, 
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { color: var(--text-secondary); }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input { 
            background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);
            border-radius: var(--radius-sm);
        }
        .page-link { background: var(--bg-card); border-color: var(--border-color); color: var(--text-primary); }
        .page-link:hover { background: var(--accent-gold-subtle); border-color: var(--accent-gold); color: var(--accent-gold); }
        .page-item.active .page-link { background: var(--accent-gold); border-color: var(--accent-gold); color: var(--bg-primary); }

        /* Badges */
        .badge-soft { background: var(--accent-gold-subtle); color: var(--accent-gold); font-weight: 600; }
        .badge.bg-success { background: var(--accent-green-glow) !important; color: var(--accent-green); }
        .badge.bg-warning { background: var(--accent-gold-subtle) !important; color: var(--accent-gold); }
        .badge.bg-danger { background: var(--accent-red-glow) !important; color: var(--accent-red); }

        /* Buttons */
        .btn-accent { background: var(--accent-gold); border-color: var(--accent-gold); color: var(--bg-primary); }
        .btn-accent:hover { background: var(--accent-gold-dark); border-color: var(--accent-gold-dark); color: var(--bg-primary); }
        .btn-shield { 
            background: transparent; border: 1px solid var(--border-color); color: var(--text-primary);
            padding: .5rem 1rem; border-radius: var(--radius-sm); transition: all .15s;
        }
        .btn-shield:hover { background: var(--accent-gold-subtle); border-color: var(--accent-gold); color: var(--accent-gold); }
        
        /* Form Controls */
        .form-control { background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary); }
        .form-control:focus { background: var(--bg-input); border-color: var(--accent-gold); color: var(--text-primary); box-shadow: 0 0 0 4px rgba(245, 166, 35, 0.1); }
        .form-control::placeholder { color: var(--text-muted); }
        .form-select { background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary); }
        .form-select:focus { border-color: var(--accent-gold); box-shadow: 0 0 0 4px rgba(245, 166, 35, 0.1); }
        
        .table-actions .btn { padding: .2rem .45rem; font-size: .8rem; }
        .mono { font-family: var(--font-mono); font-size: .85rem; }
        
        /* Toast */
        .toast { background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); }
        .toast.text-bg-success { background: var(--accent-green-glow) !important; color: var(--accent-green) !important; }
        .toast.text-bg-danger { background: var(--accent-red-glow) !important; color: var(--accent-red) !important; }
        
        /* Modal */
        .modal-content { background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); }
        .modal-header { border-bottom: 1px solid var(--border-color); }
        .modal-footer { border-top: 1px solid var(--border-color); }
        .btn-close { filter: invert(1); }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Background Decoration -->
    <div class="bg-decoration">
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
    </div>

    @include('admin.partials.sidebar')

    <div class="sls-main">
        <div class="sls-topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">
                    <i class="bi bi-shield-check" style="color: var(--accent-gold);"></i> 
                    {{ auth()->user()?->name ?? 'Guest' }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="background: var(--accent-gold-subtle); border: 1px solid var(--border-color); color: var(--accent-gold);">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <div class="sls-content">
            @yield('content')
        </div>
    </div>

    <!-- Global toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
        <div id="slsToast" class="toast align-items-center" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="slsToastBody"></div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // ---- Global AJAX setup ----
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // ---- Toast helper ----
        window.slsToast = function (message, type = 'success') {
            const el = document.getElementById('slsToast');
            const body = document.getElementById('slsToastBody');
            el.className = 'toast align-items-center text-bg-' + (type === 'success' ? 'success' : 'danger');
            body.textContent = message;
            new bootstrap.Toast(el, { delay: 4000 }).show();
        };

        // ---- Central AJAX error handler ----
        window.slsHandleError = function (xhr) {
            let msg = 'An unexpected error occurred.';
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
            } else if (xhr.responseJSON?.message) {
                msg = xhr.responseJSON.message;
            } else if (xhr.status === 403) {
                msg = 'You are not authorized to perform this action.';
            }
            slsToast(msg, 'error');
        };
    </script>
    @stack('scripts')
</body>
</html>
