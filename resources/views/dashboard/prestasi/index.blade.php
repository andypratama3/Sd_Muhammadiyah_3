@extends('layouts.dashboard')
@section('title','Prestasi')
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
        overflow-y: initial !important;
        position: fixed;
        z-index: 1;
        padding-top: 80px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.9);
    }

    .modal-content {
        margin: auto;
        display: flex;
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
<div class="mb-4 col-lg-12">
    <!-- Simple Tables -->
    <div class="card">
        @include('layouts.flashmessage')
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h4 class="m-0 text-center font-weight-bold">Prestasi</h4>
            <a href="{{ route('dashboard.datasekolah.prestasi.create') }}" class="float-right btn btn-success btn-sm">Tambah <i
                    class="fas fa-plus"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table text-center align-items-center table-flush">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Prestasi</th>
                        <th>Deskripsi</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prestasis as $prestasi)
                    <tr>
                        <td>{{ ++$no }}</td>
                        <td>{{ $prestasi->name }}</td>
                        <td>{!! \Str::limit($prestasi->description, 150) !!}</td>
                        <td>
                            <a href="{{ route('dashboard.datasekolah.prestasi.show', $prestasi->slug) }}"
                                class="btn btn-dark btn-sm"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('dashboard.datasekolah.prestasi.edit', $prestasi->slug) }}"
                                class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
                            <a href="#" data-id="{{ $prestasi->slug }}" class="btn btn-danger btn-sm delete"
                                title="Hapus">
                                <form action="{{ route('dashboard.datasekolah.prestasi.destroy', $prestasi->slug) }}"
                                    id="delete-{{ $prestasi->slug }}" method="POST" enctype="multipart/form-data">
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
        <div class="clearfix card-footer">
            <ul class="float-left m-0">
                <span class="badge badge-primary">Total : {{ $count }} Data</span>
            </ul>
            <ul class="float-right m-0 pagination">
                {{ $prestasis->onEachSide(1)->links() }}
            </ul>
        </div>
    </div>
    </div>
</div>
@endsection
