<aside class="sls-sidebar">
    <div class="sls-brand">
        <span class="dot"></span>
        <span>Saudi License Server</span>
    </div>

    <nav class="sls-nav">
        <div class="nav-label">Overview</div>
        <a href="<?php echo e(route('admin.analytics.index')); ?>" class="<?php echo e(request()->routeIs('admin.analytics.*') ? 'active' : ''); ?>">
            <i class="bi bi-speedometer2"></i> Analytics
        </a>

        <div class="nav-label">Management</div>
        <a href="<?php echo e(route('admin.customers.index')); ?>" class="<?php echo e(request()->routeIs('admin.customers.*') ? 'active' : ''); ?>">
            <i class="bi bi-people"></i> Customers
        </a>
        <a href="<?php echo e(route('admin.licenses.index')); ?>" class="<?php echo e(request()->routeIs('admin.licenses.*') ? 'active' : ''); ?>">
            <i class="bi bi-key"></i> Licenses
        </a>
        <a href="<?php echo e(route('admin.blacklists.index')); ?>" class="<?php echo e(request()->routeIs('admin.blacklists.*') ? 'active' : ''); ?>">
            <i class="bi bi-slash-circle"></i> Blacklist
        </a>

        <div class="nav-label">Monitoring</div>
        <a href="<?php echo e(route('admin.activation-logs.index')); ?>" class="<?php echo e(request()->routeIs('admin.activation-logs.*') ? 'active' : ''); ?>">
            <i class="bi bi-box-arrow-in-right"></i> Activation Logs
        </a>
        <a href="<?php echo e(route('admin.verification-logs.index')); ?>" class="<?php echo e(request()->routeIs('admin.verification-logs.*') ? 'active' : ''); ?>">
            <i class="bi bi-patch-check"></i> Verification Logs
        </a>
        <a href="<?php echo e(route('admin.resets.index')); ?>" class="<?php echo e(request()->routeIs('admin.resets.*') ? 'active' : ''); ?>">
            <i class="bi bi-arrow-counterclockwise"></i> License Resets
        </a>

        <div class="nav-label">Security</div>
        <a href="<?php echo e(route('admin.audit-logs.index')); ?>" class="<?php echo e(request()->routeIs('admin.audit-logs.*') ? 'active' : ''); ?>">
            <i class="bi bi-journal-text"></i> Audit Trail
        </a>

        <div class="nav-label">Website</div>
        <a href="<?php echo e(route('admin.settings.edit')); ?>" class="<?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>">
            <i class="bi bi-sliders"></i> Site Settings
        </a>
        <a href="<?php echo e(url('/')); ?>" target="_blank">
            <i class="bi bi-box-arrow-up-right"></i> View Homepage
        </a>
    </nav>
</aside>
<?php /**PATH C:\laragon\www\license-server\resources\views/admin/partials/sidebar.blade.php ENDPATH**/ ?>