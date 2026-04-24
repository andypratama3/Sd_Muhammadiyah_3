@extends('layouts.dashboard')

@section('title', 'Rekap Absensi Sholat')

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
    <style>
        .filter-section {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .filter-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dt-buttons .dt-button {
            border: 0;
            border-radius: 0.375rem;
            color: #fff !important;
            padding: 0.45rem 0.75rem;
            font-size: 0.875rem;
            margin-right: 0.4rem;
        }

        .dt-button.buttons-pdf   { background: #dc3545 !important; }
        .dt-button.buttons-excel { background: #198754 !important; }
        .dt-button.buttons-print { background: #0d6efd !important; }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .is-invalid { border-color: #dc3545 !important; }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-body">

            {{-- ── Filter ── --}}
            <div class="filter-section">
                <div class="filter-title">
                    <i class="fas fa-filter"></i>
                    Pengaturan Filter
                </div>

                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="date_range" class="mb-2">
                            <i class="fas fa-calendar-alt"></i> Periode Tanggal
                        </label>
                        <input type="text" id="date_range" name="date" class="form-control"
                            placeholder="Pilih rentang tanggal (dd-mm-yyyy : dd-mm-yyyy)" autocomplete="off">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <div class="gap-2 d-flex w-100">
                            <button type="button" id="btn_filter" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            <button type="button" id="btn_reset" class="btn btn-secondary w-100">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Tabel ── --}}
            <div class="mt-3 table-responsive">
                <table class="table table-bordered table-striped" id="table_absensi_sholat">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Karyawan</th>
                            <th>Tanggal</th>
                            <th>Dhuha</th>
                            <th>Dzuhur</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Modal Edit / Tambah ── --}}
    <div class="modal fade" id="modalEditAbsensiSholat" tabindex="-1"
         aria-labelledby="modalEditAbsensiSholatLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditAbsensiSholatLabel">
                        Edit Data Absensi Sholat
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formEditAbsensiSholat" method="POST">
                    @csrf
                    <div class="modal-body">

                        {{-- Hidden fields --}}
                        <input type="hidden" id="edit_id"          name="id">
                        <input type="hidden" id="edit_karyawan_id" name="karyawan_id">

                        {{-- Nama Karyawan --}}
                        <div class="mb-3 form-group">
                            <label for="edit_nama" class="form-label">
                                <i class="fas fa-user"></i> Nama Karyawan
                            </label>
                            <input type="text" class="form-control" id="edit_nama" disabled>
                            <small class="text-muted">Field ini tidak dapat diubah</small>
                        </div>

                        {{-- Tanggal --}}
                        <div class="mb-3 form-group">
                            <label for="edit_tanggal" class="form-label">
                                <i class="fas fa-calendar"></i> Tanggal
                            </label>
                            <input type="date" class="form-control" id="edit_tanggal"
                                   name="tanggal" required>
                            <div class="invalid-feedback" id="error_tanggal"></div>
                        </div>

                        {{-- Jenis Sholat --}}
                        <div class="mb-3 form-group">
                            <label for="edit_jenis" class="form-label">
                                <i class="fas fa-pray"></i> Jenis Sholat
                            </label>
                            <select class="form-select" id="edit_jenis"
                                    name="jenis_sholat" required>
                                <option value="duha">Dhuha</option>
                                <option value="dzuhur">Dzuhur</option>
                                <option value="izin">Izin</option>
                            </select>
                            <div class="invalid-feedback" id="error_jenis_sholat"></div>
                        </div>

                        {{-- Jam Sholat --}}
                        <div class="mb-3 form-group">
                            <label for="edit_jam" class="form-label">
                                <i class="fas fa-clock"></i> Jam Sholat
                            </label>
                            <input type="time" class="form-control" id="edit_jam"
                                   name="jam_sholat" step="60">
                            <small class="text-muted">Kosongkan jika Izin</small>
                            <div class="invalid-feedback" id="error_jam_sholat"></div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn_save">
                            <i class="fas fa-save"></i> Simpan Perubahan
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

        // ── State modal ──────────────────────────────────────────────────────
        // 'edit'  → update record yang sudah ada (PUT /{id})
        // 'add'   → buat record baru (POST /)
        let modalMode        = 'edit';
        let currentKaryawanId = null;

        // ── DateRangePicker ──────────────────────────────────────────────────
        $('input[name="date"]').daterangepicker({
            autoUpdateInput: false,
            timePicker: false,
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Bersihkan'
            }
        });

        $('input[name="date"]').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(
                picker.startDate.format('DD-MM-YYYY') + ' : ' +
                picker.endDate.format('DD-MM-YYYY')
            );
            table.ajax.reload();
        });

        $('input[name="date"]').on('cancel.daterangepicker', function () {
            $(this).val('');
            table.ajax.reload();
        });

        // ── DataTables ───────────────────────────────────────────────────────
        const table = $('#table_absensi_sholat').DataTable({
            ordering   : true,
            paging     : true,
            serverSide : true,
            processing : true,
            responsive : true,
            lengthMenu : [    
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'Semua']
            ],
            dom : 'Blfrtip', 
            ajax: {
                url : "{{ route('dashboard.rekap.sholat.index') }}",
                data: function (d) {
                    d.date = $('#date_range').val();
                }
            },
            buttons: [
                {
                    extend       : 'pdfHtml5',
                    text         : '<i class="fas fa-file-pdf"></i> PDF',
                    className    : 'buttons-pdf',
                    title        : 'Rekap Absensi Sholat',
                    orientation  : 'portrait',
                    pageSize     : 'A4',
                    exportOptions: { columns: [0, 1, 2, 3, 4] },
                    customize    : function (doc) {
                        doc.content[1].table.widths = ['10%','35%','25%','15%','15%'];
                        doc.content[1].alignment = 'center';
                        doc.styles.tableHeader.alignment    = 'center';
                        doc.styles.tableBodyEven.alignment = 'center';
                        doc.styles.tableBodyOdd.alignment  = 'center';
                        doc.content[0].alignment = 'center';
                    }
                },
                {
                    extend       : 'excelHtml5',
                    text         : '<i class="fas fa-file-excel"></i> Excel',
                    className    : 'buttons-excel',
                    title        : 'Rekap Absensi Sholat',
                    exportOptions: { columns: [0, 1, 2, 3, 4] }
                },
                {
                    extend       : 'print',
                    text         : '<i class="fas fa-print"></i> Print',
                    className    : 'buttons-print',
                    title        : '',
                    exportOptions: { columns: [0, 1, 2, 3, 4] },
                    customize    : function (win) {
                        $(win.document.body).css('font-size', '10pt');
                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css({ 'font-size': 'inherit', 'margin': '20px auto', 'width': '100%' });
                        $(win.document.body).prepend(
                            '<h3 style="text-align:center;margin-bottom:20px;">Rekap Absensi Sholat</h3>'
                        );
                    }
                }
            ],
            columns: [
                { data: 'DT_RowIndex',      name: 'DT_RowIndex',   orderable: false, searchable: false, className: 'text-center' },
                { data: 'karyawan',          name: 'karyawan' },
                { data: 'tanggal_display',   name: 'tanggal' },
                { data: 'duha',              name: 'duha',   className: 'text-center' },
                { data: 'dzuhur',            name: 'dzuhur', className: 'text-center' },
                { data: 'aksi',              name: 'aksi',   orderable: false, searchable: false, className: 'text-center' }
            ]
        });

        // ── Tombol Filter / Reset ────────────────────────────────────────────
        $('#btn_filter').on('click', function () { table.ajax.reload(); });
        $('#btn_reset').on('click', function () {
            $('#date_range').val('');
            table.ajax.reload();
        });

        // ── Tombol TAMBAH (baris no data) ────────────────────────────────────
        $(document).on('click', '.btn-add', function () {
            modalMode         = 'add';
            currentKaryawanId = $(this).data('karyawan-id');

            resetModal();
            $('#modalEditAbsensiSholatLabel').text('Tambah Data Absensi Sholat');
            $('#edit_id').val('');
            $('#edit_karyawan_id').val(currentKaryawanId);
            $('#edit_nama').val($(this).data('karyawan-nama'));

            // Default tanggal = hari ini
            const today = new Date().toISOString().split('T')[0];
            $('#edit_tanggal').val(today);

            new bootstrap.Modal(document.getElementById('modalEditAbsensiSholat')).show();
        });

        // ── Tombol EDIT (baris yang sudah ada data) ──────────────────────────
        $(document).on('click', '.btn-edit', function () {
            modalMode         = 'edit';
            currentKaryawanId = null;

            resetModal();
            $('#modalEditAbsensiSholatLabel').text('Edit Data Absensi Sholat');

            const id = $(this).data('id');

            $.ajax({
                url     : "{{ route('dashboard.rekap.sholat.show', '') }}" + '/' + id,
                type    : 'GET',
                success : function (res) {
                    if (res.success) {
                        const d = res.data;

                        $('#edit_id').val(d.id);
                        $('#edit_nama').val(d.karyawan);

                        // Konversi d-m-Y → Y-m-d untuk input[type=date]
                        const parts = d.tanggal.split('-');
                        $('#edit_tanggal').val(`${parts[2]}-${parts[1]}-${parts[0]}`);

                        $('#edit_jenis').val(d.jenis_sholat);
                        $('#edit_jam').val(d.jam_sholat || '');

                        new bootstrap.Modal(
                            document.getElementById('modalEditAbsensiSholat')
                        ).show();
                    } else {
                        Swal.fire('Error', res.message || 'Gagal memuat data', 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error', 'Gagal memuat data absensi', 'error');
                }
            });
        });

        // ── Submit Form (Edit & Tambah) ───────────────────────────────────────
        $('#formEditAbsensiSholat').on('submit', function (e) {
            e.preventDefault();
            clearErrors();

            const jamRaw = $('#edit_jam').val();
            // Append ':00' agar backend menerima format H:i:s
            const jamSholat = jamRaw ? jamRaw + ':00' : null;

            if (modalMode === 'edit') {
                // ── Mode Edit: PUT /rekap-sholat/{id} ──
                const id = $('#edit_id').val();

                $.ajax({
                    url     : "{{ route('dashboard.rekap.sholat.update', '') }}" + '/' + id,
                    type    : 'PUT',
                    data    : {
                        _token       : '{{ csrf_token() }}',
                        tanggal      : $('#edit_tanggal').val(),
                        jenis_sholat : $('#edit_jenis').val(),
                        jam_sholat   : jamSholat,
                    },
                    beforeSend: function () { lockBtn(); },
                    success  : function (res) { handleSuccess(res); },
                    error    : function (xhr) { handleError(xhr); }
                });

            } else {
                // ── Mode Tambah: POST /rekap-sholat ──
                $.ajax({
                    url     : "{{ route('dashboard.rekap.sholat.store') }}",
                    type    : 'POST',
                    data    : {
                        _token       : '{{ csrf_token() }}',
                        karyawan_id  : currentKaryawanId,
                        tanggal      : $('#edit_tanggal').val(),
                        jenis_sholat : $('#edit_jenis').val(),
                        jam_sholat   : jamSholat,
                    },
                    beforeSend: function () { lockBtn(); },
                    success  : function (res) { handleSuccess(res); },
                    error    : function (xhr) { handleError(xhr); }
                });
            }
        });

        // ── Tombol HAPUS ─────────────────────────────────────────────────────
        $(document).on('click', '.btn-delete', function () {
            const id  = $(this).data('id');
            const url = "{{ route('dashboard.rekap.sholat.destroy', '') }}" + '/' + id;

            Swal.fire({
                title              : 'Anda yakin?',
                text               : 'Data yang sudah dihapus tidak dapat dikembalikan!',
                icon               : 'warning',
                showCancelButton   : true,
                confirmButtonText  : 'Ya, Hapus!',
                cancelButtonText   : 'Batal',
                reverseButtons     : true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url    : url,
                        type   : 'DELETE',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function (res) {
                            if (res.success) {
                                Swal.fire({
                                    icon             : 'success',
                                    title            : 'Berhasil',
                                    text             : res.message,
                                    timer            : 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
                        }
                    });
                }
            });
        });

        // ── Helper Functions ─────────────────────────────────────────────────
        function lockBtn() {
            $('#btn_save').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        }

        function unlockBtn() {
            $('#btn_save').prop('disabled', false)
                .html('<i class="fas fa-save"></i> Simpan Perubahan');
        }

        function handleSuccess(res) {
            unlockBtn();
            if (res.success) {
                Swal.fire({
                    icon             : 'success',
                    title            : 'Berhasil',
                    text             : res.message,
                    timer            : 2000,
                    showConfirmButton : false
                });
                bootstrap.Modal.getInstance(
                    document.getElementById('modalEditAbsensiSholat')
                ).hide();
                table.ajax.reload();
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        }

        function handleError(xhr) {
            unlockBtn();
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors || {};
                displayErrors(errors);
                // Tampilkan ringkasan via Swal jika banyak error
                const msgs = Object.values(errors).flat().join('<br>');
                if (msgs) Swal.fire({ icon: 'error', title: 'Validasi Gagal', html: msgs });
            } else {
                Swal.fire('Error',
                    xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data',
                    'error'
                );
            }
        }

        function displayErrors(errors) {
            $.each(errors, function (field, messages) {
                $('#error_' + field).text(messages[0]);
                $('#edit_' + field).addClass('is-invalid');
            });
        }

        function clearErrors() {
            $('#formEditAbsensiSholat').find('.invalid-feedback').text('');
            $('#formEditAbsensiSholat').find('input, select').removeClass('is-invalid');
        }

        function resetModal() {
            clearErrors();
            $('#edit_id').val('');
            $('#edit_karyawan_id').val('');
            $('#edit_nama').val('');
            $('#edit_tanggal').val('');
            $('#edit_jenis').val('duha');
            $('#edit_jam').val('');
            unlockBtn();
        }
    });
    </script>
@endpush