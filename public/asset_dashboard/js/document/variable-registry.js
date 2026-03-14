/**
 * variable-registry.js — daftar variabel aktif, panel chips, place ke canvas
 *
 * Depends: constants.js (MARGIN), utils.js (escHtml, friendlyVarLabel)
 */

var variableRegistry = [];

/**
 * Daftarkan satu variabel ke registry.
 * Tidak melakukan apa-apa jika sudah terdaftar.
 *
 * @param {string} name   nama variabel (snake_case)
 * @param {string} [label] label yang mudah dibaca — jika kosong, pakai friendlyVarLabel()
 */
function registerVariable(name, label) {
    if (variableRegistry.find(function (v) { return v.name === name; })) return;

    variableRegistry.push({
        name:  name,
        label: label || friendlyVarLabel(name),
    });

    renderVariableChips();
}

/**
 * Render ulang panel chips variabel.
 * Panel otomatis disembunyikan jika tidak ada variabel terdaftar.
 */
function renderVariableChips() {
    var panel = document.getElementById('variablePanel');
    var chips = document.getElementById('variableChips');
    if (!panel || !chips) return;

    if (!variableRegistry.length) {
        panel.style.display = 'none';
        return;
    }

    panel.style.display = 'block';
    chips.innerHTML     = '';

    variableRegistry.forEach(function (v) {
        var btn = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'btn btn-primary btn-sm d-flex align-items-center gap-1';
        btn.style.cssText = 'border-radius:20px;font-size:0.8rem';
        btn.innerHTML =
            '<i class="bi bi-braces" style="font-size:0.7rem"></i>' +
            '<span>' + escHtml(v.label) + '</span>' +
            '<code style="font-size:0.7rem;color:rgba(255,255,255,0.75);margin-left:2px">' +
            '{{' + escHtml(v.name) + '}}</code>';

        btn.addEventListener('click', function () {
            placeVariableOnCanvas(v.name);
        });

        chips.appendChild(btn);
    });
}

/**
 * Taruh variabel sebagai Textbox baru di canvas aktif.
 * @param {string} name  nama variabel
 */
function placeVariableOnCanvas(name) {
    var canvas = getCanvas();
    if (!canvas) return;

    var textbox = new fabric.Textbox('{{' + name + '}}', {
        left:       MARGIN + 10,
        top:        200,
        width:      220,
        fontSize:   16,
        fontFamily: 'Arial',
        fill:       '#1a56db',
        name:       'var_' + name,
    });

    canvas.add(textbox);
    canvas.setActiveObject(textbox);
    canvas.requestRenderAll();
}
