/**
 * elements.js — tambah & hapus elemen di canvas
 *
 * Depends: constants.js, utils.js, variable-registry.js (registerVariable)
 */

// ─────────────────────────────────────────────────────────────
// TEKS
// ─────────────────────────────────────────────────────────────

function addText() {
    var canvas = getCanvas();
    if (!canvas) return;

    // Cari posisi Y yang belum ditempati
    var usedTops = canvas.getObjects().map(function (o) {
        return Math.round(o.top || 0);
    });
    var newTop = 180;
    while (usedTops.indexOf(newTop) !== -1) newTop += 24;

    canvas.add(new fabric.Textbox('Tulis teks di sini', {
        left:       MARGIN + 10,
        top:        newTop,
        width:      300,
        fontSize:   16,
        fontFamily: 'Arial',
        fill:       '#000000',
    }));

    canvas.requestRenderAll();
}

// ─────────────────────────────────────────────────────────────
// GAMBAR
// ─────────────────────────────────────────────────────────────

function triggerImageUpload() {
    document.getElementById('imageUpload').click();
}

function addImage(e) {
    var canvas = getCanvas();
    if (!canvas) return;
    var file = e.target.files[0];
    if (!file) return;

    var reader = new FileReader();
    reader.onload = function (ev) {
        fabric.Image.fromURL(ev.target.result, function (img) {
            img.scaleToWidth(200);
            img.set({ left: 100, top: 100 });
            canvas.add(img);
            canvas.requestRenderAll();
            saveState();
        });
    };
    reader.readAsDataURL(file);
    e.target.value = '';
}

function triggerLogoUpload() {
    document.getElementById('logoUpload').click();
}

function addLogoImage(e) {
    var canvas = getCanvas();
    if (!canvas) return;
    var file = e.target.files[0];
    if (!file) return;

    var reader = new FileReader();
    reader.onload = function (ev) {
        fabric.Image.fromURL(ev.target.result, function (img) {
            img.scaleToWidth(100);
            img.set({ left: 40, top: 30, name: 'logo' });
            canvas.add(img);
            canvas.requestRenderAll();
            saveState();
        });
    };
    reader.readAsDataURL(file);
    e.target.value = '';
}

// ─────────────────────────────────────────────────────────────
// BARCODE SIGNATURE
// ─────────────────────────────────────────────────────────────

function addBarcode() {
    var canvas = getCanvas();
    if (!canvas) return;

    // Daftarkan variabel ke registry
    registerVariable('barcode_signature',   'Barcode / TTD Digital');
    registerVariable('name_kepala_sekolah', 'Nama Kepala Sekolah');

    var group = new fabric.Group([

        // Area putih tanpa border
        new fabric.Rect({
            width: 120, height: 120,
            fill: '#ffffff', stroke: '#ffffff', strokeWidth: 0,
            rx: 4, ry: 4, left: 0, top: 0,
        }),

        // Label judul
        new fabric.Text('Kepala Sekolah', {
            fontSize: 12, fontFamily: 'Arial',
            fill: '#000000', fontWeight: 'bold',
            textAlign: 'center', originX: 'center',
            left: 60, top: 0,
        }),

        // Placeholder variabel barcode
        new fabric.Text('{{barcode_signature}}', {
            fontSize: 10, fontFamily: 'Courier New',
            fill: '#333333', textAlign: 'center',
            originX: 'center', originY: 'center',
            left: 60, top: 60,
        }),

        // Nama kepala sekolah
        new fabric.Text('{{name_kepala_sekolah}}', {
            fontSize: 12, fontFamily: 'Arial',
            fill: '#000000', fontWeight: 'bold',
            textAlign: 'center', originX: 'center',
            left: 60, top: 135,
        }),

    ], {
        left:        620,
        top:         860,
        name:        'barcode',
        selectable:  true,
        evented:     true,
        hasBorders:  true,
        hasControls: true,
        lockRotation: true,
    });

    canvas.add(group);
    canvas.setActiveObject(group);
    canvas.requestRenderAll();
    saveState();
}

// ─────────────────────────────────────────────────────────────
// HAPUS ELEMEN
// ─────────────────────────────────────────────────────────────

function removeSelected() {
    var canvas = getCanvas();
    if (!canvas) return;
    var pg = pages[currentPage];
    if (!pg) return;

    var obj = canvas.getActiveObject();
    if (!obj) {
        alert('Pilih elemen terlebih dahulu.');
        return;
    }

    // Hapus seluruh kop surat sekaligus
    if (obj.name && obj.name.startsWith('kop_')) {
        if (!confirm('Hapus seluruh kop surat?')) return;
        canvas.getObjects()
            .filter(function (o) { return o.name && o.name.startsWith('kop_'); })
            .forEach(function (o) { canvas.remove(o); });
    } else {
        if (obj.name && pg.tableStore[obj.name]) {
            delete pg.tableStore[obj.name];
        }
        canvas.remove(obj);
    }

    canvas.discardActiveObject();
    canvas.requestRenderAll();
    saveState();
}
