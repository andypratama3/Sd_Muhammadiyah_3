/**
 * raport-api.js — fetch kelas & mapel dari API, preview tabel raport,
 *                 toggle kelompok aktif
 *
 * Depends: constants.js (escHtml), table-renderer.js (_raportKelompoks, _raportAktifKelompok)
 */

// Auto-load daftar tingkat kelas saat halaman pertama kali dimuat
(function autoLoadTingkatKelas() {
    fetch('/dashboard/surat/templates/api/kelas-list', {
        headers: { 'Accept': 'application/json' },
    })
    .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
    .then(function (data) {
        var sel = document.getElementById('raportTingkatKelas');
        if (!sel) return;

        data.forEach(function (tk) {
            var opt = document.createElement('option');
            opt.value = tk.id;

            var categoryLabel = '';
            if (tk.category_kelas) {
                try {
                    var parsed = JSON.parse(tk.category_kelas);
                    categoryLabel = ' (' + (Array.isArray(parsed) ? parsed.join(', ') : tk.category_kelas) + ')';
                } catch (e) {
                    categoryLabel = ' (' + tk.category_kelas + ')';
                }
            }

            opt.textContent = tk.name + categoryLabel;
            sel.appendChild(opt);
        });

        // Simpan ke global jika belum ada
        if (!window.EDITOR_KELAS_LIST || !window.EDITOR_KELAS_LIST.length) {
            window.EDITOR_KELAS_LIST = data;
        }
    })
    .catch(function () {});
})();

// ── DOMContentLoaded: pasang listener kelas & tombol muat mapel ──
document.addEventListener('DOMContentLoaded', function () {
    var selKelas = document.getElementById('raportTingkatKelas');
    var btnLoad  = document.getElementById('btnLoadMapel');

    if (selKelas) {
        selKelas.addEventListener('change', function () {
            var id = this.value;

            // Simpan ke hidden input kelas_id (untuk form rapot)
            var hiddenKelas = document.getElementById('kelas_id');
            if (hiddenKelas) hiddenKelas.value = id;

            if (btnLoad) btnLoad.disabled = !id;
            if (id) loadMapelFromAPI(id);
        });
    }

    if (btnLoad) {
        btnLoad.addEventListener('click', function () {
            var id = document.getElementById('raportTingkatKelas').value;
            var hiddenKelas = document.getElementById('kelas_id');
            if (hiddenKelas) hiddenKelas.value = id;
            if (id) loadMapelFromAPI(id);
        });
    }
});

// ── Load mapel dari API ───────────────────────────────────────

/**
 * Buat slug variabel yang bersih dari nama mapel.
 *
 * Tidak menggunakan mp.id karena bisa berupa UUID panjang.
 * Cukup gunakan nama mapel yang di-slugify — sudah cukup unik
 * dalam konteks satu template raport.
 *
 * Contoh:
 *  { name: "Bahasa Arab" }  → "bahasa_arab"
 *  { name: "Tahfidz"     }  → "tahfidz"
 *  { name: "Al-Qur'an"   }  → "alquran"
 *
 * @param {{ name: string }} mp
 * @returns {string}
 */
