@extends('layouts.app')
@section('title') {{'Import Aduit Report - NaviBot'}} @endsection
@section('content')
<div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
    <div class="container d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
        <div class="d-flex align-items-center flex-wrap mr-1">
            <div class="d-flex flex-column">
                <h1 class="font-weight-bold my-2 mr-5 fs-20px">Import Audit Report</h1>
            </div>
        </div>
        <div class="d-flex align-items-center">
            {{-- <a href="/navibot/web-navigator/sales/dashboard" class="btn bg-blue font-weight-bold ml-2 text-white">Dashboard</a> --}}
        </div>
    </div>
</div>
<div class="d-flex justify-content-center" style="min-height: 70vh">
    <div class="align-self-center align-self-center w-100">
        <div class="row">
            <div class="col-xl-8 offset-xl-2 mb-5">
                <div class="card shadow">
                    <div class="card-body">
                        <form id="fileUploadForm" method="POST" action="{{ route('file-upload') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="file" class="ml-2 bold">Select Audit Report Excel File</label>
                                <input name="file" type="file" id='file' class="form-control border-0" accept=".xlsx, .xls">
                            </div>
                            
                            <div class="form-group d-none" id="progressbar">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-blue" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                        <span class="bold" id="progress_label">0%</span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <input type="submit" value="Submit" class="btn bg-blue text-white w-25">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection