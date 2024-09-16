@extends('layouts.dashboard')
@section('title','Gallery')
@section('content')
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-center">Gallery</h4>
                <a href="{{ route('dashboard.datasekolah.gallery.create') }}" class="btn btn-success float-right btn-sm">Tambah <i
                        class="fas fa-plus"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Foto</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gallerys as $gallery)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>{{ $gallery->name }}</td>
                            <td>
                                <a href="{{ asset('storage/img/gallery/'. $gallery->foto) }}" target="__blank" class="btn btn-success btn-sm" id="priview-image"><i class="fa fa-image"></i> Lihat</a>
                            </td>
                            <td>
                                <a href="{{ route('dashboard.datasekolah.gallery.edit', $gallery->slug) }}"
                                    class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
                                <a href="#" data-id="{{ $gallery->slug }}" class="btn btn-danger btn-sm delete"
                                    title="Hapus">
                                    <form action="{{ route('dashboard.datasekolah.gallery.destroy', $gallery->slug) }}"
                                        id="delete-{{ $gallery->slug }}" method="POST" enctype="multipart/form-data">
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
                    {{ $gallerys->onEachSide(1)->links() }}
                </ul>
            </div>
        </div>
        </div>
    </div>

@endsection
