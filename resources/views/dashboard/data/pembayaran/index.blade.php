@extends('layouts.dashboard')
@section('title','Pembayaran Siswa')
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.css"/>
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
                                <th>Order ID</th>
                                <th>Total</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        {{-- <tbody>
                            @foreach ($pembayarans as $pembayaran)
                                <tr>
                                    <td>{{ ++$no }}</td>
                                    <td>{{ $pembayaran->name }}</td>
                                    <td>{{ $pembayaran->siswa->name }}</td>
                                    <td>{{ $pembayaran->order_id }}</td>
                                    <td>Rp. {{ $pembayaran->gross_amount }}</td>
                                    <td>{{ $pembayaran->kelas->name }}</td>
                                    <td>
                                        @if ($pembayaran->status === 'pending')
                                            <span class="badge badge-warning">{{ $pembayaran->status }}</span>
                                        @elseif ($pembayaran->status === 'failed')
                                        <span class="badge badge-danger">{{ $pembayaran->status }}</span>
                                        @elseif ($pembayaran->status === 'success')
                                        <span class="badge badge-success">{{ $pembayaran->status }}</span>

                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('dashboard.datamaster.pembayaran.show', $pembayaran->order_id) }}"
                                            class="btn btn-dark btn-sm"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('dashboard.datamaster.pembayaran.edit', $pembayaran->order_id) }}"
                                            class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
                                        <a href="#" data-id="{{ $pembayaran->order_id }}" class="btn btn-danger btn-sm delete"
                                            title="Hapus">
                                            <form action="{{ route('dashboard.datamaster.pembayaran.destroy', $pembayaran->order_id) }}"
                                                id="delete-{{ $pembayaran->order_id }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('delete')
                                            </form>
                                            <i class="fas fa-trash"></i>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody> --}}
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
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
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
            { data: 'DT_RowIndex',name: 'DT_RowIndex',orderable: false,searchable: false},
            { data: 'name', name: 'name'},
            { data: 'siswa.name', name: 'siswa.name'},
            { data: 'kelas.name', name: 'kelas.name'},
            { data: 'order_id', name: 'order_id'},
            { data: 'category_kelas', name: 'category_kelas'},
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
                            swal('Berhasil', data.message, 'success').then(() => {
                                // Reload the page
                                // window.location.href = "{{ route('dashboard.datamaster.siswa.index') }}";
                                // reload table
                                reloadTable('#invoice_table');
                                // Reload the page with a success message
                            });
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
