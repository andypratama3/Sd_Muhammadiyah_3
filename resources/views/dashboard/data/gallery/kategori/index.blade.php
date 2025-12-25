@extends('layouts.dashboard')
@section('title','Kategori Gallery')
@section('content')
<div class="mb-4 col-lg-12">
    <!-- Simple Tables -->
    <div class="card">
        @include('layouts.flashmessage')
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h4 class="m-0 text-center font-weight-bold">Kategori Gallery</h4>
            <a href="{{ route('dashboard.datasekolah.kategori.gallery.create') }}" class="float-right btn btn-success btn-sm">Tambah <i
                    class="fas fa-plus"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table text-center align-items-center table-flush">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kategoriGallery as $kategori)
                    <tr>
                        <td>{{ ++$no }}</td>
                        <td>{{ $kategori->name }}</td>
                        <td>
                            <a href="{{ route('dashboard.datasekolah.kategori.gallery.show', $kategori->id) }}"
                                class="btn btn-dark btn-sm"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('dashboard.datasekolah.kategori.gallery.edit', $kategori->id) }}"
                                class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
                            <a href="#" data-id="{{ $kategori->id }}" class="btn btn-danger btn-sm delete"
                                title="Hapus">
                                <form action="{{ route('dashboard.datasekolah.kategori.gallery.destroy', $kategori->id) }}"
                                    id="delete-{{ $kategori->id }}" method="POST" enctype="multipart/form-data">
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
                {{ $kategoriGallery->onEachSide(1)->links() }}
            </ul>
        </div>
    </div>
    </div>
</div>
@endsection
