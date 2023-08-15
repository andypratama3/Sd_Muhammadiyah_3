@extends('layouts.dashboard')
@section('title','Artikel')
@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Data Artikel</h5>
                    <a href="{{ route('dashboard.artikel.create') }}" class="btn btn-primary float-end">Tambah</a>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Artikel</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    {{-- <tbody>
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
                                <a href="{{ route('dashboard.berita.show', $berita->slug) }}" class="btn btn-dark"><i
                                        class="fas fa-info-circle"></i></a>
                                <a href="#" data-id="{{ $berita->slug }}" class="btn btn-danger delete" title="Hapus">
                                    <form action="{{ route('dashboard.berita.destroy', $berita->slug) }}"
                                        id="delete-{{ $berita->slug }}" method="POST" enctype="multipart/form-data">
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
            <div class="card-footer"></div>
        </div>
    </div>
</div>
@endsection
