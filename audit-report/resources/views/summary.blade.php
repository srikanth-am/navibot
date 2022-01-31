@extends('layouts.app')
@section('title') {{'Summary - Audit Report::NaviBot'}} @endsection
@section('content')
<style>
    .table {
        width: 100%;
        margin-bottom: 1rem;
        color: #273c75;
        border-collapse: inherit;
    }
</style>
<div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
        <div class="d-flex align-items-center flex-wrap mr-1">
            <div class="d-flex flex-column">
                <h1 class="font-weight-bold my-2 mr-5 fs-20px">Summary</h1>
            </div>
        </div>
        <div class="d-flex align-items-center">
            @if(count($data))
            <a href="{{url('export-summary')}}/{{request()->route('file')}}" class="btn bg-blue text-white btn-sm" aria-label="Download summary as Excel" >Download</a>
            @endif
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header text-center">
           <div class="h6">Summary - {{$data['summary']['Website']}}</div>
            <div class="info">
                <span class="mr-2 bold">Standard: <span class="bold">{{$data['summary']['Standard']}}</span></span> |
                <span class="mx-2 bold">Total Templates: <span class="bold">{{$data['summary']['Total Templates']}}</span></span> |
                <span class="mx-2 bold">Total Web Audit URLs: <span class="bold">{{$data['summary']['Total Web Audit URLs']}}</span></span> |
                <span class="ml-2 bold">Total Issues found in Audit URLs: <span class="bold">{{$data['summary']['Total Issues Found in Audit URLs']}}</span></span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="text-center">
                        <tr>
                            <th class="align-middle" rowspan="2" colspan="1">#</th>
                            <th class="align-middle" rowspan="2" colspan="1">WCAG - Success Criteria</th>
                            <th class="align-middle" rowspan="2" colspan="1">Level</th>
                            <th class="align-middle" rowspan="2" colspan="1">Version</th>
                            <th colspan="3">Test Result</th>
                            <th colspan="4">Severity</th>
                        </tr>
                        <tr>
                            <th>Pass</th>
                            <th>Fail</th>
                            <th>DNA</th>
                            <th>Low</th>
                            <th>Medium</th>
                            <th>High</th>
                            <th>NA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $sno=0;
                        @endphp
                        @foreach($data['data']['wcag'] as $wcag)
                        @php
                        $sno++;
                        $wcag_sc = array_values($wcag);
                        $is_valid = true;
                        $fail = $wcag_sc[0]['fail'];
                        $severity = $wcag_sc[0]['severity_low'] + $wcag_sc[0]['severity_medium'] +  $wcag_sc[0]['severity_high'];
                        $pass_dna = $wcag_sc[0]['pass'] + $wcag_sc[0]['dna'];
                        if($fail != $severity){
                            $is_valid  = false;
                        }
                        if($is_valid && $pass_dna != $wcag_sc[0]['severity_na']){
                            $is_valid  = false;
                        }
                        @endphp
                        <tr class="{{($is_valid == false) ? 'bg-danger text-white' : 'ok'}}">
                            <td class="text-center">{{$sno}}</td>
                            <td>{{$wcag_sc[0]['name']}}</td>
                            <td class="text-center">{{$wcag_sc[0]['level']}}</td>
                            <td class="text-center">{{$wcag_sc[0]['version']}}</td>
                            <td class="text-center">{{$wcag_sc[0]['pass']}}</td>
                            <td class="text-center bold">{{$wcag_sc[0]['fail']}}</td>
                            <td class="text-center">{{$wcag_sc[0]['dna']}}</td>
                            <td class="text-center">{{$wcag_sc[0]['severity_low']}}</td>
                            <td class="text-center">{{$wcag_sc[0]['severity_medium']}}</td>
                            <td class="text-center">{{$wcag_sc[0]['severity_high']}}</td>
                            <td class="text-center">{{$wcag_sc[0]['severity_na']}}</td>
                        </tr>
                        
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection