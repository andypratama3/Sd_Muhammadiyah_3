{{-- resources/views/dashboard/document/templates/_modal_variable.blade.php --}}
<div class="modal fade" id="modalVariable" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title fs-6">
                    <i class="bi bi-braces text-primary me-2"></i>Tambah Variabel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nama Variabel</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text text-primary fw-bold">{</span>
                            <input type="text" id="varNameInput" class="form-control"
                                placeholder="contoh: nama_siswa">
                            <span class="input-group-text text-primary fw-bold">}</span>
                        </div>
                        <div class="invalid-feedback" id="varNameError">Nama variabel wajib diisi.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Label <span class="text-muted fw-normal">(opsional)</span></label>
                        <input type="text" id="varLabelInput" class="form-control form-control-sm"
                            placeholder="contoh: Nama Siswa">
                    </div>
                    <div class="col-12">
                        <div class="p-2 bg-light rounded small text-muted border">
                            Preview: <code id="varPreviewCode">nama_variabel</code>
                        </div>
                    </div>
                </div>

                {{-- Preset per grup dari TemplateVariableRegistry --}}
                <label class="form-label fw-semibold small d-block mb-2">
                    Variabel Umum — klik untuk pilih
                </label>

                <div class="accordion accordion-flush" id="varAccordion">
                    @foreach($variableGroups as $groupKey => $group)
                        @if(($group['title'] ?? '') === 'Sistem (Otomatis)')
                            @continue
                        @endif
                        <div class="accordion-item border rounded mb-1">
                            <h2 class="accordion-header">
                                <button class="accordion-button py-2 collapsed"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#varGroup_{{ $groupKey }}"
                                    style="font-size:.82rem;font-weight:700;background:var(--bs-light)">
                                    <i class="{{ $group['icon'] ?? 'bi bi-tag' }} me-2"
                                       style="color:{{ $group['color'] ?? '#1a5276' }}"></i>
                                    {{ $group['title'] }}
                                    <span class="badge bg-secondary ms-2 fw-normal" style="font-size:.68rem">
                                        {{ count($group['variables']) }}
                                    </span>
                                </button>
                            </h2>
                            <div id="varGroup_{{ $groupKey }}"
                                 class="accordion-collapse collapse"
                                 data-bs-parent="#varAccordion">
                                <div class="accordion-body py-2 px-3">
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($group['variables'] as $var)
                                            <button type="button"
                                                class="btn btn-outline-secondary btn-sm preset-var-btn"
                                                style="font-size:.75rem;border-radius:20px"
                                                data-name="{{ $var['key'] }}"
                                                data-label="{{ $var['label'] }}">
                                                {{ $var['key'] }}
                                                <small class="text-muted ms-1">{{ $var['label'] }}</small>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm"
                    data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnConfirmVariable">
                    <i class="bi bi-plus-circle me-1"></i>Tambah ke Canvas
                </button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
// ── Preset buttons ────────────────────────────────────────────────────────────
document.querySelectorAll('.preset-var-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('varNameInput').value  = this.dataset.name;
        document.getElementById('varLabelInput').value = this.dataset.label;
        document.getElementById('varPreviewCode').textContent = '{{ ' + this.dataset.name + ' }}';
        document.getElementById('varNameInput').classList.remove('is-invalid');
    });
});

// ── Preview update ────────────────────────────────────────────────────────────
document.getElementById('varNameInput').addEventListener('input', function () {
    this.classList.remove('is-invalid');
    var val = this.value.trim().replace(/\s+/g, '_').toLowerCase() || 'nama_variabel';
    document.getElementById('varPreviewCode').textContent = '{{ ' + val + ' }}';
});

// ── Confirm ───────────────────────────────────────────────────────────────────
document.getElementById('btnConfirmVariable').addEventListener('click', function () {
    var raw = document.getElementById('varNameInput').value.trim();
    if (!raw) {
        document.getElementById('varNameInput').classList.add('is-invalid');
        document.getElementById('varNameInput').focus();
        return;
    }
    var name  = raw.replace(/\s+/g, '_').toLowerCase();
    var label = document.getElementById('varLabelInput').value.trim() || name;

    if (typeof registerVariable === 'function') registerVariable(name, label);
    if (typeof placeVariableOnCanvas === 'function') placeVariableOnCanvas(name);

    // Reset form
    document.getElementById('varNameInput').value  = '';
    document.getElementById('varLabelInput').value = '';
    document.getElementById('varPreviewCode').textContent = 'Nama variable';

    bootstrap.Modal.getInstance(document.getElementById('modalVariable'))?.hide();
});

document.getElementById('varNameInput').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnConfirmVariable').click(); }
});
</script>
@endpush