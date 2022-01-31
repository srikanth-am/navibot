<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link rel="apple-touch-icon" sizes="180x180" href="/navibot/public/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/navibot/public/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/navibot/public/assets/img/favicon-16x16.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link href="/navibot/public/plugins/font-awesome/5.15.4/css/all.css" rel="stylesheet">
    <link href="/navibot/public/plugins/sweet-alert-2/sweet-alert-min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="/navibot/public/assets/css/auth.css" rel="stylesheet">
    <!-- Scripts -->
    <script src="/navibot/public/plugins/jquery/3.6.0/jquery.min.js"></script>  
    <script src="{{ asset('js/app.js') }}" defer></script>
    
</head>
<body class="navi-auth-body">
    <main class="main">
        <div class="container">
            <div class="d-flex justify-content-center" style="min-height: 100vh">
                <div class="align-self-center align-self-center w-100">
                    <div class="row justify-content-center">
                        <div class="col-md-12 col-lg-10">
                            <div class="d-inline" style="font-size: 18px;">
                                <a class="font-weight-bold fs-20px" href="/navibot" style="color: #273C75;font-size:18px">NaviBot</a>
                                    <span class="font-weight-bold mt-2 fa fa-chevron-right" style="font-size: 16px;"></span>
                                    <span class="font-weight-bold mt-2 fs-20px" style="opacity: .9">Web Navigator</span>
                            </div>
                            <div class="box d-block d-md-flex mt-3 mb-5">
                                <div class="box-left-bg p-4 p-lg-5 text-center d-flex align-items-center w-100 w-md-50">
                                    <div class="text-center w-100">
                                        <img src="/navibot/public/assets/img/logo-white.png" height="55" class="d-inline-block align-top mb-3" alt="Amnet Logo">
                                        <h2>Welcome to NaviBot</h2>
                                    </div>
                                </div>
                                <div class="box-right-bg login-wrap p-4 p-lg-5 w-100 w-md-50">
                                    
                                    @yield('content')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
    
    <script src="/navibot/public/plugins/sweet-alert-2/sweet-alert-min.js"></script>
    <script src="/navibot/web-navigator/public/assets/js/common.js?v=0.5"></script>
    <script src="/navibot/web-navigator/public/assets/auth/auth.js" type="text/javascript"></script>

    
</body>
</html>

