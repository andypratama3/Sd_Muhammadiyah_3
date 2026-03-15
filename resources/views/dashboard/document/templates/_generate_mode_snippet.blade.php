
{{-- ── GENERATE MODE SELECTOR ─────────────────────────────────────────── --}}
<div class="tb-field" style="min-width:220px">
    <label style="font-size:.7rem;color:var(--text-muted);margin-bottom:3px;display:block">
        <i class="bi bi-file-earmark-arrow-down" style="font-size:.75rem"></i>
        Dokumen ini untuk
    </label>
    <div class="mode-toggle" id="modeToggle">
        <button type="button"
                class="mode-btn active"
                id="modeBtnPerorang"
                onclick="setGenerateMode('perorang')"
                title="1 baris Excel = 1 PDF&#10;Cocok untuk: ijazah, surat keterangan, raport per siswa">
            <i class="bi bi-person-fill"></i>
            <span>Per Orang</span>
        </button>
        <button type="button"
                class="mode-btn"
                id="modeBtnDaftar"
                onclick="setGenerateMode('daftar')"
                title="Semua baris Excel = 1 PDF&#10;Cocok untuk: daftar hadir, rekap nilai kelas">
            <i class="bi bi-list-ul"></i>
            <span>Daftar</span>
        </button>
    </div>
    {{-- Hidden input yang dikirim saat form submit --}}
    <input type="hidden" name="generate_mode" id="generate_mode"
           value="{{ $template->generate_mode ?? 'perorang' }}">
</div>

{{-- ── MODE INFO TOOLTIP ───────────────────────────────────────────────── --}}
<div id="modeInfoBadge" class="mode-info-badge mode-perorang">
    <i class="bi bi-person-fill"></i>
    <span id="modeInfoText">1 baris → 1 PDF</span>
</div>

{{-- ── CSS ─────────────────────────────────────────────────────────────── --}}
<style>
/* Mode Toggle */
.mode-toggle {
    display: flex;
    background: var(--bg-tertiary, #f1f3f5);
    border-radius: 8px;
    padding: 2px;
    gap: 2px;
    border: 1px solid var(--border-color, #dee2e6);
}

.mode-btn {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border: none;
    border-radius: 6px;
    font-size: .76rem;
    font-weight: 500;
    cursor: pointer;
    transition: all .18s ease;
    background: transparent;
    color: var(--text-muted, #6c757d);
    white-space: nowrap;
}

.mode-btn:hover {
    background: rgba(255,255,255,.7);
    color: var(--text-primary, #212529);
}

.mode-btn.active {
    background: #fff;
    color: var(--accent, #1a5276);
    font-weight: 600;
    box-shadow: 0 1px 4px rgba(0,0,0,.12);
}

.mode-btn.active[data-mode="daftar"],
#modeBtnDaftar.active {
    color: #0f5132;
}

/* Info Badge */
.mode-info-badge {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 600;
    white-space: nowrap;
    transition: all .2s ease;
}

.mode-info-badge.mode-perorang {
    background: rgba(26, 82, 118, .1);
    color: #1a5276;
    border: 1px solid rgba(26, 82, 118, .2);
}

.mode-info-badge.mode-daftar {
    background: rgba(15, 81, 50, .1);
    color: #0f5132;
    border: 1px solid rgba(15, 81, 50, .2);
}
</style>

{{-- ── JS ─────────────────────────────────────────────────────────────── --}}
<script>
/**
 * setGenerateMode — toggle visual dan update hidden input
 * Dipanggil oleh onclick di tombol mode.
 */
function setGenerateMode(mode) {
    // Update hidden input
    var input = document.getElementById('generate_mode');
    if (input) input.value = mode;

    // Update tombol aktif
    var btnPerorang = document.getElementById('modeBtnPerorang');
    var btnDaftar   = document.getElementById('modeBtnDaftar');
    if (btnPerorang) btnPerorang.classList.toggle('active', mode === 'perorang');
    if (btnDaftar)   btnDaftar.classList.toggle('active',   mode === 'daftar');

    // Update info badge
    var badge    = document.getElementById('modeInfoBadge');
    var infoText = document.getElementById('modeInfoText');
    if (badge && infoText) {
        badge.className = 'mode-info-badge mode-' + mode;
        if (mode === 'perorang') {
            badge.querySelector('i').className = 'bi bi-person-fill';
            infoText.textContent = '1 baris → 1 PDF';
        } else {
            badge.querySelector('i').className = 'bi bi-list-ul';
            infoText.textContent = 'Semua baris → 1 PDF';
        }
    }
}

// Inisialisasi saat DOM ready — sinkronkan tampilan dengan nilai awal
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('generate_mode');
    if (input) {
        setGenerateMode(input.value || 'perorang');
    }
});
</script>