@extends('layouts.dashboard')
@section('title','Pembayaran Siswa')
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.css"/>
<style>
    table.dataTable td {
    font-size: 0.9em;
}
</style>
@endpush
@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        @include('layouts.flashmessage')
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-4"> Pembayaran Siswa
                <a href="{{ route('dashboard.datamaster.pembayaran.create') }}" class="btn btn-success btn-sm float-right">Tambah <i class="fas fa-plus"></i></a>
                </h4>
                <div class="table-responsive">
                    <table class="table mt-4" id="invoice_table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pembayaran</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                {{-- <th>Category Kelas</th> --}}
                                <th>Order ID</th>
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
</div>
<input type="hidden" id="invoice_data" value="{{ route('dashboard.datamaster.get.records') }}">
@push('js')
<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.js"></script>
<script>
$(document).ready(function () {
    $('#invoice_table').DataTable({
        ordering: true,
        pagination: true,
        deferRender: true,
        serverSide: true,
        responsive: true,
        
        processing: true,
        pageLength: 100,
        ajax: {
            'url': $('#invoice_data').val(),
        },
        columns: [
            { data: 'DT_RowIndex',name: 'DT_RowIndex', orderable: false, searchable: false, ordering: false},
            { data: 'name', name: 'name'},
            { data: 'siswa.name', name: 'siswa.name'},
            { data: 'kelas.name', name: 'kelas.name'},
            // { data: 'category_kelas', name: 'category_kelas'},
            { data: 'order_id', name: 'order_id'},
            {
                data: 'gross_amount', // Refers to the data field in your dataset
                name: 'gross_amount', // Name of the column
                render: function (data, type, full, meta) { // Custom rendering function
                    return 'Rp. ' + data; // Formats the data with 'Rp.' prefix
                }
            },
            {
                data: 'status', name: 'status',
                render: function (data, type, full, meta) {
                    if (data === 'pending') {
                        return '<h5 style="color: black;"><span class="badge bg-warning"><i class="fa-solid fa-clock"></i> ' + data + '</span></h5>';
                    } else if (data === 'berhasil') {
                        return '<h5 style="color: black;"><span class="badge bg-success"><i class="fa-solid fa-circle-check"></i> ' + data + '</span></h5>';
                    } else if (data === 'failed') {
                        return '<h5 style="color: black;"><span class="badge bg-danger"><i class="fa-solid fa-xmark"></i> ' + data + '</span></h5>';
                    } else {
                        return data;
                    }
                }
            },
            { data: 'options',name: 'options', orderable: false, searchable: false }
        ],
    });
    $('#invoice_table').on('click', '#btn-delete', function () {
        var order_id = $(this).data('id');
        var url = '{{ route("dashboard.datamaster.pembayaran.destroy", ":order_id") }}'; // Use the correct route name "destroy"
        url = url.replace(':order_id', order_id);
        swal({
            title: 'Anda yakin?',
            text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // Send a DELETE request
                $.ajax({
                    url: url,
                    type: 'DELETE', // Use the DELETE method
                    success: function (data) {
                        if (data.status === 'success') {
                            swal({
                                title: 'Berhasil',
                                text: data.message,
                                icon: 'success',
                                buttons: false // This will remove the button
                            });
                                // Reload the page
                                // window.location.href = "{{ route('dashboard.datamaster.siswa.index') }}";
                                // reload table
                            reloadTable('#invoice_table');

                        } else {
                            // Reload the page with an error message
                            swal('Error', data.message, 'error');
                            window.location.href = "{{ route('dashboard.datamaster.siswa.index') }}";
                        }
                    },
                });
            } else {
                // If the user cancels the deletion, do nothing
            }
        });
    });
});
</script>
@endpush
@endsection
