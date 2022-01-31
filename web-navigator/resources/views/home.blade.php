{{--  @extends('layouts.navi')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection  --}}
@extends('layouts.navi')
@section('title') {{'Home - NaviBot::Amnet-Systems'}} @endsection
@section('content')
<div class="container">
    <div class="d-flex justify-content-center h-100" style="min-height: 80vh; width:100%">
        <div class="align-self-center w-100">
            <div class="row g-5 g-xl-8">
                <div class="col-xl-10 offset-xl-1">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-left">
                                <h1 class="fs-20 mb-3">Welcome!</h1>

                            </div>
                        </div>
                        <div class="col-xl-4">
                            <a href="dashboard" class="card bg-dark hoverable card-xl-stretch mb-xl-8">
                                <div class="card-body">
                                    {{--  <span class="svg-icon svg-icon-gray-100 svg-icon-3x ms-n1" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path opacity="0.3" d="M18 21.6C16.3 21.6 15 20.3 15 18.6V2.50001C15 2.20001 14.6 1.99996 14.3 2.19996L13 3.59999L11.7 2.3C11.3 1.9 10.7 1.9 10.3 2.3L9 3.59999L7.70001 2.3C7.30001 1.9 6.69999 1.9 6.29999 2.3L5 3.59999L3.70001 2.3C3.50001 2.1 3 2.20001 3 3.50001V18.6C3 20.3 4.3 21.6 6 21.6H18Z" fill="black"></path>
                                            <path d="M12 12.6H11C10.4 12.6 10 12.2 10 11.6C10 11 10.4 10.6 11 10.6H12C12.6 10.6 13 11 13 11.6C13 12.2 12.6 12.6 12 12.6ZM9 11.6C9 11 8.6 10.6 8 10.6H6C5.4 10.6 5 11 5 11.6C5 12.2 5.4 12.6 6 12.6H8C8.6 12.6 9 12.2 9 11.6ZM9 7.59998C9 6.99998 8.6 6.59998 8 6.59998H6C5.4 6.59998 5 6.99998 5 7.59998C5 8.19998 5.4 8.59998 6 8.59998H8C8.6 8.59998 9 8.19998 9 7.59998ZM13 7.59998C13 6.99998 12.6 6.59998 12 6.59998H11C10.4 6.59998 10 6.99998 10 7.59998C10 8.19998 10.4 8.59998 11 8.59998H12C12.6 8.59998 13 8.19998 13 7.59998ZM13 15.6C13 15 12.6 14.6 12 14.6H10C9.4 14.6 9 15 9 15.6C9 16.2 9.4 16.6 10 16.6H12C12.6 16.6 13 16.2 13 15.6Z" fill="black"></path>
                                            <path d="M15 18.6C15 20.3 16.3 21.6 18 21.6C19.7 21.6 21 20.3 21 18.6V12.5C21 12.2 20.6 12 20.3 12.2L19 13.6L17.7 12.3C17.3 11.9 16.7 11.9 16.3 12.3L15 13.6V18.6Z" fill="black"></path>
                                        </svg>
                                    </span>  --}}
                                    
                                    <i class="fa fa-th-large fa-3x text-white" aria-hidden="true"></i>

                                    <div class="text-gray-100 fw-bolder fs-2 my-3">Dashboard</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-4">
                            <a href="web-navigator" class="card bg-blue hoverable card-xl-stretch mb-xl-8">
                                <div class="card-body">
                                    <i class="fa fa-spider fa-3x text-white" aria-hidden="true"></i>
                                    <div class="text-white fw-bolder fs-2 my-3">Web Navigator</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xl-4">
                            <a href="image-extract" class="card bg-info hoverable card-xl-stretch mb-5 mb-xl-8">
                                <div class="card-body">
                                    <i class="fa fa-image fa-3x text-white" aria-hidden="true"></i>
                                    <div class="text-white fw-bolder fs-2 my-3">Image Extractor</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
@endsection