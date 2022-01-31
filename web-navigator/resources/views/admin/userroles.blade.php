@extends('layouts.navi')
@section('title') {{'User Roles - NaviBot::Amnet-Systems'}} @endsection
@section('content')
<div class="container-fluid switch-appear">
    <div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex flex-column">
                    <div class="d-inline"">
                        <a class="font-weight-bold fs-20px" href="/navibot/web-navigator/settings" style="color: #273C75;font-size:18px">Settings</a>
                        <span class="font-weight-bold mt-2 fa fa-chevron-right" style="font-size: 16px;"></span>
                        <span class="font-weight-bold mt-2 fs-18px" style="opacity: .9;font-size:18px">User Roles</span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn bg-blue text-white font-weight-bold ml-2" title="Refresh" id="refresh">Refresh</button>
                <a href="/navibot/web-navigator/settings/user-roles/add" class="btn bg-blue text-white font-weight-bold ml-2">Add Role</a>
            </div>
        </div>
    </div>
    <div class="card mb-5">
        <div class="card-body" style="overflow: hidden">
            <div class="table-responsive overflow-hidden">
                <table class="table table-hover" id="users__role_tbl">
                    <thead class="bg-blue text-white">
                        <tr>
                            <th scope="col" class="text-center">#</th>
                            <th scope="col" class="text-center">Role</th>
                            <th scope="col" class="text-center">Is Default</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">Created On</th>
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
        var table = $('#users__role_tbl').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            order: [],
            ajax : {
                url : "{{route('user-roles')}}",
                data: function(d){
                    d.search = $('input[type="search"]').val()
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', class:"text-center", orderable: false, searchable: false},
                {data: 'role', name: 'role', class:"text-left"},
                {data: 'is_default', name: 'is_default', class:"text-center"},
                {data: 'active', name: 'active', class:"text-center"},
                {data: 'created_at', name: 'created_at', class:"text-center"},
                {data: 'action', name: 'action', class:"text-center", orderable: false, searchable: false}
            ],
            oLanguage: {sProcessing: '<div class="spinner spinner-darker-dark spinner-center"></div>'}
        });
        $("#refresh").click(function(){
            table.draw();
        });
    });
    function deleteRole(id){
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
                    url  : "{{url('settings/user-roles/delete')}}/"+id,
                }).done(function(data) {
                    if(data.status == "success"){
                        $('#users__role_tbl').DataTable().ajax.reload(); 
                    }
                    show_swal_notify(data.status, data.message);
                });
            }
        });

    }
    function change_role_status(id){
        let is_checked = $("#enable_disable_"+id).is(":checked");
        let action = (is_checked == true) ? "enable" : 'disable';
        swal({
            title: "Are you sure?",
            text: "Do you wish to " + action,
            type: "question",
            showCancelButton: true,
            cancelButtonColor: '#dc3545',
            confirmButtonColor: '#273c75',
            confirmButtonText: 'Yes, do it!',
            cancelButtonText: 'No, cancel!',
        }).then(function(e) {
            if (e.value) {
                $.ajax({
                    type: 'POST',
                    data : {id: id, action: action},
                    url  : "{{url('settings/user-roles/change_status')}}",
                }).done(function(data) {
                    if(data.status == "success"){
                        $('#users__role_tbl').DataTable().ajax.reload(); 
                    }
                    show_swal_notify(data.status, data.message);
                });
            }else{
                $("#enable_disable_"+id).prop('checked', !is_checked);
            }
        });
    }
</script>
@endsection