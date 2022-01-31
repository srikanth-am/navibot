@extends('layouts.navi')
@section('title') {{'Dashboard - NaviBot::Amnet-Systems'}} @endsection
@section('content')
<div class="container-fluid">
    <div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex flex-column">
                    <h1 class="font-weight-bold mt-2 fs-20px">Dashboard</h1>
                    {{--  <div class="d-flex align-items-center">
                        <a href="/navibot/home" class="fc-blue">Home</a>
                        <span class="mx-2"><i class="fa fa-angle-right fc-blue"></i></span>
                        <span class="fc-blue">Dashboard</span>
                    </div>  --}}
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div class="font-weight-bold">Last Updated: 
                    <span id="last_update_time">
                        {{\Carbon\Carbon::now()->format('d-m-Y h:i A')}}
                    </span>
                </div>
                <button class="btn bg-blue text-white font-weight-bold ml-2" id="refresh"><i class="fa fa-sync-alt mr-1 text-white" aria-hidden="true"></i>Refresh</button>
                <a href="/navibot/web-navigator/add-domain" class="btn bg-blue text-white font-weight-bold ml-2"><i class="fa fa-spider mr-1 text-white" aria-hidden="true"></i>Add Domain</a>
            </div>
        </div>
    </div>
    <div class="card mb-5">
        <div class="card-body" style="overflow: hidden">
            <div class="table-responsive">
                <table class="table table-hover" id="domain_table">
                    <thead class="bg-blue text-white">
                        <tr>
                            <th scope="col" class="text-center">#</th>
                            <th scope="col" class="text-center">Website URL</th>
                            <th scope="col" class="text-center">Query String</th>
                            <th scope="col" class="text-center">Total URLs</th>
                            <th scope="col" class="text-center">Start Time</th>
                            {{--  <th scope="col">End Time</th>  --}}
                            <th scope="col" class="text-center">Time Utilized</th>
                            <th scope="col" class="text-center">URL Status</th>
                            <th scope="col" class="text-center">Template Status</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="exampleModalScrollable" tabindex="-1" role="dialog" aria-labelledby="staticBackdrop" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Download</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="fa fa-times text-dark"></i>
                </button>
            </div>
            <div class="modal-body" style="height: 300px;">
                <div id="export_disp_data"></div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal" id="export_cancel">Close</button>
                <button type="button" class="btn bg-blue text-white font-weight-bold" id="export_submit">Submit</button>
            </div>
        </div>
    </div>
</div>
{{--  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>  --}}
<script type="text/javascript">

    $(document).ready(function(){
        var time_interval = {{env('DASHBOARD_TIMEOUT')}}
        //console.log("time_interval", time_interval);
        var auto_refresh = "";
        if(auto_refresh !=''){

            clearTimeout(auto_refresh);
        }
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var table = $('#domain_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            order: [],
            ajax : {
                url : "/navibot/web-navigator/dashboard",
                data: function(d){
                    d.search = $('input[type="search"]').val()
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', class:"text-center", orderable: false, searchable: false},
                {data: 'url', name: 'url', class:"text-left"},
                {data: 'q_str', name: 'q_str', class:"text-center"},
                {data: 'total_urls', name: 'total_url', class:"text-right font-weight-bolder"},
                {data: 's_time', name: 's_time', class:"text-center"},
                {data: 't_utilized', name: 't_urilized', class:"text-center"},
                {data: 'url_status', name: 'url_status', class:"text-center"},
                {data: 'temp_status', name: 'temp_status', class:"text-center"},
                {data: 'action', name: 'action', class:"text-center", orderable: false, searchable: false}
            ],
            oLanguage: {sProcessing: '<div class="spinner spinner-darker-dark spinner-center"></div>'}
        });
        
        $("#refresh").click(function(){
            update_time();
            table.draw();

        });
        auto_refresh = setTimeout(function () {
            update_time();
            table.draw();
        }, time_interval);
        function update_time(){
            $("#last_update_time").html('<span class="spinner spinner-darker-dark spinner-sm spinner-center mx-3"></span>');
            $.get("time-now", function(data, status){
                $("#last_update_time").html(data);
            });
        }
    });
    //
    function deleteDomains(id){
        swal({
            title: "Are you sure, do you want to delete?",
            text: "You won't be able to revert this!",
            type: "question",
            showCancelButton: true,
            cancelButtonColor: '#dc3545',
            confirmButtonColor: '#273c75',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
        }).then(function(e) {
            if (e.value) {
                $.ajax({
                    type: 'GET',
                    url  : "{{url('manage-websites')}}/"+id,
                }).done(function(data) {
                    if(data.status == "success"){
                        //$('#member_table').DataTable().draw(true);
                        $('#domain_table').DataTable().ajax.reload(); 
                    }
                    show_swal_notify(data.status, data.message);
                });
            }
        });
    }
</script>
@endsection