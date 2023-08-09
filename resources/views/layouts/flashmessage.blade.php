<style>
    .alert {
        text-align: center;
        position: relative;
        animation: floatImage 8s;
    }
    @keyframes floatImage {
    0%{
        width: 100%;
    }
    50%{
        width: 50%;
    }

}

    .btn-close {
        position: absolute;
        top: 0;
        right: 0;
        z-index: 2;
        padding: 1.25rem 1rem;
    }
</style>

@if (count($errors) > 0)
<div class="alert alert-danger alert-dismissible" delay=1>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h5><i class="icon fas fa-ban"></i> Alert!</h5>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
</div>
@endif
@if ($message = Session::get('success'))
<div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show" role="alert" delay=10>
    <strong>{{ $message }}</strong>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
</div>
@endif

@if ($message = Session::get('error'))
<div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show" role="alert">
    <strong>{{ $message }}</strong>
</div>
@endif

@if ($message = Session::get('warning'))
<div class="alert alert-warning bg-warning border-0 alert-dismissible fade show" role="alert">
    <strong>{{ $message }}</strong>
</div>
@endif
