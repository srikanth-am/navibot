@extends('layouts.navi')

@section('content')
<div class="container">
    <div class="d-flex justify-content-center" style="min-height: 80vh">
        <div class="align-self-center align-self-center w-100">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card bg-blue border-0 text-white shadow-lg">
                        <div class="card-header bg-blue font-weight-bolder text-white"><i class="fa fa-exclamation-triangle mr-2 text-white"></i>{{ __('Verify Your Email Address') }}</div>

                        <div class="card-body">
                            @if (session('resent'))
                                <div class="alert alert-success" role="alert">
                                    {{ __('A fresh verification link has been sent to your email address.') }}
                                </div>
                            @endif

                            {{ __('Before proceeding, please check your email for a verification link.') }}
                            <!-- {{ __('If you did not receive the email') }} -->
                        </div>
                        <div class="card-footer text-center border-top">
                            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                @csrf
                                <button type="submit" class="btn bg-white bold">{{ __('Resend Verification Email') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>   
@endsection
