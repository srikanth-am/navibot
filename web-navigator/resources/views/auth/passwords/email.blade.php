@extends('layouts.auth')
@section('title') {{'Forgot Password - NaviBot::Web Navigator'}} @endsection
@section('content')
    <div class="">
        <div class="text-center">
            <h3 class="mb-4 fw-600">Reset Password</h3>
            <div class="heading_divider"></div>
        </div>
    </div>
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    <form method="POST" id="forgot_pwd">
        @csrf
        <div class="form-group mb-3">
            <label class="label" for="email">{{ __('Email') }}</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary" id="forgot_submit_btn">
                {{ __('Send Password Reset Link') }}
            </button>
        </div>
        {{--  <div class="form-group row">
            <label for="email" class="col-md-4 col-form-label text-md-right"></label>

            <div class="col-md-6">
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="form-group row mb-0">
            <div class="col-md-6 offset-md-4">
               
            </div>
        </div>  --}}
    </form>
@endsection