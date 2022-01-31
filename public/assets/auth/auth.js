$(document).ready(function() {
    $.ajaxSetup({ async: false, headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } }); //setingup with csrf token
    //
    $(document).ajaxStart(function(event, xhr, settings) {
        $("#login_submit_btn").html("<i class='fas fa-sync-alt fa-spin mr-2' aria-hidden='true'></i>Processing...");
    });
    $(document).ajaxComplete(function(event, xhr, settings) {
        $("#login_submit_btn").text("Sign In");
        var http_status = xhr.status;
        if (http_status == '419') {
            location.reload();
        }
        var res = JSON.parse(xhr.responseText);
        show_swal_notify("error", res.message);
        console.log("Error", res);
    });

});
//login

$("#login_form").submit(function(e) {
    e.preventDefault();

    let email, password;
    email = $.trim($('#email').val());
    password = $.trim($('#password').val());
    //
    $.ajax({
        data: { email: email, password: password },
        url: "login",
        type: "POST",
        dataType: 'json',
        success: function(data) {
            show_swal_notify(data.status, data.message);
            if (data.status == "success") {
                window.location.reload();
            }
        }
    });
});
//
$("#forgot_pwd").submit(function(e) {
    $("#forgot_submit_btn").html("<i class='fas fa-sync-alt fa-spin mr-2' aria-hidden='true'></i>Processing...");
    e.preventDefault();
    let email, password;
    email = $.trim($('#email').val());
    //
    $.ajax({
        data: { email: email },
        url: '/navibot/forgot-password',
        type: "POST",
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            $("#forgot_submit_btn").text("Send Password Reset Link");
            showAlert(data.status, data.message);
            if (data.status == "success") {
                window.location.href = "/navibot/login";
            }
        }
    });
    //console.log(email, password);

});
$("#reset_form").submit(function(e) {
    $("#reset_submit_btn").html("<i class='fas fa-sync-alt fa-spin mr-2' aria-hidden='true'></i>Processing...");
    e.preventDefault();
    let token, email, password, c_password;
    token = $.trim($('#token').val());
    email = $.trim($('#email').val());
    password = $.trim($('#password').val());
    c_password = $.trim($('#cpassword').val());
    //
    $.ajax({
        data: { token: token, email: email, password: password, password_confirmation: c_password },
        url: '/navibot/reset-password',
        type: "POST",
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            $("#reset_submit_btn").text("Reset Password");
            showAlert(data.status, data.message);
            var redirect = (data.redirect != undefined) ? true : false;
            if (data.status == "success" || redirect) {
                window.location.href = "/navibot/login";
            }
        }
    });
    //console.log(email, password);

});
//
$("#signup_form").submit(function(e) {
    e.preventDefault();
    // let name, email, password, password_confirmation;
    // name = $.trim($('#name').val());
    // email = $.trim($('#email').val());
    // password = $.trim($('#password').val());
    // password_confirmation = $.trim($('#password_confirmation').val());
    let data = {
        name : $.trim($('#name').val()),
        email : $.trim($('#email').val()),
        password : $.trim($('#password').val()),
        password_confirmation : $.trim($('#password_confirmation').val()),
        role_id : $.trim($('#role').val()),
    }
    //
    alert();
    return false;
    $.ajax({
        data: data,
        url: "signup",
        type: "POST",
        dataType: 'json',
        success: function(data) {
            show_swal_notify(data.status, data.message);
            if (data.status == "success") {
                window.location.reload();
            }
        }
    });
});