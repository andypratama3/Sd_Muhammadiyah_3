/**
 * utils.js — pure helper / utility functions
 * Tidak ada side-effect DOM. Semua fungsi bisa di-test secara isolasi.
 */

// ── Unit conversion ───────────────────────────────────────────

/** Konversi px → pt (untuk export HTML/PDF) */
function pt(px) {
    return parseFloat((px * PX_TO_PT).toFixed(2));
}

/** Konversi mm → px */
function mmToPx(mm) {
    return mm * MM_TO_PX;
}

/** Konversi px → mm (untuk tampilan ruler) */
function pxToMm(px) {
    return px / MM_TO_PX;
}

// ── HTML escaping ─────────────────────────────────────────────

/** Escape HTML entities dasar */
function escHtml(s) {
    return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

/**
 * Escape konten teks biasa (textbox, paragraf).
 * Variabel {{...}} dipertahankan apa adanya.
 */
function escapeContent(text) {
    return (text || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\{\{([^}]+)\}\}/g, function (m, v) {
            return '{{' + v.trim() + '}}';
        });
}

/**
 * Escape konten sel tabel.
 * Newline dikonversi ke <br>, variabel dipertahankan.
 */
function escapeCellContent(text) {
    return (text || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\n/g, '<br>')
        .replace(/\{\{([^}]+)\}\}/g, function (m, v) {
            return '{{' + v.trim() + '}}';
        });
}

// ── Fabric.js helpers ─────────────────────────────────────────

/**
 * Menghitung posisi pojok kiri-atas yang sebenarnya dari sebuah Fabric object,
 * terlepas dari originX/originY-nya.
 * @returns {{ x: number, y: number, w: number, h: number }}
 */
function realTopLeft(obj) {
    var w  = (obj.width  || 0) * (obj.scaleX || 1);
    var h  = (obj.height || 0) * (obj.scaleY || 1);
    var ox = obj.originX || 'left';
    var oy = obj.originY || 'top';
    var x  = obj.left || 0;
    var y  = obj.top  || 0;

    if (ox === 'center') x -= w / 2;
    else if (ox === 'right')  x -= w;

    if (oy === 'center') y -= h / 2;
    else if (oy === 'bottom') y -= h;

    return { x: x, y: y, w: w, h: h };
}

// ── Table layout helpers ──────────────────────────────────────

/**
 * Menghitung lebar tiap kolom berdasarkan specs.
 * null = fill (bagikan sisa lebar secara merata).
 * @param {Array<number|null>} specs
 * @param {number} totalW
 * @returns {number[]}
 */
function buildColWidths(specs, totalW) {
    var fixedTotal = 0;
    var fillCount  = 0;

    specs.forEach(function (s) {
        s === null ? fillCount++ : (fixedTotal += s);
    });

    var fillWidth = fillCount > 0
        ? Math.max(20, (totalW - fixedTotal) / fillCount)
        : 0;

    return specs.map(function (s) {
        return s === null ? fillWidth : s;
    });
}

/**
 * Membangun array tinggi baris: [headerH, rowH, rowH, ...].
 * @param {number} dataRows  jumlah baris data (tidak termasuk header)
 * @param {number} rowH      tinggi baris data
 * @param {number} headerH   tinggi baris header
 * @returns {number[]}
 */
function buildRowHeights(dataRows, rowH, headerH) {
    var arr = [headerH];
    for (var i = 0; i < dataRows; i++) arr.push(rowH);
    return arr;
}

// ── Canvas state helpers ──────────────────────────────────────

/** Mengembalikan canvas aktif (halaman sedang aktif). */
function getCanvas() {
    return pages[currentPage] ? pages[currentPage].canvas : null;
}

/** Mengembalikan tableStore halaman aktif. */
function getTableStore() {
    return pages[currentPage] ? pages[currentPage].tableStore : TABLE_STORE;
}

// ── Variable label mapping ────────────────────────────────────

/**
 * Mengubah nama variabel snake_case menjadi label yang mudah dibaca.
 * Mapping statis sesuai TemplateVariableRegistry.php — harus selalu sinkron.
 *
 * @param {string} varName  nama variabel (snake_case)
 * @returns {string}        label yang mudah dibaca user
 */
function friendlyVarLabel(varName) {
    var map = {
        // ── Data Siswa ──────────────────────────────
        'nama_siswa'      : 'Nama Siswa',
        'nisn'            : 'NISN',
        'nis'             : 'NIS',
        'jenis_kelamin'   : 'Jenis Kelamin',
        'tempat_lahir'    : 'Tempat Lahir',
        'tanggal_lahir'   : 'Tanggal Lahir',
        'agama'           : 'Agama',
        'nama_ayah'       : 'Nama Ayah',
        'nama_ibu'        : 'Nama Ibu',
        'pekerjaan_ayah'  : 'Pekerjaan Ayah',
        'pekerjaan_ibu'   : 'Pekerjaan Ibu',
        'alamat_siswa'    : 'Alamat',
        'no_hp'           : 'No. HP',
        'nama_wali'       : 'Nama Wali',
        // ── Data Sekolah ────────────────────────────
        'nama_sekolah'    : 'Nama Sekolah',
        'alamat_sekolah'  : 'Alamat Sekolah',
        'kepala_sekolah'  : 'Kepala Sekolah',
        'nip'             : 'NIP/NBM',
        'tahun_ajaran'    : 'Tahun Ajaran',
        'semester'        : 'Semester',
        'wali_kelas'      : 'Wali Kelas',
        'nbm_wali'        : 'NBM Wali Kelas',
        // ── Data Surat ──────────────────────────────
        'nomor_surat'     : 'Nomor Surat',
        'tanggal'         : 'Tanggal',
        'perihal'         : 'Perihal',
        'keterangan'      : 'Keterangan',
        'isi'             : 'Isi Surat',
        'tujuan'          : 'Tujuan',
        'tembusan'        : 'Tembusan',
        // ── Nilai & Prestasi ────────────────────────
        'kelas'           : 'Kelas',
        'fase'            : 'Fase',
        'nama_kelas'      : 'Nama Kelas',
        'nilai_rata'      : 'Nilai Rata-rata',
        'peringkat'       : 'Peringkat',
        'predikat'        : 'Predikat',
        'catatan'         : 'Catatan',
        'naik_kelas'      : 'Naik Kelas',
        'mata_pelajaran'  : 'Mata Pelajaran',
        // ── Lainnya ─────────────────────────────────
        'nama_ortu'       : 'Nama Orang Tua',
        // ── Sistem ──────────────────────────────────
        'barcode_signature'    : 'Barcode / TTD Digital',
        'name_kepala_sekolah'  : 'Nama Kepala Sekolah',
    };

    if (map[varName]) return map[varName];

    // Auto-generate raport: nilai_xxx → "Nilai Xxx"
    var nilaiMatch = varName.match(/^nilai_(.+)$/);
    if (nilaiMatch) {
        return 'Nilai ' + nilaiMatch[1].replace(/_/g, ' ')
            .replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    // Auto-generate raport: capaian_xxx → "Capaian Xxx"
    var capaianMatch = varName.match(/^capaian_(.+)$/);
    if (capaianMatch) {
        return 'Capaian ' + capaianMatch[1].replace(/_/g, ' ')
            .replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    // Fallback: snake_case → Title Case
    return varName.split('_').map(function (w) {
        return w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
    }).join(' ');
}
