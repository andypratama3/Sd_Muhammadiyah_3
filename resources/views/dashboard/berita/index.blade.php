@extends('layouts.dashboard')
@section('title','Berita')
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
<style>
    #myImg {
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }
    #myImg:hover {
        opacity: 0.7;
    }
    .modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        padding-top: 100px;
        /* Location of the box */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.9);
    }
    .modal-content {
        margin: auto;
        display: block;
        width: 100%;
        max-width: 800px;
    }
    @keyframes zoom {
        from {
            transform: scale(0)
        }

        to {
            transform: scale(1)
        }
    }
    /* The Close Button */
    .closeheader {
        position: absolute;
        top: 15px;
        right: 35px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
    }
    .closeheader:hover,
    .closeheader:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }
    @media only screen and (max-width: 700px) {
        .modal-content {
            width: 100%;
        }
    }
</style>
@endpush
@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="m-0 font-weight-bold text-center">Berita</h4>
            <a href="{{ route('dashboard.news.berita.create') }}" class="btn btn-primary btn-sm float-right">Tambah <i
                    class="fas fa-plus"></i></a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="berita_table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Foto</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<!--Row-->
<div id="myModal" class="modal">

    <!-- The Close Button -->
    <span class="closeheader">&times;</span>

    <!-- Modal Content (The Image) -->
    <img class="modal-content" id="foto" src="">

    <!-- Modal Caption (Image Text) -->
    <div id="caption"></div>
</div>
<input type="hidden" id="berita_table_value" value="{{ route('dashboard.news.berita.getBerita') }}">
@push('js')
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script>
$(document).ready(function () {
    $('#berita_table').DataTable({
        ordering: true,
        pagination: true,
        deferRender: true,
        serverSide: true,
        responsive: true,
        processing: true,
        pageLength: 100,
        ajax: {
            'url': $('#berita_table_value').val(),
        },
        columns: [
            { data: 'DT_RowIndex',name: 'DT_RowIndex',orderable: false,searchable: false},
            { data: 'judul', name: 'judul'},
            { data: 'desc', name: 'desc', orderable: false,
                render: function (data) {
                    return '<p>' + data.substring(0, 20) + '...</p>';
                }
            },
            { data: 'foto', name: 'foto', orderable: false,
                render: function (data) {
                    return '<a href="storage/img/berita/'+data+'" target="_blank" class="btn btn-success btn-sm">Lihat Foto</a>';
                }
            },
            { data: 'options',name: 'options', orderable: false, searchable: false }
        ],
    });
    $('#berita_table').on('click', '#btn-delete', function () {
        var slug = $(this).data('id');
        var url = '{{ route("dashboard.news.berita.destroy", ":slug") }}';
        url = url.replace(':slug', slug);
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
                                // redirect fucntion
                                // window.location.href = "{{ route('dashboard.news.berita.index') }}";
                                 // reload datatable
                                 reloadTable('#artikel_table');
                        } else {
                            window.location.href = "{{ route('dashboard.news.berita.index') }}";
                        }
                    },
                });
            } else {
                // If the user cancels the deletion, do nothing
            }
        });
    });
});
// $(document).on('click', '#priview-image', function () {
//     // Get the modal
//     var modal = document.getElementById("myModal");
//     //take foto from folder
//     var foto = $(this).data('foto');
//     var imageUrl = '/storage/app/public/img/berita/' + foto;

//     modal.style.display = "block";
//     $('#foto').attr('src', imageUrl);

//     // // Get the <span> element that closes the modal
//     var span = document.getElementsByClassName("closeheader")[0];
//     // // When the user clicks on <span> (x), close the modal
//     span.onclick = function () {
//         modal.style.display = "none";
//     }
// });
</script>
@endpush
@endsection
