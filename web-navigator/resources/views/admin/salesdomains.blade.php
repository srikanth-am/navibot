@extends('layouts.navi')
@section('title') {{'Sales Domains - NaviBot::Amnet-Systems'}} @endsection
@section('content')
<div class="container-fluid switch-appear">
    <div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex flex-column">
                    <div class="d-inline"">
                        <a class="font-weight-bold fs-20px" href="{{route('settings')}}" style="color: #273C75;font-size:18px">Settings</a>
                        <span class="font-weight-bold mt-2 fa fa-chevron-right" style="font-size: 16px;"></span>
                        <span class="font-weight-bold mt-2 fs-18px" style="opacity: .9;font-size:18px">Sales Domains</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn bg-blue text-white font-weight-bold ml-2" title="Refresh" id="refresh">Refresh</button>
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
                            <th scope="col" class="text-center">Total URLs</th>
                            <th scope="col" class="text-center">Start Time</th>
                            <th scope="col" class="text-center">Time Utilized</th>
                            <th scope="col" class="text-center">Created By</th>
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
<script type="text/javascript">

    $(document).ready(function(){
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
                url : "{{route('sales-domains')}}",
                data: function(d){
                    d.search = $('input[type="search"]').val()
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', class:"text-center", orderable: false, searchable: false},
                {data: 'url', name: 'url', class:"text-left"},
                {data: 'total_urls', name: 'total_url', class:"text-center font-weight-bolder"},
                {data: 's_time', name: 's_time', class:"text-center"},
                {data: 't_utilized', name: 't_urilized', class:"text-center"},
                {data: 'name', name: 'name', class:"text-center"},
                {data: 'action', name: 'action', class:"text-center", orderable: false, searchable: false}
            ],
            oLanguage: {sProcessing: '<div class="spinner spinner-darker-dark spinner-center"></div>'}
        });
        
        $("#refresh").click(function(){
            table.draw();

        });
        
    });
    //
    function deleteSalesDomainsAsAdmin(id){
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
                    url  : "/navibot/web-navigator/settings/sales-domains/delete/"+id,
                }).done(function(data) {
                    if(data.status == "success"){
                        $('#domain_table').DataTable().ajax.reload(); 
                    }
                    show_swal_notify(data.status, data.message);
                });
            }
        });
    }
</script>
@endsection