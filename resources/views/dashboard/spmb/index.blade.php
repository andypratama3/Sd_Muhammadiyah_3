@extends('layouts.dashboard')
@section('title', 'Pembayaran Siswa')
@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.css" />
@endpush
@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title"> Data SPMB</h4>
            <a href="{{ route('dashboard.datamaster.pembayaran.create') }}" class="float-right btn btn-success btn-sm">
                Tambah
                <i class="fas fa-plus"></i>
            </a>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-6">
                    <div class="form-group">
                        <select name="tahun" id="tahun" class="form-control">
                            <option selected value="">Pilih Tahun SPMB</option>
                            @for($i = 2019; $i <= date('Y'); $i++)
                                <option value="{{ $i }}" {{ request('tahun', date('Y')) == $i ? 'selected' : '' }}>Tahun {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary export_data" type="button"><i class="fa fa-excel"></i> Export Excel</button>
                </div>
            </div>
            <div class="mt-4 table-responsive">
                <table class="table mt-4 w-100" id="spmb_table" >
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Urut</th>
                            <th>Nama</th>
                            <th>Status Pembayaran</th>
                            <th>Status SPMB</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script type="text/javascript"
        src="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            function reloadTable(id) {
                var table = $(id).DataTable();
                table.cleanData;
                table.ajax.reload();
            }


            $('#spmb_table').DataTable({
                ordering: true,
                pagination: true,
                deferRender: true,
                serverSide: true,
                responsive: true,
                processing: true,
                pageLength: 100,
                ajax: {
                    'url': "{{ route('dashboard.spmb.data_table') }}",
                    'data': function(d) {
                        d.tahun = $('#tahun').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        ordering: false
                    },
                    {
                        data: 'nomor_urut',
                        name: 'nomor_urut'
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'status_pembayaran',
                        name: 'status_pembayaran'
                    },
                    {
                        data: 'status',
                        name: 'status',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });

            $('#spmb_table').on('click', '#btn-delete', function() {
                var order_id = $(this).data('id');
                var url =
                '{{ route('dashboard.spmb.destroy', ':order_id') }}'; // Use the correct route name "destroy"
                url = url.replace(':order_id', order_id);
                Swal.fire({
                        title: 'Anda yakin?',
                        text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus Data',
                        cancelButtonText: 'Tidak, Batalkan!',
                        reverseButtons: true,
                        dangerMode: true,
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
                                    reloadTable('#spmb_table');

                                } else {
                                    // Reload the page with an error message
                                    Swal.fire('Error', data.message, 'error');
                                    window.location.href =
                                        "{{ route('dashboard.datamaster.siswa.index') }}";
                                }
                            },
                        });
                    } else {
                        // If the user cancels the deletion, do nothing
                    }
                });
            });

            $('#tahun').on('change', function() {
                reloadTable('#spmb_table');
            });


            $('.export_data').on('click', function() {
                let tahun = $('#tahun').val();

                if (!tahun) {
                    Swal.fire({
                        title: 'Error',
                        text: "Tahun Tidak Boleh Kosong",
                        icon: 'error',
                    });
                    return;
                }

                // Membuat URL dengan query param tahun
                let url = "{{ route('dashboard.spmb.export_excel') }}?year=" + tahun;

                // Redirect browser ke URL tersebut untuk trigger download
                window.location.href = url;
            });


        });
    </script>
@endpush
@endsection