function _buildMapelSlug(mp) {
    return (mp.name || '')
        .toLowerCase()
        .replace(/[''']/g, '')      // hapus apostrof
        .replace(/[^a-z0-9]+/g, '_') // non-alphanumeric → underscore
        .replace(/_+/g, '_')        // collapse underscore ganda
        .replace(/^_|_$/g, '');     // trim underscore di tepi
}

function loadMapelFromAPI(tingkatId) {
    var previewEl = document.getElementById('raportMapelPreview');
    if (!previewEl) return;

    previewEl.innerHTML =
        '<div class="text-center py-3">' +
        '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat...' +
        '</div>';

    fetch('/dashboard/surat/templates/api/mapel-list?kelas_id=' + tingkatId, {
        headers: { 'Accept': 'application/json' },
    })
    .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
    .then(function (data) {
        // Format: array mapel polos ATAU {kelompoks: [...]}
        var isPlainArray = Array.isArray(data);

        if (isPlainArray) {
            _raportKelompoks = [{
                kelompok: { id: 'all', nama: 'Mata Pelajaran', warna_header: '#1a5276' },
                mapels: data.map(function (mp) {
                    var slug = _buildMapelSlug(mp);
                    return {
                        nama:        mp.name,
                        var_nilai:   'nilai_'   + slug,
                        var_capaian: 'capaian_' + slug,
                    };
                }),
            }];
        } else {
            _raportKelompoks = data.kelompoks || [];
        }

        // Semua kelompok aktif secara default
        _raportAktifKelompok = {};
        _raportKelompoks.forEach(function (k) {
            _raportAktifKelompok[k.kelompok.id] = true;
        });

        renderRaportPreview();
        renderKelompokToggles();
    })
    .catch(function () {
        previewEl.innerHTML =
            '<div class="alert alert-danger small py-2">Gagal memuat mapel.</div>';
    });
}

// ── Render preview tabel raport ───────────────────────────────

function renderRaportPreview() {
    var previewEl = document.getElementById('raportMapelPreview');
    if (!previewEl) return;

    if (!_raportKelompoks.length) {
        previewEl.innerHTML =
            '<div class="alert alert-warning small py-2">Tidak ada mapel.</div>';
        return;
    }

    var html = '';

    _raportKelompoks.forEach(function (grp) {
        var kel = grp.kelompok;
        if (_raportAktifKelompok[kel.id] === false) return;

        var headerBg = kel.warna_header || '#1a5276';

        html +=
            '<div class="mb-2">' +
            '<div class="px-2 py-1 rounded-top small fw-bold text-white d-flex justify-content-between" ' +
            'style="background:' + headerBg + '">' +
            '<span>' + escHtml(kel.nama) + '</span>' +
            '<span class="badge bg-white text-dark">' + grp.mapels.length + ' mapel</span>' +
            '</div>' +
            '<table class="table table-sm table-bordered mb-0" style="font-size:0.8rem">' +
            '<thead class="table-light"><tr>' +
            '<th style="width:30px">No</th>' +
            '<th>Mata Pelajaran</th>' +
            '<th style="width:80px">Var Nilai</th>' +
            '<th style="width:100px">Var Capaian</th>' +
            '</tr></thead><tbody>';

        grp.mapels.forEach(function (mp, i) {
            html +=
                '<tr>' +
                '<td class="text-center">' + (i + 1) + '</td>' +
                '<td>' + escHtml(mp.nama) + '</td>' +
                '<td><code class="text-primary small">{{' + mp.var_nilai   + '}}</code></td>' +
                '<td><code class="text-success small">{{' + mp.var_capaian + '}}</code></td>' +
                '</tr>';
        });

        html += '</tbody></table></div>';
    });

    previewEl.innerHTML = html || '<p class="text-muted small">Semua kelompok di-nonaktifkan.</p>';
}

// ── Toggle kelompok aktif ─────────────────────────────────────

function renderKelompokToggles() {
    var container = document.getElementById('raportKelompokToggle');
    if (!container) return;
    container.innerHTML = '';

    _raportKelompoks.forEach(function (grp) {
        var kel   = grp.kelompok;
        var aktif = _raportAktifKelompok[kel.id] !== false;

        var btn = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'btn btn-sm ' + (aktif ? 'btn-primary' : 'btn-outline-secondary');
        btn.style.fontSize = '0.75rem';
        btn.textContent    = kel.nama;

        btn.addEventListener('click', function () {
            _raportAktifKelompok[kel.id] = !_raportAktifKelompok[kel.id];
            renderRaportPreview();
            renderKelompokToggles();
        });

        container.appendChild(btn);
    });
}