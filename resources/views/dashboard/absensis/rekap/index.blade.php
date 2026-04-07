@extends('layouts.dashboard')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
    <style>
        .filter-section {
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }

        .filter-section .filter-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
         .filter-controls {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: flex-end;
        }

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }

        .form-group label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }
    </style>
@endpush

@section('title', 'Rekap Absensi')

@section('content')
<div class="card">

    <div class="card-body">
        <div class="filter-section">
        <div class="filter-title">
            <i class="fas fa-filter"></i>
            Pengaturan Filter
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="date_range" class="mb-2">
                        <i class="fas fa-calendar-alt"></i> Periode Tanggal
                    </label>
                    <input
                        type="text"
                        id="date_range"
                        name="date"
                        class="form-control"
                        placeholder="Pilih rentang tanggal (dd-mm-yyyy : dd-mm-yyyy)"
                        value="{{ request('date')  }}"
                        autocomplete="off"
                    >
                </div>
                <label for="status_kehadiran" class="mt-2 mb-2">
                    <i class="fas fa-check-circle"></i> Status Kehadiran
                </label>
                <select name="status_kehadiran" id="status_kehadiran" class="form-control">
                    <option value="">Semua</option>
                    <option value="hadir">Hadir</option>
                    <option value="cuti">Cuti</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="alpha">Alpha</option>
                </select>

                <div class="mt-2 btn-group-filter">
                    <button type="button" id="btn_filter" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <button type="button" id="btn_reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>

                    @role('admin|superadmin')
                        <button type="button" id="btn_export_pdf" class="btn btn-danger" title="Download laporan dalam format PDF">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>

                        <button type="button" id="btn_export_excel" class="btn btn-success" title="Download laporan dalam format Excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    @endrole
                </div>

                <div class="mt-2 col-md-12">

                </div>
            </div>
        </div>
        <div class="mt-4 table-responsive">
            <table class="table table-bordered table-striped" id="table_absensi">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Jenis Pegawai</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>RP Masuk</th>
                        <th>RP Pulang</th>
                        <th>Keterangan</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Absensi -->
<div class="modal fade" id="modalEditAbsensi" tabindex="-1" aria-labelledby="modalEditAbsensiLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditAbsensiLabel">Edit Data Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditAbsensi" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" id="absensi_id" name="id">

                    <div class="mt-2 mb-3 form-group">
                        <label for="edit_nama" class="form-label">
                            <i class="fas fa-user"></i> Nama Karyawan
                        </label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" disabled>
                        <small class="text-muted">Field ini tidak dapat diubah</small>
                    </div>

                    <div class="mt-2 mb-3 form-group">
                        <label for="edit_tanggal" class="form-label">
                            <i class="fas fa-calendar"></i> Tanggal
                        </label>
                        <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
                        <div class="invalid-feedback" id="error_tanggal"></div>
                    </div>

                    <div class="mt-2 mb-3 form-group">
                        <label for="edit_status" class="form-label">
                            <i class="fas fa-check-circle"></i> Status Kehadiran
                        </label>
                        <select class="form-select" id="edit_status" name="status_kehadiran" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="hadir">Hadir</option>
                            <option value="cuti">Cuti</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpha">Alpha</option>
                        </select>
                        <div class="invalid-feedback" id="error_status"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="edit_jam_masuk" class="form-label">
                            <i class="fas fa-sign-in-alt"></i> Jam Masuk
                        </label>
                        <input type="time" class="form-control" id="edit_jam_masuk" name="jam_masuk" step="60">
                        <div class="invalid-feedback" id="error_jam_masuk"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="edit_jam_pulang" class="form-label">
                            <i class="fas fa-sign-out-alt"></i> Jam Pulang
                        </label>
                        <input type="time" class="form-control" id="edit_jam_pulang" name="jam_pulang" step="60"/>
                        <div class="invalid-feedback" id="error_jam_pulang"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="edit_rp_masuk" class="form-label">
                            <i class="fas fa-money-bill"></i> Rp Masuk
                        </label>
                        <input type="number" class="form-control" id="edit_rp_masuk" name="rp_masuk" step="1000" min="0"/>
                        <div class="invalid-feedback" id="error_rp_masuk"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="edit_rp_pulang" class="form-label">
                            <i class="fas fa-money-bill"></i> Rp Pulang
                        </label>
                        <input type="number" class="form-control" id="edit_rp_pulang" name="rp_pulang" step="1000" min="0"/>
                        <div class="invalid-feedback" id="error_rp_pulang"></div>
                    </div>

                    <div class="mb-3 form-group">
                        <label for="edit_keterangan" class="form-label">
                            <i class="fas fa-sticky-note"></i> Keterangan
                        </label>
                        <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan (opsional)"></textarea>
                        <div class="invalid-feedback" id="error_keterangan"></div>
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

