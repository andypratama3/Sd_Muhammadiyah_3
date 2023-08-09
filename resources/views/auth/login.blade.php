<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <link href="{{ asset('asset_dashboard/img/logo/logo.png') }}" rel="icon">
  <title>Masuk</title>
  <link href="{{ asset('asset_dashboard/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
  <link href="{{ asset('asset_dashboard/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
  <link href="{{ asset('asset_dashboard/css/ruang-admin.min.css') }}" rel="stylesheet">

</head>

<body class="bg-gradient-login">
  <!-- Login Content -->
  <div class="container-login">
    <div class="row justify-content-center">
      <div class="col-xl-6 col-lg-12 col-md-9">
        <div class="card shadow-sm my-5">
          <div class="card-body p-0">
            <div class="row">
              <div class="col-lg-12">
                <div class="login-form">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4">Login</h1>
                  </div>
                  <form class="user" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-group">
                      <input type="email" class="form-control" :value="old('email')" required id="exampleInputEmail" aria-describedby="emailHelp"
                        placeholder="Enter Email Address" name="email">
                    </div>
                    @if ($errors->has('password'))
                    <div class="alert alert-primary alert-dismissible fade show text-center text-black" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </div>
                    @endif
                    <div class="form-group">
                      <input type="password" class="form-control" id="exampleInputPassword" name="password" placeholder="Password">
                    </div>
                    @if ($errors->has('email'))
                    <div class="alert alert-danger alert-dismissible fade show text-center text-black" role="alert">
                        <strong>{{ $errors->first('email') }}</strong>
                    </div>
                    @endif
                    <div class="form-group">
                      <div class="custom-control custom-checkbox small" style="line-height: 1.5rem;">
                        <input type="checkbox" class="custom-control-input" id="customCheck">
                        <label class="custom-control-label" for="customCheck">Remember
                          Me</label>
                      </div>
                    </div>
                    <div class="form-group">

                      <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </div>
                    <hr>
                    <a href="{{ route('login.google') }}" class="btn btn-google btn-block">
                      <i class="fab fa-google fa-fw"></i> Masuk Dengan Google
                    </a>
                  </form>
                  <hr>
                  <div class="text-center">
                    <a class="font-weight-bold small" href="{{ route('register') }}">Buat Akun</a>
                  </div>
                  <div class="text-center">
                  </div>
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
