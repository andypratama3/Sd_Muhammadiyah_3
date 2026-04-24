@extends('layouts.dashboard')

@section('title', 'Rekap Absensi Sholat')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
    <style>
        .filter-section { border-radius: .5rem; margin-bottom: 1.5rem; }
        .filter-title {
            font-size: 1rem; font-weight: 600; margin-bottom: 1rem;
            display: flex; align-items: center; gap: .5rem;
        }
        .dt-buttons .dt-button {
            border: 0; border-radius: .375rem; color: #fff !important;
            padding: .45rem .75rem; font-size: .875rem; margin-right: .4rem;
        }
        .dt-button.buttons-pdf   { background: #dc3545 !important; }
        .dt-button.buttons-excel { background: #198754 !important; }
        .dt-button.buttons-print { background: #0d6efd !important; }
        .invalid-feedback { display: block; color: #dc3545; font-size: .875rem; margin-top: .25rem; }
        .is-invalid { border-color: #dc3545 !important; }

        /* Tabel Rekap Sholat Enhancements */
        #table_absensi_sholat td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
        #table_absensi_sholat thead th {
            text-align: center;
            vertical-align: middle;
            font-weight: 600;
        }
        .badge {
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        /* Pastikan SweetAlert selalu di atas modal Bootstrap */
        .swal-on-modal { z-index: 99999 !important; }
    </style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Filter --}}
        <div class="filter-section">
            <div class="filter-title">
                <i class="fas fa-filter"></i> Pengaturan Filter
            </div>
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="date_range" class="mb-2">
                        <i class="fas fa-calendar-alt"></i> Periode Tanggal
                    </label>
                    <input type="text" id="date_range" name="date" class="form-control"
                           placeholder="Pilih rentang tanggal (dd-mm-yyyy : dd-mm-yyyy)"
                           autocomplete="off">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="gap-2 d-flex w-100">
                        <button type="button" id="btn_filter" class="btn btn-primary">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <button type="button" id="btn_reset" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="mt-3 table-responsive">
            <table class="table table-bordered table-striped" id="table_absensi_sholat">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Karyawan</th>
                        <th class="text-center">Dhuha</th>
                        <th class="text-center">Dzuhur</th>
                        <th class="text-center" style="min-width:220px;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>

{{-- Modal Tambah / Edit --}}
<div class="modal fade" id="modalAbsensiSholat" tabindex="-1"
     aria-labelledby="modalAbsensiSholatLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAbsensiSholatLabel">Tambah Data Absensi Sholat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Tidak perlu @csrf — submit via AJAX, token dihandle $.ajaxSetup --}}
            <form id="formAbsensiSholat">
                <div class="modal-body">
                    <input type="hidden" id="edit_id"          name="id">
                    <input type="hidden" id="edit_karyawan_id" name="karyawan_id">

                    <div class="mb-3 form-group">
                        <label for="edit_nama" class="form-label">
                            <i class="fas fa-user"></i> Nama Karyawan
                        </label>
                        <input type="text" class="form-control" id="edit_nama" disabled>
                        <small class="text-muted">Field ini tidak dapat diubah</small>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="edit_tanggal" class="form-label">
                            <i class="fas fa-calendar"></i> Tanggal
                        </label>
                        <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
                        <div class="invalid-feedback" id="error_tanggal"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="edit_jenis" class="form-label">
                            <i class="fas fa-pray"></i> Jenis Sholat
                        </label>
                        <select class="form-select" id="edit_jenis" name="jenis_sholat" required>
                            <option value="duha">Dhuha</option>
                            <option value="dzuhur">Dzuhur</option>
                            <option value="izin">Izin</option>
                        </select>
                        <div class="invalid-feedback" id="error_jenis_sholat"></div>
                    </div>

                    <div class="mb-3 form-group" id="wrap_jam_sholat">
                        <label for="edit_jam" class="form-label">
                            <i class="fas fa-clock"></i> Jam Sholat
                        </label>
                        <input type="time" class="form-control" id="edit_jam" name="jam_sholat" step="60">
                        <small class="text-muted">Kosongkan jika Izin</small>
                        <div class="invalid-feedback" id="error_jam_sholat"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn_save">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function () {

    // ── CSRF global — tidak perlu tulis ulang di setiap AJAX call ──────────
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ── Modal instance tunggal ─────────────────────────────────────────────
    // backdrop: 'static' → modal TIDAK tertutup saat klik di luar (termasuk
    // saat overlay SweetAlert muncul di atas modal).
    // keyboard: false    → modal tidak tertutup saat tekan Escape secara tidak sengaja.
    const $modalEl      = document.getElementById('modalAbsensiSholat');
    const modalInstance = new bootstrap.Modal($modalEl, {
        backdrop : 'static',
        keyboard : false,
    });

    let modalMode         = 'add';
    let currentKaryawanId = null;

    // ── Helper: range string untuk hari ini ────────────────────────────────
    function todayRange() {
        const t = moment().format('DD-MM-YYYY');
        return t + ' : ' + t;
    }

    // ── DateRangePicker ────────────────────────────────────────────────────
    $('#date_range').daterangepicker({
        autoUpdateInput : false,
        timePicker      : false,
        locale          : { format: 'DD-MM-YYYY', cancelLabel: 'Bersihkan' }
    });

    $('#date_range')
        .val(todayRange())                              // default hari ini
        .on('apply.daterangepicker', function (ev, picker) {
            $(this).val(
                picker.startDate.format('DD-MM-YYYY') + ' : ' +
                picker.endDate.format('DD-MM-YYYY')
            );
            table.ajax.reload();
        })
        .on('cancel.daterangepicker', function () {
            $(this).val('');
            table.ajax.reload();
        });

    // ── DataTables ─────────────────────────────────────────────────────────
    const table = $('#table_absensi_sholat').DataTable({
        ordering   : true,
        paging     : true,
        serverSide : true,
        processing : true,
        responsive : true,
        pageLength : 100,
        lengthMenu : [
            [10,25,50,100,250,500,1000,-1],
            [10,25,50,100,250,500,1000, 'Semua']
        ],
        dom        : 'Blfrtip',
        ajax: {
            url  : "{{ route('dashboard.rekap.sholat.index') }}",
            data : function (d) { d.date = $('#date_range').val(); }
        },
        buttons: [
            {
                extend        : 'pdfHtml5',
                text          : '<i class="fas fa-file-pdf"></i> PDF',
                className     : 'buttons-pdf',
                title         : 'Rekap Absensi Sholat',
                orientation   : 'landscape',
                pageSize      : 'A4',
                exportOptions : { 
                    columns: [0, 1, 2, 3, 4],
                    format: {
                        body: function (data, row, column, node) {
                            if (column === 3 || column === 4) {
                                const text = $(node).text().trim();
                                if (text.toLowerCase().includes('izin')) return 'Izin';
                                const timeMatch = text.match(/\d{2}:\d{2}/);
                                return timeMatch ? timeMatch[0] : '-';
                            }
                            return data;
                        }
                    }
                },
                customize     : function (doc) {
                    doc.content[1].table.widths    = ['5%', '15%', '40%', '20%', '20%'];
                    doc.styles.tableHeader.alignment   = 'center';
                    doc.styles.tableBodyEven.alignment = 'center';
                    doc.styles.tableBodyOdd.alignment  = 'center';
                    doc.content[0].alignment           = 'center';
                    
                    // Align Nama Karyawan left
                    doc.content[1].table.body.forEach(function(row, i) {
                        if (i > 0) row[2].alignment = 'left';
                    });
                }
            },
            {
                extend        : 'excelHtml5',
                text          : '<i class="fas fa-file-excel"></i> Excel',
                className     : 'buttons-excel',
                title         : 'Rekap Absensi Sholat',
                exportOptions : { 
                    columns: [0, 1, 2, 3, 4],
                    format: {
                        body: function (data, row, column, node) {
                            if (column === 3 || column === 4) {
                                const text = $(node).text().trim();
                                if (text.toLowerCase().includes('izin')) return 'Izin';
                                const timeMatch = text.match(/\d{2}:\d{2}/);
                                return timeMatch ? timeMatch[0] : '-';
                            }
                            return data;
                        }
                    }
                }
            },
            {
                extend        : 'print',
                text          : '<i class="fas fa-print"></i> Print',
                className     : 'buttons-print',
                title         : '',
                exportOptions : { 
                    columns: [0, 1, 2, 3, 4],
                    format: {
                        body: function (data, row, column, node) {
                            if (column === 3 || column === 4) {
                                const text = $(node).text().trim();
                                if (text.toLowerCase().includes('izin')) return 'Izin';
                                const timeMatch = text.match(/\d{2}:\d{2}/);
                                return timeMatch ? timeMatch[0] : '-';
                            }
                            return data;
                        }
                    }
                },
                customize     : function (win) {
                    $(win.document.body)
                        .css('font-size', '10pt')
                        .find('table').addClass('compact')
                        .css({ 'font-size': 'inherit', margin: '20px auto', width: '100%' });
                    $(win.document.body).prepend(
                        '<h3 style="text-align:center;margin-bottom:20px;">Rekap Absensi Sholat</h3>'
                    );

                    // Align Nama Karyawan left for print
                    $(win.document.body).find('table tbody tr').each(function() {
                        $(this).find('td').eq(2).css('text-align', 'left');
                    });
                }
            }
        ],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'tanggal_display', name: 'tanggal' },
            { data: 'karyawan',        name: 'karyawan' },
            { data: 'duha',   name: 'duha',   className: 'text-center', orderable: false, searchable: false },
            { data: 'dzuhur', name: 'dzuhur', className: 'text-center', orderable: false, searchable: false },
            { data: 'aksi',   name: 'aksi',   className: 'text-center', orderable: false, searchable: false }
        ]
    });

    // ── Filter & Reset ─────────────────────────────────────────────────────
    $('#btn_filter').on('click', function () { table.ajax.reload(); });

    $('#btn_reset').on('click', function () {
        $('#date_range').val(todayRange());
        table.ajax.reload();
    });

    // ── Toggle Jam Sholat ──────────────────────────────────────────────────
    $('#edit_jenis').on('change', function () {
        const isIzin = $(this).val() === 'izin';
        $('#wrap_jam_sholat').toggle(!isIzin);
        if (isIzin) $('#edit_jam').val('');
    });

    // ── Tombol TAMBAH ──────────────────────────────────────────────────────
    $(document).on('click', '.btn-add', function () {
        const $btn = $(this);
        modalMode         = 'add';
        currentKaryawanId = $btn.data('karyawan-id');

        resetModal();
        $('#modalAbsensiSholatLabel').text('Tambah Data Absensi Sholat');
        $('#edit_karyawan_id').val(currentKaryawanId);
        $('#edit_nama').val($btn.data('karyawan-nama'));
        $('#edit_tanggal').val($btn.data('tanggal'));

        // Pre-fill & kunci jenis sesuai tombol yang diklik
        $('#edit_jenis').val($btn.data('jenis')).trigger('change').prop('disabled', true);

        modalInstance.show();
    });

    // ── Tombol EDIT ────────────────────────────────────────────────────────
    $(document).on('click', '.btn-edit', function () {
        modalMode         = 'edit';
        currentKaryawanId = null;

        resetModal();
        $('#modalAbsensiSholatLabel').text('Edit Data Absensi Sholat');

        $.ajax({
            url     : "{{ route('dashboard.rekap.sholat.show', '') }}/" + $(this).data('id'),
            type    : 'GET',
            success : function (res) {
                if (!res.success) {
                    return Swal.fire('Error', res.message || 'Gagal memuat data', 'error');
                }

                const d      = res.data;
                const parts  = d.tanggal.split('-'); // d-m-Y → Y-m-d

                $('#edit_id').val(d.id);
                $('#edit_nama').val(d.karyawan);
                $('#edit_tanggal').val(`${parts[2]}-${parts[1]}-${parts[0]}`);
                $('#edit_jenis').prop('disabled', false).val(d.jenis_sholat).trigger('change');
                $('#edit_jam').val(d.jam_sholat || '');

                modalInstance.show();
            },
            error: function () {
                Swal.fire('Error', 'Gagal memuat data absensi', 'error');
            }
        });
    });

    // ── Submit Form ────────────────────────────────────────────────────────
    $('#formAbsensiSholat').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        const jenisSholat = $('#edit_jenis').val();
        const jamRaw      = $('#edit_jam').val();
        const jamValue    = jamRaw ? jamRaw + ':00' : null;

        const payload = {
            tanggal      : $('#edit_tanggal').val(),
            jenis_sholat : jenisSholat,
            jam_sholat   : jamValue,
        };

        if (modalMode === 'edit') {
            $.ajax({
                url        : "{{ route('dashboard.rekap.sholat.update', '') }}/" + $('#edit_id').val(),
                type       : 'PUT',
                data       : payload,
                beforeSend : lockBtn,
                success    : handleSuccess,
                error      : handleError,
            });
        } else {
            $.ajax({
                url        : "{{ route('dashboard.rekap.sholat.store') }}",
                type       : 'POST',
                data       : { ...payload, karyawan_id: currentKaryawanId },
                beforeSend : lockBtn,
                success    : handleSuccess,
                error      : handleError,
            });
        }
    });

    // ── Tombol HAPUS ───────────────────────────────────────────────────────
    $(document).on('click', '.btn-delete', function () {
        const url = "{{ route('dashboard.rekap.sholat.destroy', '') }}/" + $(this).data('id');

        Swal.fire({
            title             : 'Anda yakin?',
            text              : 'Data yang sudah dihapus tidak dapat dikembalikan!',
            icon              : 'warning',
            showCancelButton  : true,
            confirmButtonText : 'Ya, Hapus!',
            cancelButtonText  : 'Batal',
            reverseButtons    : true,
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url     : url,
                type    : 'DELETE',
                success : function (res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 2000, showConfirmButton: false });
                        table.ajax.reload();
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
                }
            });
        });
    });

    // ── Helper Functions ───────────────────────────────────────────────────
    function lockBtn() {
        $('#btn_save').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    }

    function unlockBtn() {
        $('#btn_save').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan');
    }

    // FIX: Modal baru ditutup SETELAH Swal success selesai (via .then()),
    // bukan bersamaan — mencegah race condition antara dua overlay.
    function handleSuccess(res) {
        unlockBtn();
        if (res.success) {
            // 1. Tutup modal dulu
            const modalEl = $modalEl;
            const instance = bootstrap.Modal.getInstance(modalEl);

            // 2. Setelah modal benar-benar hilang (animasi selesai), baru tampilkan Swal
            $(modalEl).one('hidden.bs.modal', function () {
                Swal.fire({
                    icon              : 'success',
                    title             : 'Berhasil',
                    text              : res.message,
                    timer             : 2000,
                    showConfirmButton : false,
                }).then(function () {
                    table.ajax.reload();
                });
            });

            instance?.hide();
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    }

    // FIX: Swal ditampilkan dengan z-index di atas modal Bootstrap (1055)
    // menggunakan customClass + didOpen, sehingga modal tidak ikut tertutup
    // saat overlay Swal muncul.
    function handleError(xhr) {
        unlockBtn();

        if (xhr.status === 422) {
            const errors = xhr.responseJSON?.errors || {};
            displayErrors(errors);

            const msgs = Object.values(errors).flat().join('<br>');
            if (msgs) {
                Swal.fire({
                    icon        : 'error',
                    title       : 'Validasi Gagal',
                    html        : msgs,
                    customClass : { container: 'swal-on-modal' },
                    didOpen     : function () {
                        const container = document.querySelector('.swal-on-modal');
                        if (container) container.style.zIndex = 99999;
                    },
                });
            }
        } else {
            Swal.fire({
                icon        : 'error',
                title       : 'Error',
                text        : xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data',
                customClass : { container: 'swal-on-modal' },
                didOpen     : function () {
                    const container = document.querySelector('.swal-on-modal');
                    if (container) container.style.zIndex = 99999;
                },
            });
        }
    }

    function displayErrors(errors) {
        $.each(errors, function (field, messages) {
            $('#error_' + field).text(messages[0]);
            const inputId = field === 'jenis_sholat' ? '#edit_jenis' : '#edit_' + field;
            $(inputId).addClass('is-invalid');
        });
    }

    function clearErrors() {
        $('#formAbsensiSholat .invalid-feedback').text('');
        $('#formAbsensiSholat input, #formAbsensiSholat select').removeClass('is-invalid');
    }

    function resetModal() {
        clearErrors();
        $('#edit_id, #edit_karyawan_id, #edit_nama, #edit_tanggal, #edit_jam').val('');
        $('#edit_jenis').prop('disabled', false).val('duha').trigger('change');
        $('#wrap_jam_sholat').show();
        unlockBtn();
    }
});
</script>
@endpush