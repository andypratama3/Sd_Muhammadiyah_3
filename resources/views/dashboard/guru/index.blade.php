@extends('layouts.dashboard')
@section('title','Dashboard')
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
        position: fixed;
        z-index: 1;
        padding-top: 100px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.9);
        z-index: 9999;
    }
    .modal-content {
        margin: auto;
        display: block;
        width: 80%;
        max-width: 500px;
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
        <div class="card">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Data Guru</h5>
                    <a href="{{ route('dashboard.datasekolah.guru.create') }}" class="btn btn-success btn-sm float-right">Tambah <i class="fas fa-plus"></i></a>
            </div>
            <div class="row mx-2 mb-2">
                <div class="col-md-12">
                    {{-- <div class="form-group">
                        <label for="sortir">Sortir</label>
                        <select name="mata_pelajaran" id="mata_pelajaran" class="form-control">
                            <option value="">Semua</option>
                            @foreach ($mataPelajarans as $pelajaran)
                                <option value="{{ $pelajaran->id }}">{{ $pelajaran->name }}</option>
                            @endforeach
                        </select>
                    </div> --}}
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center" id="table-guru">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Lulusan</th>
                            <th>Foto</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gurus as $guru)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>{{ $guru->name }}</td>
                            <td>{{ $guru->description }}</td>
                            <td>{{ $guru->lulusan }}</td>
                            <td>
                                <span class="btn btn-secondary fa fa-image" id="priview-image" data-foto="<?=$guru->foto ?>"></span>
                            </td>
                            <td>
                                <a href="{{ route('dashboard.datasekolah.guru.show', $guru->slug) }}" class="btn btn-dark btn-sm"><i
                                        class="fas fa-info-circle"></i></a>
                                <a href="{{ route('dashboard.datasekolah.guru.edit', $guru->slug) }}" class="btn btn-primary btn-sm"><i
                                        class="fa fa-pen"></i></a>
                                <a href="#" data-id="{{ $guru->slug }}" class="btn btn-danger delete btn-sm" title="Hapus">
                                    <form action="{{ route('dashboard.datasekolah.guru.destroy', $guru->slug) }}"
                                        id="delete-{{ $guru->slug }}" method="POST" enctype="multipart/form-data">
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
            <div class="card-footer clearfix">
                <ul class="m-0 float-left">
                    <span class="badge badge-primary">Total : {{ $count }} Data</span>
                </ul>
                <ul class="pagination m-0 float-right">
                    {{ $gurus->onEachSide(1)->links() }}
                </ul>
            </div>
            <div class="card-footer"></div>
        </div>
    </div>
</div>
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
        var imageUrl = '/storage/img/guru/' + foto;

        modal.style.display = "block";
        $('#foto').attr('src', imageUrl);

        // // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("closeheader")[0];
        // // When the user clicks on <span> (x), close the modal
        span.onclick = function () {
            modal.style.display = "none";
        }
    });

    $(document).ready(function () {
        $('#mata_pelajaran').on('change', function () {
            var mata_pelajaran = $(this).val();
            $.ajax({
                url: "{{ route('dashboard.datasekolah.guru.index') }}",
                type: "GET",
                data: {
                    mata_pelajaran: mata_pelajaran
                },
                success: function (data) {
                    $('.table').load(location.href + " .table");

                }
            });
        });
    });
</script>
@endpush
@endsection
