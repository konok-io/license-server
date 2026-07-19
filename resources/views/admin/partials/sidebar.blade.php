<aside class="sls-sidebar">
    <div class="sls-brand">
        <span class="dot"></span>
        <span>Saudi License Server</span>
    </div>

    <nav class="sls-nav">
        <div class="nav-label">Overview</div>
        <a href="{{ route('admin.analytics.index') }}" class="{{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Analytics
        </a>

        <div class="nav-label">Management</div>
        <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Customers
        </a>
        <a href="{{ route('admin.licenses.index') }}" class="{{ request()->routeIs('admin.licenses.*') ? 'active' : '' }}">
            <i class="bi bi-key"></i> Licenses
        </a>
        <a href="{{ route('admin.blacklists.index') }}" class="{{ request()->routeIs('admin.blacklists.*') ? 'active' : '' }}">
            <i class="bi bi-slash-circle"></i> Blacklist
        </a>

        <div class="nav-label">Monitoring</div>
        <a href="{{ route('admin.activation-logs.index') }}" class="{{ request()->routeIs('admin.activation-logs.*') ? 'active' : '' }}">
            <i class="bi bi-box-arrow-in-right"></i> Activation Logs
        </a>
        <a href="{{ route('admin.verification-logs.index') }}" class="{{ request()->routeIs('admin.verification-logs.*') ? 'active' : '' }}">
            <i class="bi bi-patch-check"></i> Verification Logs
        </a>
        <a href="{{ route('admin.resets.index') }}" class="{{ request()->routeIs('admin.resets.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-counterclockwise"></i> License Resets
        </a>

        <div class="nav-label">Security</div>
        <a href="{{ route('admin.audit-logs.index') }}" class="{{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> Audit Trail
        </a>

        <div class="nav-label">Website</div>
        <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-sliders"></i> Site Settings
        </a>
        <a href="{{ url('/') }}" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> View Homepage
        </a>
    </nav>
</aside>
