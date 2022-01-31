@extends('layouts.navi')
@section('title') {{'Settings - NaviBot::Amnet-Systems'}} @endsection
@section('content')
@php
$user_id = "";
$action = 'Add';
@endphp
<div class="container-fluid">
    <div class="subheader py-2 py-lg-12 subheader-transparent" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap p-0">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex flex-column">
                    <div class="d-inline"">
                        <a class="font-weight-bold fs-20px" href="/navibot/web-navigator/settings" style="color: #273C75;font-size:18px">Settings</a>
                        <span class="font-weight-bold mt-2 fa fa-chevron-right" style="font-size: 16px;"></span>
                        <a class="font-weight-bold fs-20px" href="/navibot/web-navigator/settings/users" style="color: #273C75;font-size:18px">Users</a>
                        <span class="font-weight-bold mt-2 fa fa-chevron-right" style="font-size: 16px;"></span>
                        @isset(request()->route()->parameters['id'])
                            @php
                            $user_id = request()->route()->parameters['id'];
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
            <form class="form" method="POST" id="user_signup">
                @csrf
                <div class="card shadow">
                    <div class="card-body">
                        <div class="form-group mb-3 row g-3">
                            <div class="col">
                                <label class="label" for="name">Name</label>
                                <input type="text" class="form-control" placeholder="" id="name" />
                            </div>
                            <div class="col">
                                <label class="label" for="role">Role</label>
                                <select name="team" id="role" class="form-control">
                                    <option value="">Select...</option>
                                    @foreach($roles as $r)
                                        <option value="{{$r->id}}">{{$r->role}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="label" for="email">Email</label>
                            <input type="email" class="form-control" placeholder="" id="email" disabled aria-disabled="true"/>
                        </div>
                        <div class="form-group mb-3 row g-3">
                            <div class="col">
                                <label class="label" for="emp_id">Employee ID</label>
                                <input type="text" class="form-control" id="emp_id" />
                            </div>
                            <div class="col">
                                <label class="label" for="tester_id">Tester ID</label>
                                <input type="text" class="form-control" id="tester_id" name="tester_id"/>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{route('users')}}" class="btn btn-danger font-weight-bold">Cancel</a>
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
        let user_id = "{{$user_id}}";
        let email = "";
        if(user_id){
            $.ajax({
                data: { },
                url: "/navibot/web-navigator/settings/users/user-data/"+user_id,
                type: "GET",
                dataType: 'json',
                success: function(data) {
                    // console.log('data', data);
                    if(data.length == 1){
                        let user = data[0];
                        $('#name').val(user['name']);
                        $('#role').val(user['role_id']);
                        $('#email').val(user['email']);
                        email = user['email'];
                        $('#emp_id').val(user['emp_id']);
                        $('#tester_id').val(user['tester_id']);
                    }
                },
            });
        }
        $("#user_signup").submit(function(e){
            e.preventDefault();
            var user = {
                id : '{{$user_id}}',
                name: $.trim($('#name').val()),
                role_id: $.trim($('#role').val()),
                email: email,
                emp_id: $.trim($('#emp_id').val()),
                tester_id: $.trim($('#tester_id').val()),
            };
            g_loader("on", "Processing...");
            $.ajax({
                data: user,
                url: "{{route('save-user')}}",
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    g_loader("off");
                    //console.log('data', data);
                    if (data.status == 'success') {
                        swal({
                            title: data.status.toUpperCase(),
                            text: data.message,
                            type: data.status,
                            showCancelButton: true,
                            cancelButtonColor: '#dc3545',
                            confirmButtonColor: '#273c75',
                            confirmButtonText: 'Go to Users'
                        }).then(function(e) {
                            if (e.value) {
                                window.location.href = "{{route('users')}}";
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