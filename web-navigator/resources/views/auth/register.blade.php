@extends('layouts.auth')
@section('title') {{'Sign Up - NaviBot::Web Navigator'}} @endsection

@section('content')
<div class="">
    <div class="text-center">
        <h3 class="mb-4 fw-600">Sign Up</h3>
        
        <div class="heading_divider"></div>
    </div>
</div>
<form class="form" method="POST" id="signup_form">
    @csrf
    <div class="form-group mb-3">
        <label class="label" for="name">Name</label>
        <input type="text" class="form-control" placeholder="" id="name" />
    </div>
    <div class="form-group mb-3">
        <label class="label" for="email">Email</label>
        <input type="email" class="form-control" placeholder="" id="email" autocomplete="off" />
    </div>
    <div class="form-group mb-3">
        <label class="label" for="password">Password</label>
        <input type="password" class="form-control" id="password" />
    </div>
    <div class="form-group mb-3">
        <label class="label" for="password_confirmation">Confirm Password</label>
        <input type="password" class="form-control" id="password_confirmation" autocomplete="off" name="password_confirmation"/>
    </div>
    <div class="form-group mb-3">
        <label class="label" for="role">Role</label>
        <select name="team" id="role" class="form-control">
            <option value="">Select...</option>
            <!-- <option value="2">User</option> -->
            @foreach($roles as $r)
                <option value="{{$r->id}}">{{$r->role}}</option>
            @endforeach
        </select>
    </div>
    <div class="text-right">
        <a href="{{route('login')}}" class="btn btn-danger font-weight-bold btn-lg">Cancel</a>
        <button type="submit" class="btn btn-primary submit font-weight-bold btn-lg ml-3" id="login_submit_btn">Submit</button>
    </div>
</form>
@endsection
