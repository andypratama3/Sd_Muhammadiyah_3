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

        .dt-button.buttons-pdf {
            background: #dc3545 !important;
        }

        .dt-button.buttons-excel {
            background: #198754 !important;
        }

        .dt-button.buttons-print {
            background: #0d6efd !important;
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
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

            <div class="mt-3 table-responsive">
                <table class="table table-bordered table-striped" id="table_absensi_sholat">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Karyawan</th>
                            <th>Tanggal</th>
                            <th>Dhuha</th>
                            <th>Dzuhur</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                </table>
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

    {{-- Modal Detail --}}
    <div class="modal fade" id="modalDetailAbsensi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="card-header bg-primary text-white">
                    <h5 class="modal-title m-0">
                        <i class="fas fa-info-circle"></i> Detail Absensi Sholat
                    </h5>
                </div>
                <div class="modal-body" id="detail-content">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Memuat data...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Absensi Sholat -->
    <div class="modal fade" id="modalEditAbsensiSholat" tabindex="-1" aria-labelledby="modalEditAbsensiSholatLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditAbsensiSholatLabel">Edit Data Absensi Sholat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditAbsensiSholat" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">

                        <div class="mb-3 form-group">
                            <label for="edit_nama" class="form-label">
                                <i class="fas fa-user"></i> Nama Karyawan
                            </label>
                            <input type="text" class="form-control" id="edit_nama" disabled>
                        </div>

                        <div class="mb-3 form-group">
                            <label for="edit_tanggal" class="form-label">
                                <i class="fas fa-calendar"></i> Tanggal
                            </label>
                            <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
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
                        </div>

                        <div class="mb-3 form-group">
                            <label for="edit_jam" class="form-label">
                                <i class="fas fa-clock"></i> Jam Sholat
                            </label>
                            <input type="time" class="form-control" id="edit_jam" name="jam_sholat" step="60">
                            <small class="text-muted">Kosongkan jika Izin</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn_save">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const tableSelector = '#table_absensi_sholat';

            // Inisialisasi DateRangePicker
            $('input[name="date"]').daterangepicker({
                autoUpdateInput: false,
                timePicker: false,
                locale: {
                    format: 'DD-MM-YYYY',
                    cancelLabel: 'Bersihkan'
                }
            });

            $('input[name="date"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(
                    picker.startDate.format('DD-MM-YYYY') + ' : ' + picker.endDate.format('DD-MM-YYYY')
                );
                table.ajax.reload();
            });

            $('input[name="date"]').on('cancel.daterangepicker', function() {
                $(this).val('');
                table.ajax.reload();
            });

            // Inisialisasi DataTables
            const table = $(tableSelector).DataTable({
                ordering: true,
                paging: true,
                serverSide: true,
                processing: true,
                responsive: true,
                pageLength: 50,
                dom: 'Bfrtip',
                ajax: {
                    url: "{{ route('dashboard.rekap.sholat.index') }}",
                    data: function(d) {
                        d.date = $('#date_range').val();
                    }
                },
                buttons: [{
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'buttons-pdf',
                        title: 'Rekap Absensi Sholat',
                        orientation: 'portrait',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4],
                        },
                        customize: function(doc) {
                            doc.content[1].table.widths = ['10%', '35%', '25%', '20%', '15%'];
                            doc.content[1].alignment = 'center';
                            doc.styles.tableHeader.alignment = 'center';
                            doc.styles.tableBodyEven.alignment = 'center';
                            doc.styles.tableBodyOdd.alignment = 'center';
                            doc.content[0].alignment = 'center';
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'buttons-excel',
                        title: 'Rekap Absensi Sholat',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4],
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'buttons-print',
                        title: '',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4],
                        },
                        customize: function(win) {
                            $(win.document.body)
                                .css('font-size', '10pt')
                                .css('text-align', 'center');
                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css('font-size', 'inherit')
                                .css('margin', '20px auto')
                                .css('width', '100%');
                            $(win.document.body).prepend(
                                '<h3 style="text-align:center; margin-bottom:20px;">Rekap Absensi Sholat</h3>'
                            );
                        }
                    }
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'karyawan',
                        name: 'karyawan'
                    },
                    {
                        data: 'tanggal_display',
                        name: 'tanggal'
                    },
                    {
                        data: 'duha',
                        name: 'duha',
                        className: 'text-center'
                    },
                    {
                        data: 'dzuhur',
                        name: 'dzuhur',
                        className: 'text-center'
                    },
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            // Tombol Filter
            $('#btn_filter').on('click', function() {
                table.ajax.reload();
            });

            // Tombol Reset
            $('#btn_reset').on('click', function() {
                $('#date_range').val('');
                table.ajax.reload();
            });

            // Aksi: Edit data absensi sholat
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                $.ajax({
                    url: `/dashboard/rekap-sholat/${id}`,
                    type: 'GET',
                    success: function(res) {
                        if (res.success) {
                            const data = res.data;
                            $('#edit_id').val(data.id);
                            $('#edit_nama').val(data.karyawan);

                            // Format tanggal YYYY-MM-DD
                            const parts = data.tanggal.split('-');
                            $('#edit_tanggal').val(`${parts[2]}-${parts[1]}-${parts[0]}`);

                            $('#edit_jenis').val(data.jenis_sholat);
                            $('#edit_jam').val(data.jam_sholat);

                            const modal = new bootstrap.Modal(document.getElementById(
                                'modalEditAbsensiSholat'));
                            modal.show();
                        }
                    }
                });
            });

            // Submit Form Edit
            $('#formEditAbsensiSholat').on('submit', function(e) {
                e.preventDefault();
                const id = $('#edit_id').val();
                const formData = $(this).serialize();

                $.ajax({
                    url: `/dashboard/rekap-sholat/${id}`,
                    type: 'PUT',
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Berhasil', res.message, 'success');
                            bootstrap.Modal.getInstance(document.getElementById(
                                'modalEditAbsensiSholat')).hide();
                            table.ajax.reload();
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan',
                            'error');
                    }
                });
            });

            // Aksi: Hapus data absensi
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Anda yakin?',
                    text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/dashboard/rekap-sholat/${id}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire('Berhasil', res.message, 'success');
                                    table.ajax.reload();
                                } else {
                                    Swal.fire('Gagal', res.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Terjadi kesalahan server.',
                                'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
