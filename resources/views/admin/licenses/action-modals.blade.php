{{-- One-time key reveal --}}
<div class="modal fade" id="keyRevealModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-key me-2"></i>License Key Generated</h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Copy this key now. For security it is shown only once and cannot be retrieved later.
                </div>
                <div class="input-group">
                    <input type="text" class="form-control mono fw-bold text-center" id="revealedKey" readonly>
                    <button class="btn btn-accent" type="button" id="btnCopyKey"><i class="bi bi-clipboard"></i> Copy</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">I've saved the key</button>
            </div>
        </div>
    </div>
</div>

{{-- Kill switch --}}
<div class="modal fade" id="killModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="killForm">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-x-octagon me-2"></i>Engage Kill Switch</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">The license will be blocked on its next verification and all active installations will be revoked. Provide a reason for the audit trail.</p>
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="killReason" rows="3" required></textarea>
                    <div class="invalid-feedback d-block" id="err_reason"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Kill license</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reset --}}
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="resetForm">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise me-2"></i>Reset License</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Resetting clears all active activations, rotates the RSA key version, and re-enables activation. Clients must re-activate afterwards.</p>
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="resetReason" rows="3" required></textarea>
                    <div class="invalid-feedback d-block" id="err_reset_reason"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Reset license</button>
                </div>
            </form>
        </div>
    </div>
</div>
