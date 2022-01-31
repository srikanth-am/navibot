@extends('layouts.app')
@section('title') {{'Dashboard - NaviBot'}} @endsection
@section('content')
@php
$summary = [];
$data = Session::get('summary');
$summary = ($data && count($data)) ? $data : [];

@endphp
{{-- <pre>{{print_r($summary)}}</pre> --}}
<div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <div class="d-flex align-items-center flex-wrap mr-1">
            <div class="d-flex flex-column">
                <h1 class="font-weight-bold my-2 mr-5 fs-20px">Web Audit Summary Report</h1>
            </div>
        </div>
        <div class="d-flex align-items-center">
            @if(count($summary))
            <a href="{{route('export-as-pdf')}}" class="btn bg-blue text-white btn-sm" aria-label="Download report as PDF" >Download As PDF</a>
            @endif
        </div>
    </div>
</div>
<div class="container-fluid mt-1 mb-5">
    
    @if(count($summary))
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-header text-center" ><h1 style="font-size: 20px; margin-bottom:0">Web Audit Summary Report - {{$summary['Website']}}</h1></div>
                <div class="card-body">
                    <div class="row g-5 g-xl-8">
                        <div class="col">
                            <div class="card bg-light card-xl-stretch">
                                <div class="card-body">
                                    <div class="d-flex align-items-center bg-light-warning rounded mb-7">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Date</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{ ($summary['Date']) ? $summary['Date'] : \Carbon\Carbon::now()->format("d-M-Y")}}</span>
                                    </div>
                                    <div class="d-flex align-items-center bg-light-warning rounded mb-7">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Website</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Website']}}</span>
                                    </div>
                                    <div class="d-flex align-items-center bg-light-warning rounded mb-7">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Total Working URLs</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Total Working URLs']}}</span>
                                    </div>
                                    <div class="d-flex align-items-center bg-light-warning rounded mb-7">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Total Not Working URLs</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Total Not Working URLs']}}</span>
                                    </div>
                                    <div class="d-flex align-items-center bg-light-warning rounded mb-7">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Total URLs of the website</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Total URLs of the website']}}</span>
                                    </div>
                                    <div class="d-flex align-items-center bg-light-warning rounded mb-7">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Total Templates</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Total Templates']}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card bg-light card-xl-stretch">
                                <div class="card-body">
                                    <div class="d-flex align-items-center bg-light-warning rounded">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Standard</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Standard']}}</span>
                                    </div>
                                    <div class="d-flex align-items-center bg-light-warning rounded">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Platform</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Platform']}}</span>
                                    </div>
                                    <div class="d-flex align-items-center bg-light-warning rounded">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Assistive Technology</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Assistive Technology']}}</span>
                                    </div>
                                    <div class="d-flex align-items-center bg-light-warning rounded">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Automation Testing Tools</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Automation Testing tools']}}</span>
                                    </div>
                                    <div class="d-flex align-items-center bg-light-warning rounded">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Total Web Audit URLs</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Total Web Audit URLs']}}</span>
                                    </div>
                                    <div class="d-flex align-items-center bg-light-warning rounded">
                                        <div class="flex-grow-1">
                                            <span class="text-blue fw-bold d-block">Total Issues Found in Audit URLs</span>
                                        </div>
                                        <span class="fw-bolder  py-1">{{$summary['Total Issues Found in Audit URLs']}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-5">
            <div class="card">
                <div class="card-header h6 text-center bg-blue text-white">WCAG Issues</div>
                <div class="card-body py-2">
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="card bg-blue text-white">
                                <div class="card-body text-center py-2">
                                    <div class="bold" id="wcag_issues" style="font-size: 22px">
                                        00
                                    </div>
                                    <div class="bold">Total Issues</div> 
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-blue text-white">
                                <div class="card-body text-center py-2">
                                    <div class="bold" id="wcag_issues_per_page" style="font-size: 22px">
                                        00
                                    </div>
                                    <div class="bold">Issues Per Page</div> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="wcag_issues_chart" class="mx-auto">
                        <div id="chart_div" style="max-width: 100%;width:100%">
                            <canvas id="wcag_issues_chart_canvas"></canvas>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-7">
            <div class="card">
                <div class="card-header h6 text-center bg-blue text-white">User Impact</div>
                <div class="card-body py-2">
                    <div id="user_impact_chart" style="height: 310px;max-width:660px">
                        <canvas id="user_impact_chart_canvas" width="100%" height="auto"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 mt-3">
            <div class="card">
                <div class="card-header text-center" ><h1 style="font-size: 20px; margin-bottom:0">Indicative Top 10 WCAG Issues for Audited URLs</h1></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header h6 text-center bg-blue text-white">WCAG 2.0</div>
                                <div class="card-body py-2">
                                    <div id="wcag_top_ten_2_0_chart" style="height: 310px">
                                        <canvas id="wcag_top_ten_2_0_chart_canvas" width="100%" height="auto"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header h6 text-center bg-blue text-white">WCAG 2.1</div>
                                <div class="card-body py-2">
                                    <div id="wcag_top_ten_2_1_chart" style="height: 310px"> 
                                        <canvas id="wcag_top_ten_2_1_chart_canvas" width="100%" height="auto"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-lg-12 mt-3">
            <div class="card">
                <div class="card-header text-center" ><h1 style="font-size: 20px; margin-bottom:0">Conformance by WCAG Success Criteria - Version 2.0 & 2.1</h1></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header h6 text-center bg-blue text-white">WCAG Level A</div>
                                <div class="card-body py-2">
                                    <div id="wcag_sc_lavel_a_chart" style="height: 740px">
                                        <canvas id="wcag_sc_lavel_a_chart_canvas" width="100%" height="auto"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header h6 text-center bg-blue text-white">WCAG Level AA</div>
                                <div class="card-body py-2">
                                    <div id="wcag_sc_lavel_aa_chart" style="height: 740px">
                                        <canvas id="wcag_sc_lavel_aa_chart_canvas" width="100%" height="auto"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @else
    <div class="d-flex justify-content-center" style="min-height: 70vh">
        <div class="align-self-center align-self-center w-100">
            <div class="row">
                <div class="col-xl-8 offset-xl-2 mb-5">
                    <div class="card shadow bg-blue text-white">
                        <div class="card-body">
                            <div class="text-center">
                                <h1 style="font-size: 20px">Please Upload the file</h1>
                                <a href="{{url('/')}}" class="btn bg-white">Go to file upload</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
{{-- Scripts --}}
<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }

        });
        var summary_data_count = "{{count($summary)}}";
        if(summary_data_count > 0){
            wcag_issues_chart();//chart - 1
            user_impact_chart();//chart - 2
            wcag_top_ten_2_0_chart(); //chart - 3
            wcag_top_ten_2_1_chart(); //chart - 4
            wcag_sc_lavel_a_chart(); //chart - 5
            wcag_sc_lavel_aa_chart(); //chart - 6
        }
        
    });
</script>
@endsection