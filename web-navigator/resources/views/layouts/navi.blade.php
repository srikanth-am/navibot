<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet" />
    <link rel="apple-touch-icon" sizes="180x180" href="/navibot/public/assets/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/navibot/public/assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/navibot/public/assets/img/favicon-16x16.png">
    <link href="/navibot/web-navigator/public/assets/bootstrap/4.6.0/bootstrap.min.css" rel="stylesheet">
    <link href="/navibot/public/plugins/sweet-alert-2/sweet-alert-min.css" rel="stylesheet">
    <link href="/navibot/public/plugins/font-awesome/5.15.4/css/all.css" rel="stylesheet" />
    <link href="/navibot/public/plugins/datatables/1.10.16/datatables.min.css" rel="stylesheet" />
    <link href="/navibot/web-navigator/public/assets/css/navi.css?v="<?= rand();?> rel="stylesheet" />
    <title>@yield('title')</title>
    <script src="/navibot/public/plugins/jquery/3.6.0/jquery.min.js"></script> 
</head>
<body>
    <nav class="navbar navbar-expand-lg nav-custom py-0">
        <a class="navbar-brand" href="/navibot/web-navigator/">
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
                <!-- Authentication Links -->
                @if(Auth::user()->name)
                @if(Auth::user()->role_id == 1)
                <li>
                    <a href="{{route('settings')}}" class="btn btn-light mr-2 shadow-lg font-weight-bold border border-warning">
                        <i class="fa fa-cog mr-1 text-blue" aria-hidden="true"></i> Control Pannel</a>
                </li>
                @endif
                <li class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle text-capitalize" href="#" role="button"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        Hi, {{ Auth::user()->name }}
                    </a>

                    <div class="dropdown-menu p-0 m-0 dropdown-menu-left dropdown-menu-anim-up dropdown-menu-lg" aria-labelledby="navbarDropdown">
                        <div class="d-flex flex-column flex-center py-10 bgi-size-cover bgi-no-repeat rounded-top" style="background-image: url(/navibot/web-navigator/public/assets/img/bg-1.jpg)">
                            <h5 class="text-white font-weight-bold mt-5">{{ Auth::user()->name }}</h5>
                            <span class="label label-xl label-white font-weight-bold text-dark label-inline ">
                                {{\App\Models\User::role()}}
                            </span>

                        </div>
                        <div class="px-4 py-2">
                            <div class="font-weight-bold text-blue mb-2">Email</div>
                            <div class="font-weight-bold text-dark">{{Auth::user()->email}}</div>
                        </div>
                        <hr class="m-1">
                        <div class="px-4 py-2 text-center">
                            <a class="btn btn-danger mb-2 font-weight-bold" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out-alt mr-1 text-white" aria-hidden="true"></i>{{ __('Logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                </li>
                
                @else
                    session expired
                @endif
            </ul>
            
        </div>
    </nav>
    <main class="">
        <div class="content d-flex flex-column flex-column-fluid">
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
    <footer class="footer fixed-bottom text-center bg-blue">
        <div class="container py-2 text-white">
                Powered by Amnet | &copy; <?php echo date("Y"); ?> Copyright: Amnet. All Rights Reserved
        </div>
    </footer>
    <script src="/navibot/public/plugins/popper/2.10.2/popper.min.js"></script>
    <script src="/navibot/web-navigator/public/assets/bootstrap/4.6.0/bootstrap.min.js"></script>
    <script src="/navibot/public/plugins/sweet-alert-2/sweet-alert-min.js"></script>
    <script src="/navibot/public/plugins/datatables/1.10.16/datatables.min.js"></script>
    {{--  custom scripts  --}}
    <script src="/navibot/public/assets/js/common.js?v=0.5"></script>
    <script src="/navibot/web-navigator/public/assets/js/my-script.js?v=0.5"></script>
</body>
</html>