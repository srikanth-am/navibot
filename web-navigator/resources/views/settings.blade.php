@extends('layouts.navi')
@section('title') {{'Settings - NaviBot::Amnet-Systems'}} @endsection
@section('content')
<div class="container-fluid">
    <div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex flex-column">
                    <h1 class="font-weight-bold mt-2 fs-20px">Settings</h1>
                    
                </div>
            </div>
        </div>
    </div>
    {{--  --}}
    <div class="d-flex justify-content-center" style="min-height: 70vh">
        <div class="align-self-center align-self-center w-100">
            <div class="row justify-content-center">
                <div class="col-md-3">
                    <a href="{{route('users')}}" class="text-dark">
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Users</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{route('user-roles')}}" class="text-dark">
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">User Roles</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{route('sales-domains')}}" class="text-dark">
                        <div class="info-box">
                            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>
                            <div class="info-box-content">
                            <span class="info-box-text">Sales Team Domains</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection