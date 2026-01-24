@extends('layouts.dashboard')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
    <style>
        .filter-section {
            padding: 1.5rem;
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

        <div class="filter-controls">
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
                    value="{{ request('date') }}"
                    autocomplete="off"
                >
            </div>

            <div class="btn-group-filter">
                <button type="button" id="btn_filter" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
                <button type="button" id="btn_reset" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Reset
                </button>

                @can('role: admin')
                    <button type="button" id="btn_export_pdf" class="btn btn-danger" title="Download laporan dalam format PDF">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>

                    <button type="button" id="btn_export_excel" class="btn btn-success" title="Download laporan dalam format Excel">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                @endcan
            </div>
        </div>
    </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="table_absensi">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>


@push('js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js" defer></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function () {
             $('input[name="date"]').daterangepicker({
                timePicker: false,
                autoUpdateInput: false,
                locale: {
                    format: 'DD-MM-YYYY',
                }
            });

            // Listen for the apply event to manually set the input value when a date range is selected
            $('input[name="date"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' : ' + picker.endDate.format('DD-MM-YYYY'));
            });

            // Clear the input value on cancel if needed
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
                    }
                },
                autoWidth: false,
                responsive: true,
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'karyawan', name: 'karyawan.name' },
                    { data: 'tanggal', name: 'tanggal' },
                    { data: 'status', orderable: false, searchable: false },
                    { data: 'jam_masuk', name: 'jam_masuk' },
                    { data: 'jam_pulang', name: 'jam_pulang' },
                    { data: 'keterangan', name: 'keterangan' },
                ]
            });

            // Scroll to top when changing DataTable pages
            table.on('page.dt', function() {
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            });

            // Filter button
            $('#btn_filter').click(function() {
                table.ajax.reload();
            });

            // Reset button
            $('#btn_reset').click(function() {
                $('#date_range').val('');
                table.ajax.reload();
            });

            // Export PDF button
            $('#btn_export_pdf').click(function() {
                const dateRange = $('#date_range').val();
                let url = "{{ route('dashboard.rekap.absensi.export.pdf') }}";

                if (dateRange) {
                    url += '?date=' + encodeURIComponent(dateRange);
                }

                window.location.href = url;
            });

            // Export Excel button
            $('#btn_export_excel').click(function() {
                const dateRange = $('#date_range').val();
                let url = "{{ route('dashboard.rekap.absensi.export.excel') }}";

                if (dateRange) {
                    url += '?date=' + encodeURIComponent(dateRange);
                }

                window.location.href = url;
            });

            // Print button
            $('#btn_print').click(function() {
                window.print();
            });

           $('#date_range').on('apply.daterangepicker', function () {
                let range = $('#date_range').val();
                $('#date_range').val(range);
                table.ajax.reload();
            });
        });
    </script>
@endpush
@endsection
