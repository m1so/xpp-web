<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>
<head>
    <meta charset="UTF-8">
    <title>@yield('title') | xpp-web</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Gulp'd & Elixir'd stuff -->
    <link href="{{ elixir('css/bootstrap.css') }}" rel="stylesheet" />
    <link href="{{ elixir('css/app.css') }}" rel="stylesheet" />
    <link href="{{ elixir('css/main.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="skin-green layout-top-nav">
<div class="wrapper">

    @include('common.navbar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="container-fluid">
            <!-- HEADER template -->
            @include('common.header')

                    <!-- Main content -->
            <section class="content">

                @yield('content')

            </section>
        </div>
    </div><!-- /.content-wrapper -->

    <!-- FOOTER template -->
    @include('common.footer')

</div><!-- ./wrapper -->

<!-- REQUIRED JS SCRIPTS -->

<script src="{{ elixir('js/vendor.js') }}"></script>
<script src="{{ elixir('js/main.js') }}"></script>

</body>
</html>
