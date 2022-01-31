@extends('layouts.auth')
@section('title') {{'Email Confirmation - NaviBot::Amnet-Systems'}} @endsection
@section('content')
<div class="">
    <div class="text-center">
        <h3 class="fw-600 mt-5">Email address successfully verified</h3>
        {{-- <div class="heading_divider"></div> --}}
        <p id="email_id" class=""></p>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function(){
        // $("#email_id").addClass('d-done');
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var id = "{{ Request::route('id') }}";
        var token ="{{ Request::route('token') }}";
        if(id && token){
            $.ajax({
                type: 'POST',
                url  : "{{url('email-verification')}}",
                data : {id:id, token:token},
            }).done(function(data) {
                if (data.status == 'success') {
                    $("#email_id").html(data.email);
                    swal({
                        title: data.status.toUpperCase(),
                        text: data.message,
                        type: data.status,
                        showCancelButton: false,
                        confirmButtonColor: '#273c75',
                        confirmButtonText: 'Ok'
                    }).then(function(e) {
                        if (e.value) {
                            window.location.href = "/navibot/web-navigator";
                        }
                    })
                } else {
                    show_swal_notify(data.status, data.message);
                }
            });

        }
    });
    </script>
@endsection