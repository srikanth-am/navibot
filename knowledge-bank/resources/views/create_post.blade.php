@extends('layouts.myapp')
@section('title') {{'Create Post - NaviBot::Knowledge Bank'}} @endsection
@section('content')
<div class="container-fluid mb-5">
    <div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex flex-column">
                    <h1 class="font-weight-bold mt-2 fs-20px">Create Posts</h1>
                </div>
            </div>
            
        </div>
    </div>
    <div class="row">
        <div class="col-7 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form>
                        <div class="mb-2">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="name">
                          </div>
                        <div class="mb-2">
                          <label for="email" class="form-label">Email address</label>
                          <input type="email" name="email" class="form-control" id="email">
                        </div>
                        <div class="mb-2">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" aria-label="">
                                <option selected>Open this select menu</option>
                                <option value="1">Web Accessibility</option>
                                <option value="2">Document Accessibility</option>
                                <option value="3">Multimedia Accessibility</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" id="title">
                        </div>
                        <div class="mb-2">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" class="form-control" name="" id="description" cols="10" rows="5"></textarea>
                        </div>
                        <div class="mb-2">
                            <label for="link" class="form-label">Link</label>
                            <input type="text" name="link" class="form-control" id="link">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                      </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection