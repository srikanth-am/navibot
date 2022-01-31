@extends('layouts.auth')
@section('title') {{'Sign In - NaviBot::Web Navigator'}} @endsection
@section('content')
    <div class="">
        <div class="text-center">
            <h3 class="fw-600">Sign In</h3>
            <p>New Account? <a class="" href="{{route('signup')}}">Sign Up</a></p>
            <div class="heading_divider"></div>
        </div>
    </div>
    <form class="form" method="POST" id="login_form">
        @csrf
        <div class="form-group mb-3">
            <label class="label" for="name">Email</label>
            <input type="email" class="form-control" placeholder="" id="email" />
        </div>
        <div class="form-group mb-3">
            <label class="label" for="password">Password</label>
            <input type="password" class="form-control" id="password" />
        </div>
        <div class="form-group d-md-flex">
            <div class="w-50 text-left">
                <button type="submit" class="btn btn-primary submit btn-lg btn-block font-weight-bold" id="login_submit_btn">Sign In</button>
            </div>
            <div class="w-50 text-md-right">
                <a href="{{route('password.request')}}" class="btn btn-link font-weight-bold" style="font-size: 15px;vertical-align: middle;text-align: center;margin-top: 2px;">Forgot Password</a>
            </div>
        </div>
    </form>
@endsection




