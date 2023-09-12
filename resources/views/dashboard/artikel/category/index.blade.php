@extends('layouts.dashboard')
@section('title','Category Artikel')
@section('content')
<div class="row">
    <div class="col-lg-12 mb-4">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h4 class="m-0 font-weight-bold text-primary text-center">Kategori Artikel</h5>
                    <a href="{{ route('dashboard.news.category.create') }}" class="btn btn-primary float-end">Tambah</a>
            </div>
            <div class="table-responsive">
                <table class="table align-items-center table-flush text-center">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Kategori</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categorys as $category)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td>{{ $category->name }}</td>
                            <td>
                                <a href="{{ route('dashboard.news.category.edit', $category->slug) }}" class="btn btn-dark btn-sm"><i
                                        class="fas fa-pen"></i></a>
                                <a href="#" data-id="{{ $category->slug }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                    <form action="{{ route('dashboard.news.category.destroy', $category->slug) }}"
                                        id="delete-{{ $category->slug }}" method="POST" enctype="multipart/form-data">
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
@endsection
