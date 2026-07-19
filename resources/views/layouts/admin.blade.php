<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Saudi License Server</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --sls-ink: #0f1b2d;
            --sls-panel: #16263d;
            --sls-accent: #2e8b6f;      /* Saudi green, muted */
            --sls-accent-soft: #e6f2ee;
            --sls-line: #e3e8ee;
            --sls-muted: #6b7a8d;
        }
        body { background: #f5f7fa; color: var(--sls-ink); font-family: 'Segoe UI', system-ui, sans-serif; }

        /* Sidebar */
        .sls-sidebar {
            position: fixed; top: 0; bottom: 0; left: 0; width: 248px;
            background: var(--sls-ink); color: #cdd6e2; padding: 0; overflow-y: auto;
        }
        .sls-brand {
            display: flex; align-items: center; gap: .6rem;
            padding: 1.15rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,.07);
            color: #fff; font-weight: 700; letter-spacing: .3px;
        }
        .sls-brand .dot { width: 10px; height: 10px; border-radius: 50%; background: var(--sls-accent); box-shadow: 0 0 0 4px rgba(46,139,111,.25); }
        .sls-nav { padding: .75rem .6rem; }
        .sls-nav .nav-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .12em; color: #5f708a; padding: .9rem .8rem .35rem; }
        .sls-nav a {
            display: flex; align-items: center; gap: .7rem;
            padding: .58rem .8rem; margin: .1rem 0; border-radius: 8px;
            color: #c3cede; text-decoration: none; font-size: .92rem; transition: all .15s;
        }
        .sls-nav a i { font-size: 1.05rem; width: 20px; text-align: center; }
        .sls-nav a:hover { background: rgba(255,255,255,.05); color: #fff; }
        .sls-nav a.active { background: var(--sls-accent); color: #fff; font-weight: 600; }

        /* Main */
        .sls-main { margin-left: 248px; min-height: 100vh; }
        .sls-topbar {
            background: #fff; border-bottom: 1px solid var(--sls-line);
            padding: .85rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
        }
        .sls-topbar h1 { font-size: 1.15rem; font-weight: 700; margin: 0; }
        .sls-content { padding: 1.5rem; }

        .card { border: 1px solid var(--sls-line); border-radius: 12px; box-shadow: 0 1px 2px rgba(15,27,45,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--sls-line); font-weight: 600; border-radius: 12px 12px 0 0 !important; }

        .stat-card { border-left: 3px solid var(--sls-accent); }
        .stat-card .stat-value { font-size: 1.6rem; font-weight: 700; }
        .stat-card .stat-label { color: var(--sls-muted); font-size: .82rem; text-transform: uppercase; letter-spacing: .05em; }

        table.dataTable thead th { font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: var(--sls-muted); border-bottom: 2px solid var(--sls-line); }
        table.dataTable tbody td { vertical-align: middle; font-size: .9rem; }

        .badge-soft { background: var(--sls-accent-soft); color: var(--sls-accent); font-weight: 600; }
        .btn-accent { background: var(--sls-accent); border-color: var(--sls-accent); color: #fff; }
        .btn-accent:hover { background: #26765d; border-color: #26765d; color: #fff; }
        .table-actions .btn { padding: .2rem .45rem; font-size: .8rem; }
        .mono { font-family: 'SFMono-Regular', Consolas, monospace; font-size: .85rem; }
    </style>
    @stack('styles')
</head>
<body>
    @include('admin.partials.sidebar')

    <div class="sls-main">
        <div class="sls-topbar">
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small"><i class="bi bi-shield-lock"></i> {{ auth()->user()?->name ?? 'Guest' }}</span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
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
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="slsToast" class="toast align-items-center border-0" role="alert">
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
            el.className = 'toast align-items-center border-0 text-bg-' + (type === 'success' ? 'success' : 'danger');
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