@push('js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js" defer></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function () {

            /**
             * FIX: Strip seconds from time string (HH:MM:SS → HH:MM)
             * <input type="time"> only accepts HH:MM format,
             * but the DB stores HH:MM:SS which causes the field to be blank.
             */
            function formatTime(time) {
                if (!time) return '';
                // Take only first 5 characters: "08:30:00" → "08:30"
                return String(time).substring(0, 5);
            }

            $('input[name="date"]').daterangepicker({
                timePicker: false,
                autoUpdateInput: false,
                locale: {
                    format: 'DD-MM-YYYY',
                }
            });

            $('input[name="date"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' : ' + picker.endDate.format('DD-MM-YYYY'));
            });

            $('input[name="date"]').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Initialize DataTable
            let table = $('#table_absensi').DataTable({
                ordering: true,
                pagination: true,
                deferRender: true,
                serverSide: true,
                responsive: true,
                processing: true,
                pageLength: 100,
                ajax: {
                    url: "{{ route('dashboard.rekap.absensi.index') }}",
                    data: function(d) {
                        d.date = $('#date_range').val();
                        d.status_kehadiran = $('#status_kehadiran').val();
                    }
                },
                autoWidth: false,
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'karyawan', name: 'karyawan.name' },
                    { data: 'jenis_pegawai', name: 'jenis_pegawai' },
                    { data: 'tanggal', name: 'tanggal' },
                    { data: 'status', orderable: false, searchable: false },
                    { data: 'jam_masuk', name: 'jam_masuk' },
                    { data: 'jam_pulang', name: 'jam_pulang' },
                    { data: 'rp_masuk', name: 'rp_masuk' },
                    { data: 'rp_pulang', name: 'rp_pulang' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'aksi', orderable: false, searchable: false },
                ]
            });

            table.on('page.dt', function() {
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            });

            $('#btn_filter').click(function() {
                table.ajax.reload();
            });

            $('#btn_reset').click(function() {
                $('#date_range').val('');
                $('#status_kehadiran').val('');
                table.ajax.reload();
            });

            $('#status_kehadiran').change(function() {
                table.ajax.reload();
            });

            $('#btn_export_pdf').click(function() {
                const dateRange = $('#date_range').val();
                let url = "{{ route('dashboard.rekap.absensi.export.pdf') }}";
                if (dateRange) {
                    url += '?date=' + encodeURIComponent(dateRange);
                }
                window.location.href = url;
            });

            $('#btn_export_excel').click(function() {
                const dateRange = $('#date_range').val();
                let url = "{{ route('dashboard.rekap.absensi.export.excel') }}";
                if (dateRange) {
                    url += '?date=' + encodeURIComponent(dateRange);
                }
                window.location.href = url;
            });

            $('#date_range').on('apply.daterangepicker', function () {
                table.ajax.reload();
            });

            // Handle Edit Button
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                loadAbsensiData(id);
            });

            $('#table_absensi').on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                var url = "{{ route('dashboard.rekap.absensi.destroy', ':id') }}";
                url = url.replace(':id', id);

                Swal.fire({
                    title: 'Anda yakin?',
                    text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus Data',
                    cancelButtonText: 'Tidak, Batalkan!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function(data) {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        title: 'Berhasil',
                                        text: data.message,
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Error', data.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Dibatalkan', 'Data tidak jadi dihapus', 'info');
                    }
                });
            });

            // Load data untuk edit
            function loadAbsensiData(id) {
                $.ajax({
                    url: "{{ route('dashboard.rekap.absensi.show', '') }}" + '/' + id,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const absensi = response.absensi;

                        $('#absensi_id').val(absensi.id);
                        $('#edit_nama').val(absensi.karyawan.name);
                        $('#edit_tanggal').val(absensi.tanggal);
                        $('#edit_status').val(absensi.status_kehadiran);

                        // FIX: Use formatTime() to strip seconds (HH:MM:SS → HH:MM)
                        // so <input type="time"> correctly displays the value
                        $('#edit_jam_masuk').val(formatTime(absensi.jam_masuk));
                        $('#edit_jam_pulang').val(formatTime(absensi.jam_pulang));

                        $('#edit_rp_masuk').val(absensi.rp_masuk || '');
                        $('#edit_rp_pulang').val(absensi.rp_pulang || '');
                        $('#edit_keterangan').val(absensi.keterangan || '');

                        // Reset validation state
                        clearErrors();

                        // Update form action
                        $('#formEditAbsensi').attr('action', "{{ route('dashboard.rekap.absensi.update', '') }}" + '/' + id);

                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('modalEditAbsensi'));
                        modal.show();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memuat data absensi'
                        });
                    }
                });
            }

            // Submit form edit
            $('#formEditAbsensi').on('submit', function(e) {
                e.preventDefault();
                clearErrors();

                const id = $('#absensi_id').val();

                // FIX: Get the HH:MM value from the input and append ":00" for seconds
                // so the backend receives a valid H:i:s or H:i format
                const jamMasukRaw   = $('#edit_jam_masuk').val();
                const jamPulangRaw  = $('#edit_jam_pulang').val();

                const formData = {
                    tanggal           : $('#edit_tanggal').val(),
                    status_kehadiran  : $('#edit_status').val(),
                    jam_masuk         : jamMasukRaw  ? jamMasukRaw  + ':00' : null,
                    jam_pulang        : jamPulangRaw ? jamPulangRaw + ':00' : null,
                    rp_masuk          : $('#edit_rp_masuk').val()  || null,
                    rp_pulang         : $('#edit_rp_pulang').val() || null,
                    keterangan        : $('#edit_keterangan').val() || null,
                    _token            : '{{ csrf_token() }}'
                };

                $.ajax({
                    url: "{{ route('dashboard.rekap.absensi.update', '') }}" + '/' + id,
                    type: 'PUT',
                    data: formData,
                    dataType: 'json',
                    beforeSend: function() {
                        $('#btn_save').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
                    },
                    success: function(response) {
                        $('#btn_save').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message || 'Data absensi berhasil diperbarui',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        bootstrap.Modal.getInstance(document.getElementById('modalEditAbsensi')).hide();
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        $('#btn_save').prop('disabled', false).html('<i class="fas fa-save"></i> Simpan Perubahan');

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            displayErrors(errors);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data'
                            });
                        }
                    }
                });
            });

            function displayErrors(errors) {
                $.each(errors, function(field, messages) {
                    const errorElement = $('#error_' + field);
                    const inputElement = $('#edit_' + field);

                    if (errorElement.length) {
                        errorElement.text(messages[0]);
                        inputElement.addClass('is-invalid');
                    }
                });
            }

            function clearErrors() {
                $('#formEditAbsensi').find('.invalid-feedback').text('');
                $('#formEditAbsensi').find('input, select, textarea').removeClass('is-invalid');
            }
        });
    </script>
@endpush
@endsection
