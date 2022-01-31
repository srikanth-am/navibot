@extends('layouts.navi')
@section('title') {{'User Roles - NaviBot::Amnet-Systems'}} @endsection
@section('content')
@php
$role_id = "";
$action = 'Add';
@endphp
<div class="container-fluid">
    <div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex flex-column">
                    <div class="d-inline"">
                        <a class="font-weight-bold fs-20px" href="{{route('settings')}}" style="color: #273C75;font-size:18px">Settings</a>
                        <span class="font-weight-bold mt-2 fa fa-chevron-right" style="font-size: 16px;"></span>
                        <a class="font-weight-bold fs-20px" href="{{route('user-roles')}}" style="color: #273C75;font-size:18px">User Roles</a>
                        <span class="font-weight-bold mt-2 fa fa-chevron-right" style="font-size: 16px;"></span>
                        @isset(request()->route()->parameters['id'])
                            @php
                            $role_id = request()->route()->parameters['id'];
                            $action = 'Edit';
                            @endphp
                        @endisset
                        <span class="font-weight-bold mt-2 fs-18px" style="opacity: .9;font-size:18px">{{$action}}</span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-7 mx-auto">
            <form class="form" method="POST" id="role_form">
                @csrf
                <div class="card shadow">
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="label" for="role">Role</label>
                            <input type="text" class="form-control" placeholder="" id="role" />
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{route('user-roles')}}" class="btn btn-danger font-weight-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary submit font-weight-bold" id="usr_sngup">{{($action == 'Edit') ? 'Update' : 'Submit'}}</button>
                    </div>
                </div>
            </form>
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
        //
        let role_id = "{{$role_id}}";
        if(role_id){
            $.ajax({
                data: { },
                url: "/navibot/web-navigator/settings/user-roles/"+role_id,
                type: "GET",
                dataType: 'json',
                success: function(data) {
                    // console.log('data', data);
                    if(data.length == 1){
                        let role = data[0];
                        $('#role').val(role['role']);
                    }
                },
            });
        }
        $("#role_form").submit(function(e){
            e.preventDefault();
            var role = {
                id : '{{$role_id}}',
                role: $.trim($('#role').val()),
            };
            g_loader("on", "Processing...");
            $.ajax({
                data: role,
                url: "{{route('save-roles')}}",
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    g_loader("off");
                    if (data.status == 'success') {
                        swal({
                            title: data.status.toUpperCase(),
                            text: data.message,
                            type: data.status,
                            showCancelButton: true,
                            cancelButtonColor: '#dc3545',
                            confirmButtonColor: '#273c75',
                            confirmButtonText: 'Go to user roles'
                        }).then(function(e) {
                            if (e.value) {
                                window.location.href = "{{route('user-roles')}}";
                            }
                        })
                    }else{
                        show_swal_notify(data.status, data.message);
                    }
                },
            });
        });
    });
</script>
@endsection