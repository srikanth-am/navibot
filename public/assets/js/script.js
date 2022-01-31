$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    //
    $(document).ajaxStart(function(event, xhr, settings) {

        $("#submit_btn").attr("disabled", "disabled");
        $("#template_btn").attr("disabled", "disabled");
        $("#download_btn").attr("disabled"), "disabled";
        //$("#loader").removeClass("d-none");
        g_loader('on');
    });
    //
    $('input[type=radio][name=type]').on('change', function() {
        switch ($(this).val()) {
            case 'html':
                $("#sitemap_count_container").addClass("d-none");
                break;
            case 'sitemap':
                $("#sitemap_count_container").removeClass("d-none");
                break;
        }
    });
    $(document).ajaxComplete(function(event, xhr, settings) {
        $("#loader").addClass("d-none");
        g_loader('off');
        var path = settings.url;
        if (xhr.status == '419') {
            location.reload();
        } else if (xhr.status == '500') {
            if (path == "start-crawling" || path == "getLinks") {
                $("#submit_btn").removeAttr('disabled');
            } else if (path == "generate-templates") {
                $("#template_btn").removeAttr('disabled');
            } else if (path == "export") {
                $("#template_btn").removeAttr('disabled');
            }
            // $('[disabled]​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​​')​.removeAttr('disabled');​​​
            var res = JSON.parse(xhr.responseText);
            showAlert("error", res.message);
            console.log("Error", res);
        }
    });
});

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
$("form").submit(function(e) {
    e.preventDefault();
    //
    setInit();
    var url = $.trim($('#url').val());
    var type = $('input[name="type"]:checked').val();
    if (url && type) {
        $.ajax({
            data: { url: url, type: type },
            url: "getLinks",
            type: "POST",
            dataType: 'json',
            success: function(data) {
                if (data.status != undefined) {
                    showAlert(data.status, data.message);
                    setTimeout(function() {
                        $("#success_msg").addClass("d-none");
                    }, 10000);
                    if (data.status == "success") {
                        localStorage.setItem('domain_id', data.data.domain_id)
                        start_bg_process(data.data);
                    } else {
                        $("#submit_btn").removeAttr('disabled');
                    }
                }
            }
        });
    } else {
        swal("Error", "All Fields are required", "error");
    }

});

function start_bg_process(data) {
    $.ajax({
        data: data,
        url: "start-crawling",
        type: "POST",
        // async: false,
        // success: function(response) {
        //     $("#template_container").removeClass("d-none");
        //     $("#template_btn").removeAttr("disabled");
        //     showAlert(response.status, response.message);
        // }
    });
}
$("#template_btn").click(function(e) {
    //$("#template_btn").attr('disabled', 'disabled');
    e.preventDefault();
    $.ajax({
        data: { url: $.trim($('#url').val()) },
        url: "generate-templates",
        type: "POST",
        dataType: 'json',
        success: function(data) {
            $("#download_btn").removeAttr("disabled");
            showAlert("success", "Templates Generated Successfully!");
        }
    });
    //
});

function gotoComparePage(e) {
    let host = window.location.origin;
    var url = $.trim($('#url').val());
    if (!url) {
        showAlert('error', 'Website URL is required. please enter website URL and try again.');
        $(".swal2-confirm").click(function() {
            $('#url').focus();
        });
        return false;
    }
    var domain_id = localStorage.getItem('domain_id');
    if (domain_id) {
        host += "/link-extractor/public/compare-html-vs-sitmap/" + domain_id;
        window.open(host, "_blank");
    }
}
$("#induvial_download").click(function(e) {
    e.preventDefault();
    $.ajax({
        type: 'GET',
        url: "export",
        data: {},
        dataType: 'json'
    }).done(function(data) {
        if (data.file != undefined) {
            var $a = $("<a>");
            $a.attr("href", data.file);
            $("body").append($a);
            $a.attr("download", data.filename);
            $a[0].click();
            $a.remove();
        }

        showAlert(data.status, data.message);
        $("#submit_btn").removeAttr('disabled');
    });
});
$("#bundle_download").click(function(e) {
    e.preventDefault();
    $.ajax({
        type: 'GET',
        url: "export-bundle",
        data: {},
        dataType: 'json'
    }).done(function(data) {
        if (data.file != undefined) {
            var $a = $("<a>");
            $a.attr("href", data.file);
            $("body").append($a);
            $a.attr("download", data.filename);
            $a[0].click();
            $a.remove();
        }

        showAlert(data.status, data.message);
        $("#submit_btn").removeAttr('disabled');
    });
});

function showAlert(status, message) {
    if (status && message) {
        swal(status.toUpperCase(), message, status);
    }

}