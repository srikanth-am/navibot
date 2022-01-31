@extends('layouts.auth')
@section('title') {{'Reset Password - NaviBot::Web Navigator'}} @endsection
@section('content')
    <div class="">
        <div class="text-center">
            <h3 class="mb-4 fw-600">{{ __('Reset Password') }}</h3>
            <div class="heading_divider"></div>
        </div>
    </div>
    <form class="form" method="POST" id="reset_form">
        @csrf
        <input type="hidden" id="token" name="token" value="{{ $token }}">
        <div class="form-group mb-3">
            <label class="label" for="email">Email</label>
            <input type="email" class="form-control" placeholder="" id="email" value="{{ $email ?? old('email') }}" />
        </div>
        <div class="form-group mb-3">
            <label class="label" for="password">Password</label>
            <input type="password" class="form-control" name="password" id="password" />
        </div>
        <div class="form-group mb-3">
            <label class="label" for="cpassword">Confirm Password</label>
            <input type="password" name="confirm password" class="form-control" id="cpassword" />
        </div>
        <div class="form-group">
            <div class="text-right">
                <a href="{{route('login')}}" class="btn btn-danger btn-lg">Cancel</a>
                <button type="submit" class="btn btn-primary submit btn-lg ml-2" id="reset_submit_btn">{{ __('Reset Password') }}</button>
            </div>
        </div>
    </form>
@endsection