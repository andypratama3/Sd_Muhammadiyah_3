@extends('layouts.dashboard')
@section('title','Tambah Task')
@section('content')

<div class="row">
    <div class="col-lg-12">
      <!-- Form Basic -->
      <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary text-center">Tambah Task</h6>
        </div>
        <div class="card-body">
          <form action="{{ route('dashboard.pengaturan.task.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
              <label for="name">Nama Task</label>
              <input type="text" class="form-control" id="name" aria-describedby="task" name="name" placeholder="Masukan Nama Task">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
          </form>
        </div>
      </div>
  </div>
</div>

@endsection
