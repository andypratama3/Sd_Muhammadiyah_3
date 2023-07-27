@extends('layouts.dashboard')
@section('title','Detail Task')
@section('content')

<div class="row">
    <div class="col-lg-12">
      <!-- Form Basic -->
      <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
          <h6 class="m-0 font-weight-bold text-primary text-center">Detail Task</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('dashboard.pengaturan.task.update', $task->slug) }}" method="post">
                @csrf
                @method('PUT')
            <div class="form-group">
              <label for="name">Nama Task</label>
              <input type="text" class="form-control" id="name" aria-describedby="task" name="name" placeholder="Masukan Nama Task"  value="{{ $task->name }}">
            </div>
            <a href="{{ route('dashboard.pengaturan.task.index') }}" class="btn btn-danger">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Ubah Role</button>
        </form>
        </div>
      </div>
  </div>
</div>

@endsection
