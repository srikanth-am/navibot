@extends('layouts.navi')
@section('title') {{'URL Extractor - NaviBot::Amnet-Systems'}} @endsection
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
                <a href="/navibot/web-navigator/dashboard" class="btn bg-blue font-weight-bold ml-2 text-white">Dashboard</a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-8 offset-xl-2 mb-5">
            <div class="card">
                <div class="card-body">
                    <form id="extract_urls" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="url" class="fw-bold">Website URL</label>
                            <input type="url" class="form-control" placeholder="Enter Website URL ex: https://example.com" id="url">
                        </div>
                        <div class="form-group form-check">
                            <label class="form-check-label fw-bold">
                                <input class="form-check-input" type="checkbox" id="query_str"> Remove Query Strings
                            </label>
                        </div>
                        <p class="text-center fc-blue fw-bold fs-5 mb-0">
                            <i class="fa fa-info-circle fc-blue fs-5" aria-hidden="true"></i>
                            Note
                        </p>
                        <div class="heading_divider"></div>
                        <div class="accordion accordion-toggle-arrow my-4" id="accordionExample4">
													
                            <div class="card">
                                <div class="card-header bg-light" id="headingThree4">
                                    <a class="card-title collapsed font-weight-bolder mb-0" data-toggle="collapse" data-target="#collapseThree4" href="" tabindex="0">
                                    What are query string parameters?</a>
                                </div>
                                <div id="collapseThree4" class="collapse" data-parent="#accordionExample4">
                                    <div class="card-body">
                                        <p>
                                            On the internet, a Query string is the part of a link (otherwise known as a hyperlink or a uniform resource locator, URL for short) which assigns values to specified attributes (known as keys or parameters).
                                            Typical link containing a query string is as follows: <b>http://example.com/over/there?name=ferret</b>
                                        </p>
                                        <p>Query strings allow information to be sent to a webpage in a way that can be easily ingested and used within the webpage.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <a href="/navibot/web-navigator/dashboard" class="btn btn-danger fw-bold mr-3">Cancel</a>
                            <button type="submit" class="btn bg-blue text-white fw-bold">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection