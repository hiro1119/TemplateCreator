<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap -->
    <link href="{{ asset('/vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{ asset('/vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <!-- NProgress -->
    <link href="{{ asset('/vendors/nprogress/nprogress.css') }}" rel="stylesheet">

    @stack('css')
    <!-- Custom Theme Style -->
    <link href="{{ asset('/build/css/custom.min.css') }}" rel="stylesheet">
    <!-- Site Styles -->
    <link href="{{ asset('/css/site_styles.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
</head>
  <body class="nav-md" style="background: #F7F7F7;">
    <div class="container body">
      <div class="main_container">

        @yield('side')

        @yield('header')

<div class="right_col" role="main">
  <div class="">
    @if (session('error'))
    <div class="x_content bs-example-popovers">

      <div class="alert alert-danger alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <strong>エラーが発生しました。</strong> {{ session('error') }}
      </div>
    </div>
    @elseif (session('message'))
    <div class="x_content bs-example-popovers">

      <div id="alert-msg" class="alert alert-success alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <strong></strong> {{ session('message') }}
      </div>
    </div>
    @endif

    <div class="clearfix"></div>
        @yield('content')

    </div>
  </div>
  <!-- /page content -->
        @yield('footer')

      </div>
    </div>

    <!-- jQuery -->
    <script src="{{ asset('/vendors/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap -->
    <script src="{{ asset('/vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('/vendors/fastclick/lib/fastclick.js') }}"></script>
    <!-- NProgress -->
    <script src="{{ asset('/vendors/nprogress/nprogress.js') }}"></script>
    <!-- Chart.js -->
    <script src="{{ asset('/vendors/Chart.js/dist/Chart.min.js') }}"></script>
    <!-- gauge.js -->
    <script src="{{ asset('/vendors/gauge.js/dist/gauge.min.js') }}"></script>
    <!-- iCheck -->
    <script src="{{ asset('/vendors/iCheck/icheck.min.js') }}"></script>
    <!-- bootstrap-progressbar -->
    <script src="{{ asset('/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @stack('scripts')
    <!-- Custom Theme Scripts -->
    <script src="{{ asset('/build/js/custom.min.js') }}"></script>
    <script src="{{ asset('/build/js/common.js') }}"></script>
<script>
  $(function(){
    if($('#alert-msg').hasClass('in')){
        setTimeout(function(){
            $('#alert-msg').removeClass('in');
            $('#alert-msg').addClass('out');
            setTimeout(() => {
                $('#alert-msg').parent().css('display','none');
            }, 500);
        },1000);
    }
  })
</script>
  </body>
</html>
