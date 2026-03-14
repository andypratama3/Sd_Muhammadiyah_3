/**
 * elements.js — tambah & hapus elemen di canvas
 *
 * IMPROVEMENTS:
 *  - SweetAlert2 untuk semua dialog
 *  - addText() lebih cerdas (tidak tumpuk)
 *  - addShape() baru untuk shapes dasar
 *  - addLine() untuk garis
 *  - removeSelected() menggunakan konfirmasi Swal
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
    while (usedTops.indexOf(newTop) !== -1) newTop += 28;

    var textbox = new fabric.Textbox('Tulis teks di sini', {
        left:       MARGIN + 10,
        top:        newTop,
        width:      300,
        fontSize:   14,
        fontFamily: 'Arial',
        fill:       '#000000',
        lineHeight: 1.4,
    });

    canvas.add(textbox);
    canvas.setActiveObject(textbox);
    canvas.requestRenderAll();
    saveState();

    // Enter edit mode otomatis
    setTimeout(function () {
        canvas.setActiveObject(textbox);
        textbox.enterEditing();
        textbox.selectAll();
        canvas.requestRenderAll();
    }, 50);
}

// ─────────────────────────────────────────────────────────────
// SHAPES
// ─────────────────────────────────────────────────────────────

function addShape(type) {
    var canvas = getCanvas();
    if (!canvas) return;

    var shape;
    var commonProps = {
        left:        MARGIN + 50,
        top:         200,
        fill:        'transparent',
        stroke:      '#1a5276',
        strokeWidth: 2,
        selectable:  true,
        evented:     true,
        lockRotation: false,
    };

    switch (type) {
        case 'rect':
            shape = new fabric.Rect(Object.assign({}, commonProps, { width: 200, height: 80, rx: 4, ry: 4 }));
            break;
        case 'circle':
            shape = new fabric.Ellipse(Object.assign({}, commonProps, { rx: 60, ry: 60 }));
            break;
        case 'triangle':
            shape = new fabric.Triangle(Object.assign({}, commonProps, { width: 120, height: 100 }));
            break;
        case 'line':
            shape = new fabric.Line([0, 0, 300, 0], Object.assign({}, commonProps, {
                strokeWidth: 1.5, fill: null,
            }));
            break;
        case 'hline': // Garis horizontal tebal (separator)
            shape = new fabric.Line([MARGIN, 0, CANVAS_W - MARGIN, 0], {
                left: MARGIN, top: 200,
                stroke: '#000', strokeWidth: 2,
                selectable: true, evented: true,
            });
            break;
        default:
            return;
    }

    canvas.add(shape);
    canvas.setActiveObject(shape);
    canvas.requestRenderAll();
    saveState();
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

    // Validasi ukuran file (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire({
            icon: 'warning',
            title: 'File Terlalu Besar',
            text: 'Ukuran file maksimal 5MB.',
            confirmButtonColor: '#1a5276',
        });
        e.target.value = '';
        return;
    }

    var reader = new FileReader();
    reader.onload = function (ev) {
        fabric.Image.fromURL(ev.target.result, function (img) {
            // Scale agar tidak melebihi lebar canvas
            var maxW = CANVAS_W - MARGIN * 2;
            if (img.width > maxW) img.scaleToWidth(maxW);
            img.set({ left: MARGIN, top: 150 });
            canvas.add(img);
            canvas.setActiveObject(img);
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
            img.scaleToHeight(100);
            img.set({ left: 20, top: 20, name: 'logo' });
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

    registerVariable('barcode_signature',   'Barcode / TTD Digital');
    registerVariable('name_kepala_sekolah', 'Nama Kepala Sekolah');

    var group = new fabric.Group([
        new fabric.Rect({
            width: 120, height: 130,
            fill: '#fafafa', stroke: '#e2e8f0', strokeWidth: 1,
            rx: 6, ry: 6, left: 0, top: 0,
        }),
        new fabric.Text('Kepala Sekolah', {
            fontSize: 10, fontFamily: 'Arial',
            fill: '#1a5276', fontWeight: 'bold',
            textAlign: 'center', originX: 'center',
            left: 60, top: 8,
        }),
        // QR placeholder box
        new fabric.Rect({
            width: 70, height: 70,
            fill: '#f0f4f8', stroke: '#cbd5e1', strokeWidth: 1,
            rx: 4, ry: 4,
            originX: 'center', originY: 'center',
            left: 60, top: 55,
        }),
        new fabric.Text('{{barcode_signature}}', {
            fontSize: 7, fontFamily: 'Courier New',
            fill: '#64748b', textAlign: 'center',
            originX: 'center', originY: 'center',
            left: 60, top: 55,
        }),
        new fabric.Text('{{name_kepala_sekolah}}', {
            fontSize: 10, fontFamily: 'Arial',
            fill: '#1a5276', fontWeight: 'bold',
            textAlign: 'center', originX: 'center',
            left: 60, top: 105,
        }),
    ], {
        left:        620,
        top:         860,
        name:        'barcode',
        selectable:  true,
        evented:     true,
        hasBorders:  true,
        hasControls: true,
        lockRotation: false,
    });

    canvas.add(group);
    canvas.setActiveObject(group);
    canvas.requestRenderAll();
    saveState();

    Swal.fire({
        toast: true, position: 'bottom-end', icon: 'success',
        title: 'Barcode signature ditambahkan',
        showConfirmButton: false, timer: 2000,
    });
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
        Swal.fire({
            toast: true, position: 'bottom-end', icon: 'info',
            title: 'Pilih elemen terlebih dahulu',
            showConfirmButton: false, timer: 2000,
        });
        return;
    }

    // Hapus kop surat — minta konfirmasi
    if (obj.name && obj.name.startsWith('kop_')) {
        Swal.fire({
            title: 'Hapus Kop Surat?',
            text: 'Seluruh elemen kop surat akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-trash"></i> Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            canvas.getObjects()
                .filter(function (o) { return o.name && o.name.startsWith('kop_'); })
                .forEach(function (o) { canvas.remove(o); });
            canvas.discardActiveObject();
            canvas.requestRenderAll();
            saveState();
            Swal.fire({ toast: true, position: 'bottom-end', icon: 'success', title: 'Kop surat dihapus', showConfirmButton: false, timer: 1500 });
        });
        return;
    }

    // Hapus tabel — minta konfirmasi
    if (obj._isTable || (obj.name && pg.tableStore[obj.name])) {
        Swal.fire({
            title: 'Hapus Tabel?',
            text: 'Data tabel ini akan dihapus dari canvas.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-trash"></i> Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
        }).then(function (result) {
            if (!result.isConfirmed) return;
            if (obj.name && pg.tableStore[obj.name]) {
                delete pg.tableStore[obj.name];
            }
            canvas.remove(obj);
            canvas.discardActiveObject();
            canvas.requestRenderAll();
            saveState();
        });
        return;
    }

    // Hapus elemen biasa — langsung (tanpa konfirmasi untuk UX cepat)
    canvas.remove(obj);
    canvas.discardActiveObject();
    canvas.requestRenderAll();
    saveState();
}
