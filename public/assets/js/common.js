function show_swal_notify(status, message) {
    if (!status || !message) {
        return false;
    }
    swal(status.toUpperCase(), message, status);
}

function g_loader(display, message) {
    if (display == 'on') {
        $("#global_loader_div .back-overlay").css("display", "block");
        $("#global_loader_div .loading-box").css("display", "block");
        if (!message) {
            message = 'Please wait...';
        }
        var new_disp = '<div>' + message + '</div>';
        $("#global_loader_div .loader-text").html(new_disp);
    } else if (display == 'off') {
        $("#global_loader_div .back-overlay").css("display", "none");
        $("#global_loader_div .loading-box").css("display", "none");
        $("#global_loader_div .loader-text").html("");
    }
}

function ajax(url, command, param) {
    var data = {};
    if ($.type(command) == "object") {
        data = command;
    } else {
        data.command = command;
        data.param = param;
    }
    return $.post(url, data);

}