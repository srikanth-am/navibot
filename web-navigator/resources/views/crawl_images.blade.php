@extends('layouts.navi')
@section('title') {{'Image Extractor - NaviBot::Amnet-Systems'}} @endsection
@section('content')
<div class="container-fluid">
    <div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
        <div class="container d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex flex-column">
                    <h1 class="font-weight-bold my-2 mr-5 fs-20px">Image Extractor</h1>
                    <div class="d-flex align-items-center font-weight-bold my-2">
                        <a href="/navibot/home" class="fc-blue">Home</a>
                        <span class="label label-dot label-sm bg-blue mx-3"></span>
                        <span class="fc-blue">Image Extractor</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-8 offset-xl-2 mb-5">
            <div class="card">
                <div class="card-body">
                    <form id="extract_images">
                        <div class="form-group">
                            <label for="url" class="fw-bold">Website URL</label>
                            <input type="url" class="form-control" placeholder="Enter Website URL ex: https://example.com" id="url">
                        </div>
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <p class="text-center fc-blue fw-bold fs-5 mb-0">
                                    <i class="fa fa-info-circle fc-blue fs-5" aria-hidden="true"></i>
                                    Note
                                </p>
                                <div class="heading_divider"></div>
                                <p class="mt-2">Need to add the information about it only run up to 500 urls</p>
                            </div>
                        </div>
                        <button type="submit" class="btn bg-blue btn-block text-white fw-bold">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection