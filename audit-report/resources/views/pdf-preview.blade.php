@extends('layouts.app')
@section('title') {{'Dashboard - NaviBot'}} @endsection
@section('content')
    @php
    $summary = [];
    $data = Session::get('summary');
    $summary = ($data && count($data)) ? $data : [];

    @endphp
    
    <div class="container-fluid mt-2 mb-5" style="overflow: hidden;overflow-y:auto">
        @if(count($summary))
        <div class="row">
            <div class="col-12 mb-2">
                <div class="card">
                    <div class="card-header text-center" ><h1 style="font-size: 18px; margin-bottom:0;line-height:1;font-weight:bold;">Web Audit Summary Report - {{$summary['Website']}}</h1></div>
                    <div class="card-body py-2">
                        <div class="row g-5 g-xl-8">
                            <div class="col">
                                <div class="card bg-light card-xl-stretch">
                                    <div class="card-body py-1">
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
                                    <div class="card-body py-1">
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
                    <div class="card-header text-center bg-blue text-white" style="font-size: 14px; font-weight:bold;line-height:1">WCAG Issues</div>
                    <div class="card-body py-2">
                        <div class="row mb-2">
                            <div class="col">
                                <div class="card bg-blue text-white">
                                    <div class="card-body text-center py-2">
                                        <div class="bold" id="wcag_issues" style="font-size: 20px">
                                            {{$summary['total_wcag_issues']}}
    
                                        </div>
                                        <div class="bold">Total Issues</div> 
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-blue text-white">
                                    <div class="card-body text-center py-2">
                                        <div class="bold" id="wcag_issues_per_page" style="font-size: 20px">
                                            {{$summary['avg_wcag_issues']}}
                                        </div>
                                        <div class="bold">Issues Per Page</div> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="wcag_issues_chart" class="text-center">
                            <div id="chart_div" style="height: 190px">
                                <img src="{{url('/public/assets/img/chart_1.png')}}" alt="chart_1" style="height: 180px">
                            </div>
                            {{-- <canvas id="wcag_issues_chart_canvas"></canvas> --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-7">
                <div class="card">
                    <div class="card-header h6 text-center bg-blue text-white" style="font-size: 14px; font-weight:bold;line-height:1">User Impact</div>
                    <div class="card-body py-2">
                        <div id="user_impact_chart"  class="text-center" style="height: 265px">
                            <img src="{{url('/public/assets/img/chart_2.png')}}" alt="chart_2" style="height:255px">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 mt-2">
                <div class="card">
                    <div class="card-header text-center " ><h1 style="font-size: 14px; font-weight:bold;line-height:1;margin-bottom:0">Top 10 WCAG Issues</h1></div>
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header h6 text-center bg-blue text-white" style="font-size: 14px; font-weight:bold;line-height:1">WCAG 2.0</div>
                                    <div class="card-body py-2">
                                        <div id="wcag_top_ten_2_0_chart" class="text-center" style="height: 250px">
                                            <img src="{{url('/public/assets/img/chart_3.png')}}" alt="chart_3" style="height: 240px">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header h6 text-center bg-blue text-white" style="font-size: 14px; font-weight:bold;line-height:1">WCAG 2.1</div>
                                    <div class="card-body py-2">
                                        <div id="wcag_top_ten_2_1_chart" class="text-center" style="height: 250px"> 
                                            <img src="{{url('/public/assets/img/chart_4.png')}}" alt="chart_4" style="height:240px">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
            </div>
            
            <div class="col-lg-12 mt-2">
                <div class="card">
                    <div class="card-header text-center" ><h1 style="font-size: 14px; font-weight:bold;line-height:1;margin-bottom:0">Conformance by WCAG Success Criteria - Version 2.0 & 2.1</h1></div>
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header h6 text-center bg-blue text-white">WCAG Level A</div>
                                    <div class="card-body py-2">
                                        <div id="wcag_sc_lavel_a_chart" class="text-center" style="height: 450px">
                                            <img src="{{url('/public/assets/img/chart_5.png')}}" alt="chart_5" style="height:450px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header h6 text-center bg-blue text-white">WCAG Level AA</div>
                                    <div class="card-body py-2">
                                        <div id="wcag_sc_lavel_aa_chart" class="text-center" style="height:450px;">
                                            <img src="{{url('/public/assets/img/chart_6.png')}}" alt="chart_6" style="height:450px;">
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
        No valid Information found.
        @endif
    </div>
    

@endsection