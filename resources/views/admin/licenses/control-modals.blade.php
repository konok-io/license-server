{{--
  Phase 6 control modals — include in licenses/index.blade.php:
      @include('admin.licenses.control-modals')
  and add trigger buttons in the row action renderer, e.g.:
      <button class="btn btn-outline-warning btn-disable-domain" data-id="${id}">…</button>
      <button class="btn btn-outline-warning btn-disable-install" data-id="${id}">…</button>
--}}

{{-- Disable Domain --}}
<div class="modal fade" id="disableDomainModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="disableDomainForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-globe2 me-2"></i>Disable Domain</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Revokes every active installation bound to this domain and blacklists it so it cannot re-activate.</p>
                    <label class="form-label">Domain <span class="text-danger">*</span></label>
                    <input type="text" class="form-control mb-2" id="dd_domain" placeholder="erp.client.sa" required>
                    <div class="invalid-feedback d-block" id="err_dd_domain"></div>
                    <label class="form-label mt-2">Reason</label>
                    <textarea class="form-control" id="dd_reason" rows="2"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Disable domain</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Disable Installation --}}
<div class="modal fade" id="disableInstallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="disableInstallForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pc-display me-2"></i>Disable Installation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Revokes this installation's binding and blacklists its ID for this license.</p>
                    <label class="form-label">Installation ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control mb-2 mono" id="di_install" required>
                    <div class="invalid-feedback d-block" id="err_di_install"></div>
                    <label class="form-label mt-2">Reason</label>
                    <textarea class="form-control" id="di_reason" rows="2"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Disable installation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function () {
    const ddModal = new bootstrap.Modal('#disableDomainModal');
    const diModal = new bootstrap.Modal('#disableInstallModal');
    let ddId = null, diId = null;

    // Open handlers (wire these data-ids from the license table row buttons).
    $(document).on('click', '.btn-disable-domain', function () {
        ddId = $(this).data('id'); $('#disableDomainForm')[0].reset(); $('.invalid-feedback').text(''); ddModal.show();
    });
    $(document).on('click', '.btn-disable-install', function () {
        diId = $(this).data('id'); $('#disableInstallForm')[0].reset(); $('.invalid-feedback').text(''); diModal.show();
    });

    // Submit: disable domain
    $('#disableDomainForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: `{{ url('admin/control/licenses') }}/${ddId}/disable-domain`, method: 'POST',
            data: { domain: $('#dd_domain').val(), reason: $('#dd_reason').val() },
            success: res => { ddModal.hide(); slsToast(res.message); if (window.reloadLicenses) window.reloadLicenses(); },
            error: xhr => xhr.status===422 ? $('#err_dd_domain').text(xhr.responseJSON.errors.domain?.[0] ?? '') : slsHandleError(xhr)
        });
    });

    // Submit: disable installation
    $('#disableInstallForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: `{{ url('admin/control/licenses') }}/${diId}/disable-installation`, method: 'POST',
            data: { installation_id: $('#di_install').val(), reason: $('#di_reason').val() },
            success: res => { diModal.hide(); slsToast(res.message); if (window.reloadLicenses) window.reloadLicenses(); },
            error: xhr => xhr.status===422 ? $('#err_di_install').text(xhr.responseJSON.errors.installation_id?.[0] ?? '') : slsHandleError(xhr)
        });
    });
});
</script>
