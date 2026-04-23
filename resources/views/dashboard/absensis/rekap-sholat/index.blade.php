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
                            <th>Detail Absensi</th>
                            @hasanyrole('admin|superadmin')
                                <th>Aksi</th>
                            @endhasanyrole
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
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="card-header bg-primary text-white">
                    <h5 class="modal-title m-0"><i class="fas fa-info-circle"></i> Detail Absensi Sholat</h5>
                </div>
                <div class="modal-body" id="detail-content">
                    {{-- Content loaded via JS --}}
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

    <script>
        $(document).ready(function() {
            const tableSelector = '#table_absensi_sholat';

            $('input[name="date"]').daterangepicker({
                autoUpdateInput: false,
                timePicker: false,
                locale: {
                    format: 'DD-MM-YYYY',
                    cancelLabel: 'Bersihkan'
                }
            });

            $('input[name="date"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' : ' + picker.endDate.format(
                    'DD-MM-YYYY'));
                table.ajax.reload();
            });

            $('input[name="date"]').on('cancel.daterangepicker', function() {
                $(this).val('');
                table.ajax.reload();
            });

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
                        exportOptions: {
                            columns: [0, 1, 2],
                            stripHtml: true
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'buttons-excel',
                        title: 'Rekap Absensi Sholat',
                        exportOptions: {
                            columns: [0, 1, 2],
                            stripHtml: true
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Print',
                        className: 'buttons-print',
                        exportOptions: {
                            columns: [0, 1, 2],
                            stripHtml: true
                        }
                    }
                ],
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'karyawan',
                        name: 'karyawan'
                    },
                    {
                        data: 'absensi_list',
                        name: 'absensi_list'
                    },
                    @hasanyrole('admin|superadmin')
                    {
                        data: 'aksi',
                        name: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                    @endhasanyrole
                ]
            });

            $('#btn_filter').on('click', function() {
                table.ajax.reload();
            });

            $('#btn_reset').on('click', function() {
                $('#date_range').val('');
                table.ajax.reload();
            });

            // Action: Edit (Placeholder / Detail)
            $(document).on('click', '.btn-edit', function() {
                const id = $(this).data('id');
                // Untuk rekap sholat per karyawan, 'id' adalah ID Karyawan. 
                // Biasanya diarahkan ke riwayat detail karyawan tersebut.
                window.location.href = `/dashboard/absensis?karyawan_id=${id}`;
            });

            // Action: Delete
            $(document).on('click', '.btn-delete', function() {
                const id = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus data absensi sholat karyawan ini? (Tindakan ini mungkin menghapus semua record dalam jangkauan filter)')) {
                    $.ajax({
                        url: `/dashboard/rekap-sholat/${id}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success) {
                                alert(res.message);
                                table.ajax.reload();
                            } else {
                                alert('Gagal: ' + res.message);
                            }
                        },
                        error: function() {
                            alert('Terjadi kesalahan server.');
                        }
                    });
                }
            });
        });
    </script>
@endpush
