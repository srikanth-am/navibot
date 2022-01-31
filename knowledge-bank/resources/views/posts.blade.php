@extends('layouts.myapp')
@section('title') {{'Dashboard - NaviBot::Amnet-Systems'}} @endsection
@section('content')
<div class="container-fluid">
    <div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex flex-column">
                    <h1 class="font-weight-bold mt-2 fs-20px">Posts</h1>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn bg-blue text-white font-weight-bold ml-2" id="refresh"><i class="fa fa-sync-alt mx-1 text-white" aria-hidden="true"></i>Refresh</button>
                <button class="btn bg-blue text-white font-weight-bold mx-2" type="button" data-bs-toggle="collapse" data-bs-target="#filters" aria-expanded="false" aria-controls="filters">
                    <i class="fa fa-filter mx-1 text-white" aria-hidden="true"></i>Filters
                </button>
            </div>
        </div>
    </div>
    <div class="collapse" id="filters">
        <div class="card bg-blue">
            <div class="card-body">
                <div class="text-white h5">Filters</div>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-floating">
                            <select class="form-select" id="floatingSelect" name="category" aria-label="">
                            <option selected>Open this select menu</option>
                            <option value="1">Web Accessibility</option>
                            <option value="2">Document Accessibility</option>
                            <option value="3">Multimedia Accessibility</option>
                            </select>
                            <label for="floatingSelect" class="text-dark opacity-100 text-blue bold">Category</label>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="created_by" id="created_by" placeholder="Name or Email" >
                            <label for="created_by" class="text-dark opacity-100 text-blue bold">Created By</label>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-floating">
                            <select class="form-select" id="floatingSelect" name="category" aria-label="">
                            <option selected value="DESC">Descending</option>
                            <option value="ASC">Ascending</option>
                            </select>
                            <label for="floatingSelect" class="text-dark opacity-100 text-blue bold">Sort By</label>
                        </div>
                    </div>
                    <div class="col-lg-3 justify-content-between d-flex">
                        <button class="btn btn-lg bg-white text-blue" style="width: 45%">Submit</button>
                        <button class="btn btn-lg btn-danger" style="width: 45%">Clear</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="post-lists mt-2 mb-5">
        <div class="row">
            @for($i=0;$i<=10;$i++)
            <div class="col-3 mb-4">
                <div class="card">
                    <img src="https://amnet-systems.com/wp-content/uploads/2020/07/Pivoting-To-Video-Lessons-Learned-image-300x156.jpg" class="card-img-top" alt="...">
                    <div class="card-body">
                      <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                    </div>
                </div>

            </div>
            @endfor
        </div>
    </div>
</div>
@endsection