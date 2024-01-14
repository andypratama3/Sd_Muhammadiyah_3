@extends('layouts.dashboard')
@section('title','Fasilitas')
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

<div class="row">
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Fasilitas</h4>
                <a href="{{ route('dashboard.fasilitas.create') }}" class="btn btn-success float-right">Tambah <i
                        class="fas fa-plus"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Fasilitas</th>
                            <th>Deskripsi</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fasilitass as $fasilitas)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>{{ $fasilitas->nama_fasilitas }}</td>
                            <td>{{ $fasilitas->desc }}</td>
                            <td>
                                <a href="{{ route('dashboard.fasilitas.show', $fasilitas->slug) }}"
                                    class="btn btn-dark btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('dashboard.fasilitas.edit', $fasilitas->slug) }}"
                                    class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
                                <a href="#" data-id="{{ $fasilitas->slug }}" class="btn btn-danger btn-sm delete"
                                    title="Hapus">
                                    <form action="{{ route('dashboard.fasilitas.destroy', $fasilitas->slug) }}"
                                        id="delete-{{ $fasilitas->slug }}" method="POST" enctype="multipart/form-data">
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
                    {{ $fasilitass->onEachSide(1)->links() }}
                </ul>
            </div>
        </div>
        </div>
    </div>
</div>

@endsection
