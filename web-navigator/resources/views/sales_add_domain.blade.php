@extends('layouts.navi')
@section('title') {{'Create Domain - NaviBot::Amnet-Systems'}} @endsection
@section('content')
<div class="container-fluid">
    <div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
        <div class="container d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex flex-column">
                    <h1 class="font-weight-bold my-2 mr-5 fs-20px">Create Domain</h1>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <a href="/navibot/web-navigator/sales/dashboard" class="btn bg-blue font-weight-bold ml-2 text-white">Dashboard</a>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-center" style="min-height: 70vh">
        <div class="align-self-center align-self-center w-100">
            <div class="row">
                <div class="col-xl-8 offset-xl-2 mb-5">
                    <div class="card shadow">
                        <div class="card-body">
                            <form id="sales_add_domain" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="url" class="fw-bold">Website URL</label>
                                    <input type="url" class="form-control" placeholder="Enter Website URL ex: https://example.com" id="domain">
                                </div>
                                <div class="text-right">
                                    <a href="/navibot/web-navigator/sales/dashboard" class="btn btn-danger fw-bold mr-3">Cancel</a>
                                    <button type="submit" class="btn bg-blue text-white fw-bold">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection