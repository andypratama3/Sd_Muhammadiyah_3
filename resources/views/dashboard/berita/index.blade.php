@extends('layouts.dashboard')
@section('title','Berita')
@push('css')
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
<div class="row">
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Data Berita</h5>
                    <a href="{{ route('dashboard.news.berita.create') }}" class="btn btn-primary float-end">Tambah</a>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Foto</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($beritas as $berita)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>{{ $berita->judul }}</td>
                            <td>{{ $berita->desc }}</td>
                            <td>
                                <span class="btn fa fa-image" id="priview-image" data-foto="<?=$berita->foto ?>">
                                    <p>Lihat</p>
                                </span>

                            <td>
                                <a href="{{ route('dashboard.news.berita.show', $berita->slug) }}" class="btn btn-dark btn-sm"><i
                                        class="fas fa-info-circle"></i></a>
                                <a href="{{ route('dashboard.news.berita.edit', $berita->slug) }}" class="btn btn-primary btn-sm"><i class="fa fa-pen"></i></a>
                                <a href="#" data-id="{{ $berita->slug }}" class="btn btn-danger delete" title="Hapus">
                                    <form action="{{ route('dashboard.news.berita.destroy', $berita->slug) }}"
                                        id="delete-{{ $berita->slug }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('delete')
                                    </form>
                                    <i class="fas fa-trash"></i>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer"></div>
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
<!--Row-->
@push('js')
<script>
    $(document).on('click', '#priview-image', function () {
        // Get the modal
        var modal = document.getElementById("myModal");
        //take foto from folder
        var foto = $(this).data('foto');
        var imageUrl = '/storage/img/berita/' + foto;

        modal.style.display = "block";
        $('#foto').attr('src', imageUrl);

        // // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("closeheader")[0];
        // // When the user clicks on <span> (x), close the modal
        span.onclick = function () {
            modal.style.display = "none";
        }
    });
</script>
@endpush
@endsection
