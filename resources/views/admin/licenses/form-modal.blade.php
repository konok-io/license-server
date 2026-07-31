<div class="modal fade" id="licenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="licenseForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="licenseModalLabel">Issue License</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="licenseId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6" id="customerRow">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">Select customer…</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}@if($customer->company) — {{ $customer->company }}@endif</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-block" id="err_customer_id"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type">
                                @foreach(App\Enums\LicenseType::cases() as $t)
                                    <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-block" id="err_type"></div>
                        </div>

                        {{-- Status only shown on edit --}}
                        <div class="col-md-6" id="statusRow" style="display:none">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                @foreach(App\Enums\LicenseStatus::cases() as $s)
                                    <option value="{{ $s->value }}">{{ $s->label() }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback d-block" id="err_status"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Product</label>
                            <input type="text" class="form-control" id="product" name="product" value="mrh-software-erp">
                            <div class="invalid-feedback d-block" id="err_product"></div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Version</label>
                            <input type="text" class="form-control" id="version" name="version" value="1.0.0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Activations <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_activations" name="max_activations" value="1" min="1">
                            <div class="invalid-feedback d-block" id="err_max_activations"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Grace Days</label>
                            <input type="number" class="form-control" id="grace_days" name="grace_days" value="3" min="0">
                            <div class="invalid-feedback d-block" id="err_grace_days"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Verify Interval (hrs)</label>
                            <input type="number" class="form-control" id="verification_interval_hours" name="verification_interval_hours" value="24" min="1">
                            <div class="invalid-feedback d-block" id="err_verification_interval_hours"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Starts At</label>
                            <input type="date" class="form-control" id="starts_at" name="starts_at">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expires At</label>
                            <input type="date" class="form-control" id="expires_at" name="expires_at">
                            <div class="invalid-feedback d-block" id="err_expires_at"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Save license</button>
                </div>
            </form>
        </div>
    </div>
</div>

