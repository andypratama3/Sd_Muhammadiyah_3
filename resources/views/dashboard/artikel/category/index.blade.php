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
                    <a href="{{ route('dashboard.news.category.create') }}" class="btn btn-success btn-sm float-right">Tambah
                        <i class="fas fa-plus"></i></a>
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
                                <a href="{{ route('dashboard.news.category.edit', $category->slug) }}"
                                    class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
                                <a href="#" data-id="{{ $category->slug }}" class="btn btn-danger btn-sm delete"
                                    title="Hapus">
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
                <div class="col-md-12">Total : {{ $count }}</div>
                <ul class="pagination justify-content-end">
                    <li class="page-item mr-2"> {{ $categorys->onEachSide(1)->links() }}</li></li>
                </ul></div>
            </div>
        </div>
    </div>
</div>
@endsection
