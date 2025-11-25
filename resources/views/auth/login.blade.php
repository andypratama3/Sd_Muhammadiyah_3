<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="{{ asset('asset_dashboard/img/logo/logo.png') }}" rel="icon">
  <title>Login</title>
  <link href="{{ asset('asset_dashboard/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
  <link href="{{ asset('asset_dashboard/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('asset_dashboard/css/ruang-admin.min.css') }}" rel="stylesheet">

</head>

<body class="bg-gradient-login">
  <!-- Login Content -->
  <div class="container-login">
    <div class="row justify-content-center">
      <div class="col-xl-6 col-lg-12 col-md-9">
        <div class="my-5 shadow-sm card">
          <div class="p-0 card-body">
            <div class="row">
              <div class="col-lg-12">
                <div class="login-form">
                  <div class="text-center">
                    <img src="{{ asset('asset/img/SD3_logo1.png') }}" alt="" class="mb-2 img-fluid w-25">
                    <h1 class="mb-4 text-gray-900 h4">Portal Login SD Muhammadiyah 3 Samarinda</h1>
                  </div>
                  <form class="user" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-group">
                      <input type="email" class="form-control" :value="old('email')" value="{{ old('email') }}" required id="exampleInputEmail" aria-describedby="emailHelp"
                        placeholder="Enter Email Address" name="email">
                    </div>
                    @if ($errors->has('password'))
                    <div class="text-center text-black alert alert-primary alert-dismissible fade show" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </div>
                    @endif
                    <div class="form-group">
                      <input type="password" class="form-control" id="exampleInputPassword" name="password" placeholder="Password">
                    </div>
                    @if ($errors->has('email'))
                    <div class="text-center text-black alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </div>
                    @endif
                    <div class="form-group">
                      <button type="submit" class="btn btn-success btn-block">Login</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Login Content -->
  <script src="{{ asset('asset_dashboard/vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('asset_dashboard/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('asset_dashboard/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
  <script src="{{ asset('asset_dashboard/js/ruang-admin.min.js') }}"></script>
</body>

</html>
