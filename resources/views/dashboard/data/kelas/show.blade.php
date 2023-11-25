@extends('layouts.dashboard')
@section('title', 'Detail Kelas')
@section('content')
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Show Kelas {{ $kelas->name }}</h6>
    </div>
    <div class="card-body">
        <input type="hidden" name="slug" value="{{ $kelas->slug }}">
        <div class="form-group">
            <label for="Kelas">Nama Kelas</label>
            <input type="text" class="form-control" name="name" value="{{ $kelas->name }}" id="Kelas"
                aria-describedby="emailHelp" placeholder="Masukan Nama Kelas" readonly>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered text-center" id="dynamicAddRemove">
                        <tr>
                            <th class="w-75">Kategori Kelas</th>
                        </tr>
                        @php
                            $categoryKelas = json_decode($kelas->category_kelas, true);
                            sort($categoryKelas);
                        @endphp
                        @foreach ($categoryKelas as $category)
                        <tr>
                            <td><input type="text" readonly value="{{ $category }}" class="form-control" name="category_kelas[]" placeholder="Masukkan Kategori Kelas"></td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
        <a href="{{ route('dashboard.datamaster.kelas.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
    </div>
</div>
@endsection
