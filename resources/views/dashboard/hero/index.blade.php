@extends('layouts.dashboard')
@section('title','Gambar Depan')
@push('css')

@section('content')
<div class="col-lg-12 mb-4">
    <!-- Simple Tables -->
    <div class="card">
        @include('layouts.flashmessage')
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="m-0 font-weight-bold text-center">Gambar Depan</h4>
            <a href="{{ route('dashboard.news.hero.create') }}" class="btn btn-primary btn-sm float-right">Tambah <i
                    class="fas fa-plus"></i></a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Foto</th>
                            <th>Deskripsi</th>
                            <th>Youtube</th>
                            <th>Link</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($heroes as $hero)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>{{ $hero->name }}</td>
                            <td><a href="{{ asset('storage/img/hero/'.$hero->image) }}" target="_blank" class="btn btn-success btn-sm">Lihat Foto</a></td>
                            <td>{{ $hero->desc }}</td>
                            <td>{{ $hero->youtube }}</td>
                            <td>{{ $hero->link }}</td>
                            <td>
                                <a href="{{ route('dashboard.news.hero.edit', $hero->slug) }}"
                                    class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
                                <a href="#" data-id="{{ $hero->slug }}" class="btn btn-danger btn-sm delete"
                                    title="Hapus">
                                    <form action="{{ route('dashboard.news.hero.destroy', $hero->slug) }}"
                                        id="delete-{{ $hero->slug }}" method="POST" enctype="multipart/form-data">
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
        </div>
        <div class="card-footer clearfix">
            <ul class="m-0 float-left">
                <span class="badge badge-primary">Total : {{ $count }} Data</span>
            </ul>
            <ul class="pagination m-0 float-right">
                {{ $heroes->onEachSide(1)->links() }}
            </ul>
        </div>
    </div>
    </div>
</div>

@endsection
