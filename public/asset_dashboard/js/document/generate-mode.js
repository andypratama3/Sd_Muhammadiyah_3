/**
 * generate-mode.js
 * Mengelola toggle generate_mode di topbar (Per Orang / Daftar).
 * Dimuat setelah semua JS editor — pastikan include di @push('js')
 * SETELAH init.js, atau gunakan DOMContentLoaded.
 */

(function () {
    'use strict';

    /**
     * Ubah mode aktif: update hidden input + toggle class .active di dua tombol.
     * @param {'perorang'|'daftar'} mode
     */
    window.setGenerateMode = function (mode) {
        var input = document.getElementById('generate_mode');
        var btnP  = document.getElementById('modeBtnPerorang');
        var btnD  = document.getElementById('modeBtnDaftar');

        if (input) input.value = mode;
        if (btnP)  btnP.classList.toggle('active',  mode === 'perorang');
        if (btnD)  btnD.classList.toggle('active',  mode === 'daftar');
    };

    /* Sync tampilan saat halaman pertama dibuka (baik create maupun edit) */
    function _initMode() {
        var input = document.getElementById('generate_mode');
        if (!input) return;                    // snippet tidak ada di halaman ini
        setGenerateMode(input.value || 'perorang');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', _initMode);
    } else {
        _initMode();                           // DOM sudah siap (misal: script di akhir body)
    }
})();