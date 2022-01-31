<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet" />
    <link rel="apple-touch-icon" sizes="180x180" href="/navibot/public/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/navibot/public/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/navibot/public/assets/img/favicon-16x16.png">
    <link href="{{url('../public/assets/bootstrap/4.6.0/bootstrap.min.css')}}" rel="stylesheet" type="text/css" media="all" >
    <link href="{{url('../public/plugins/sweet-alert-2/sweet-alert-min.css')}}" rel="stylesheet" type="text/css" media="all" >
    <link href="{{url('../public/plugins/font-awesome/5.15.4/css/all.css')}}" rel="stylesheet" type="text/css" media="all"  />
    <link href="{{url('../public/assets/css/navi.css')}}" rel="stylesheet" type="text/css" media="all" />
    <title>@yield('title')</title>
    <script src="{{url('../public/plugins/jquery/3.6.0/jquery.min.js')}}"></script> 
</head>
<body>
    <nav class="navbar navbar-expand-lg nav-custom py-0">
        <a class="navbar-brand" href="/navibot/audit-report/">
            {{--  <img src="/navibot/public/assets/img/logo-white.png" height="40" class="d-inline-block align-top" alt="Amnet Logo">  --}}
            <div class="row d-inline-block ">
                <div class="col">
                    <div class="d-inline-block mt-2 h3">Navi<span class="bold text-warning">B</span>ot</div>
                </div>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            
            <ul class="navbar-nav ml-auto">
                <li>
                    <a class="nav-link" href="{{url('/')}}">File Upload</a>
                </li>
                <li>
                    <a class="nav-link" href="{{route('report')}}">Report</a>
                </li>
                <li>
                    <a class="nav-link"  href="" id="summary_link">Summary</a>
                </li>
            </ul>
        </div>
    </nav>
    <main class="dashboard" id="dashboard">
        <div class="content d-flex flex-column flex-column-fluid" style="margin-bottom: 25px;">
            @yield('content')
        </div>
    </main>
    <div id="global_loader_div">
        <div class="back-overlay"></div>
        <div class="loading-box text-center">
            <i class="fas fa-sync fa-spin fa-1x"></i>
            <div class="loader-text"></div>
        </div>
    </div>
    <div id="global_loader_div">
        <div class="back-overlay"></div>
        <div class="loading-box text-center">
            <i class="fas fa-sync fa-spin fa-1x"></i>
            <div class="loader-text"></div>
        </div>
    </div>
    <style>
        body{
            font-size: 14px;
            font-family: "Roboto", sans-serif;
            overflow: hidden;
            overflow-y: auto;
        }
        
    </style>
    <footer class="footer fixed-bottom text-center bg-blue">
        <div class="container py-2 text-white">
                Powered by Amnet | &copy; <?php echo date("Y"); ?> Copyright: Amnet. All Rights Reserved
        </div>
    </footer>
    <script src="{{url('../public/plugins/popper/2.10.2/popper.min.js')}}"></script>
    <script src="{{url('../public/assets/bootstrap/4.6.0/bootstrap.min.js')}}"></script>
    <script src="{{url('../public/plugins/sweet-alert-2/sweet-alert-min.js')}}"></script>
    {{--  custom scripts  --}}
    {{-- <script src="/navibot/public/assets/js/common.js?v=0.5"></script> --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.min.js"></script> --}}



    <script src="{{url('/public/assets/js/jquery.form.min.js')}}"></script>
    <script src="{{url('/public/assets/plugins/chartjs/3.6.2/chart.min.js')}}"></script>
    <script src="{{url('/public/assets/plugins/chartjs/3.6.2/chartjs-plugin-datalabels.min.js')}}"></script>
    <script src="{{url('/public/assets/js/audit-report.js?v=2.0.1')}}"></script>
</body>
</html>