<div class="tb-field" style="gap:3px;min-width:0;">
    <label>
        <i class="bi bi-layers" style="font-size:.65rem;opacity:.7"></i>
        Mode Dokumen
    </label>

    <div class="tb-mode-toggle">
        <button type="button"
                class="tb-mode-btn active"
                id="modeBtnPerorang"
                onclick="setGenerateMode('perorang')"
                title="1 baris Excel = 1 PDF — cocok untuk ijazah, surat, raport per siswa">
            <i class="bi bi-person-fill"></i>
            <span>Per Orang</span>
        </button>
        <button type="button"
                class="tb-mode-btn"
                id="modeBtnDaftar"
                onclick="setGenerateMode('daftar')"
                title="Semua baris Excel = 1 PDF — cocok untuk daftar hadir, rekap kelas">
            <i class="bi bi-list-ul"></i>
            <span>Daftar</span>
        </button>
    </div>

    <input type="hidden" name="generate_mode" id="generate_mode"
           value="{{ old('generate_mode', isset($template) ? ($template->generate_mode ?? 'perorang') : 'perorang') }}">
</div>