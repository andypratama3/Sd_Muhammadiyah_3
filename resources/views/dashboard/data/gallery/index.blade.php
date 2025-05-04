@extends('layouts.dashboard')
@section('title','Gallery')
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.css" />
@endpush
@section('content')
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            <div class="card-body">
                @include('layouts.flashmessage')
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h4 class="m-0 font-weight-bold text-center">Gallery</h4>
                    <a href="{{ route('dashboard.datasekolah.gallery.create') }}" class="btn btn-success float-right btn-sm">Tambah <i
                            class="fas fa-plus"></i></a>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table mt-4 w-100" id="gallery_table" >
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Cover</th>
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
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        let table = $('#gallery_table').DataTable({
            ordering: true,
            pagination: true,
            deferRender: true,
            serverSide: true,
            responsive: true,
            processing: true,
            pageLength: 100,
            ajax: {
                url: "{{ route('dashboard.datasekolah.gallery.data') }}",
            },
            autoWidth: false,
            responsive: true,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, ordering: false },
                { data: 'name', name: 'name' },
                { data: 'cover', name: 'cover' },
                { data: 'options', name: 'options', orderable: false, searchable: false }
            ],
        });

        // Scroll to top when changing DataTable pages
        table.on('page.dt', function() {
            // Scroll to top using a smooth scroll
            // e.preventDefault();
            $('html, body').animate({ scrollTop: 0 }, 'slow');
        });

        $('#gallery_table').on('click', '#btn-delete', function() {
            var id = $(this).data('id');
            var url = "{{ route('dashboard.datasekolah.gallery.destroy', ':id') }}";
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
                                reloadTable('#gallery_table');

                            } else {
                                // Reload the page with an error message
                                Swal.fire('Error', data.message, 'error');
                                window.location.href =
                                    "{{ route('dashboard.datasekolah.gallery.index') }}";
                            }
                        },
                    });
                } else {
                    Swal.fire('Data Batal Dihapus', 'info');
                }
            });
        });
    });
</script>
@endpush
@endsection
