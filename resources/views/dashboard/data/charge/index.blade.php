@extends('layouts.dashboard')
@section('title', 'Pembayaran Siswa')
@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <style>
        table.dataTable td {
            font-size: 0.9em;
        }
    </style>
@endpush
@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    @include('layouts.flashmessage')
    <div class="card">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="card-title">Pembayaran Siswa</h4>
        </div>
        <div class="card-body">
            <div class="form-group row gap-3">
                <div class="col-md-2">
                    <div class="form-group">
                        <select name="kelas" id="kelas" class="form-control">
                            <option selected value="">Pilih Kelas</option>
                            @foreach ($kelass as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="text" name="date" id="date_range" class="form-control" placeholder="Pilih Tanggal">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <button class="btn btn-success" id="exportData-excel">
                            <i class="fas fa-file-excel"></i>
                            Export
                        </button>
                        <form action="{{ route('dashboard.datamaster.charge.exportExcel') }}" method="POST"
                            id="exportForm" style="display: none;">
                            @csrf
                            <input type="hidden" name="kelas" id="export_kelas">
                            <input type="hidden" name="date" id="export_date">
                        </form>
                    </div>
                </div>
            </div>
            <div class="table-responsive mt-4">
                <table class="table mt-4 w-100" id="charge_table" >
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Va Number</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js" defer></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.js"></script>
    <script>

        $(document).ready(function() {
            $('input[name="date"]').daterangepicker({
                timePicker: true,
                autoUpdateInput: false,  // This prevents automatic updating of the input value
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
            function reloadTable(id) {
                var table = $(id).DataTable();
                table.cleanData;
                table.ajax.reload();
            }
            // Initialize DataTable
            let table = $('#charge_table').DataTable({
                ordering: true,
                pagination: true,
                deferRender: true,
                serverSide: true,
                responsive: true,
                processing: true,
                pageLength: 100,
                ajax: {
                    url: "{{ route('dashboard.datamaster.charge.get.records') }}",
                    data: function(d) {
                        d.kelas = $('#kelas').val();
                        d.date = $('#date_range').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, ordering: false },
                    { data: 'name', name: 'name' },
                    { data: 'siswa.name', name: 'siswa.name' },
                    { data: 'kelas.name', name: 'kelas.name' },
                    { data: 'va_number', name: 'va_number' },
                    {
                        data: 'gross_amount',
                        name: 'gross_amount',
                        render: function(data, type, full, meta) {
                            return 'Rp. ' + data;
                        }
                    },
                    {
                        data: 'transaction_status',
                        name: 'transaction_status',
                        render: function(data, type, full, meta) {
                            if (data === 'pending') {
                                return '<h6 style="color: black;"><span class="badge bg-warning"><i class="fa-solid fa-clock"></i> ' + data + '</span></h6>';
                            } else if (data === 'settlement') {
                                return '<h6 style="color: black;"><span class="badge bg-success"><i class="fa-solid fa-circle-check"></i> ' + data + '</span></h6>';
                            } else if (data === 'Expired' || data === 'failed') {
                                return '<h6 style="color: black;"><span class="badge bg-danger"><i class="fa-solid fa-xmark"></i> ' + data + '</span></h6>';
                            } else if(data === 'pay_offline'){
                                return '<h6 style="color: black;"><span class="badge bg-success"><i class="fa-solid fa-circle-check"></i> ' + data + '</span></h6>';
                            } else {
                                return data;
                            }
                        }
                    },
                    { data: 'options', name: 'options', orderable: false, searchable: false }
                ],
            });

            // Scroll to top when changing DataTable pages
            table.on('page.dt', function() {
                // Scroll to top using a smooth scroll
                // e.preventDefault();
                $('html, body').animate({ scrollTop: 0 }, 'slow');
            });

            $('#charge_table').on('click', '#btn-delete', function() {
                var id = $(this).data('id');
                var url = "{{ route('dashboard.datamaster.charge.destroy', ':id') }}";
                url = url.replace(':id', id);
                Swal.fire({
                    title: 'Anda yakin?',
                    text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    buttons: true,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus Data',
                    cancelButtonText: 'Tidak, Batalkan!',
                    reverseButtons: true
                }).then((willDelete) => {
                    if (willDelete.isConfirmed) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        // Send a DELETE request
                        $.ajax({
                            url: url,
                            type: 'DELETE', // Use the DELETE method
                            success: function(data) {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        title: 'Berhasil',
                                        text: data.message,
                                        icon: 'success',
                                        buttons: false // This will remove the button
                                    });
                                    // Reload the page
                                    // window.location.href = "{{ route('dashboard.datamaster.siswa.index') }}";
                                    // reload table
                                    reloadTable('#charge_table');

                                } else {
                                    // Reload the page with an error message
                                    Swal.fire('Error', data.message, 'error');
                                    window.location.href =
                                        "{{ route('dashboard.datamaster.siswa.index') }}";
                                }
                            },
                        });
                    } else {
                        Swal.fire('Data Batal Dihapus', 'info');
                    }
                });
            });
            $('#kelas').on('change', function () {
                reloadTable('#charge_table');
            });

            $('#date_range').on('apply.daterangepicker', function () {
                let range = $('#date_range').val();
                $('#date_range').val(range);
                reloadTable('#charge_table');

            });
            $('#category_kelas').on('change', function () {
                reloadTable('#charge_table');
            });
            $('#exportData-excel').click(function(e) {
                e.preventDefault();
                $('#export_kelas').val($('#kelas').val());
                $('#export_date').val($('#date_range').val());
                $('#exportForm').submit();
            });
        });
    </script>
@endpush
@endsection
