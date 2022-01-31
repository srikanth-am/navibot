$(document).ready(function() {
    $.ajaxSetup({
        async: true,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ajaxComplete(function(event, xhr, settings) {
        var http_status = xhr.status;
        if (http_status == '419' || http_status == '401') {
            location.reload();
        }
        // var res = JSON.parse(xhr.responseText);
        // console.log("Error", res);
    });
});

$("#extract_urls").submit(function(e) {
    e.preventDefault();
    g_loader("on", "Processing...");
    let domain = $.trim($("#url").val());
    var parser = document.createElement('a');
    parser.href = domain;
    domain = parser.protocol + "//" + parser.hostname;
    let query_string = ($("#query_str").prop('checked')) ? "yes" : 'no';
    var ajax = $.ajax({
        data: { domain: domain, query_str: query_string },
        url: "/navibot/web-navigator/web-navigator",
        type: "POST",
        dataType: 'json',
        success: function(data) {
            g_loader("off");
            console.log(data);
            if (data.status != undefined) {
                if (data.status == 'success') {
                    start_bg_process(data.domain, data.domain_id, data.query_str);
                    swal({
                        title: data.status.toUpperCase(),
                        text: data.message,
                        type: data.status,
                        showCancelButton: true,
                        cancelButtonColor: '#dc3545',
                        confirmButtonColor: '#273c75',
                        confirmButtonText: 'Go to dashboard'
                    }).then(function(e) {
                        if (e.value) {
                            window.location.href = "/navibot/web-navigator/dashboard";
                        }
                    })
                } else {

                    show_swal_notify(data.status, data.message);
                }
            }
        }
    });
});

function ExportReport(id, url, query_str, total_urls) {
    console.log(id, url, query_str, total_urls);
    let q = (query_str == 1) ? "No" : "Yes";
    $("#export_disp_data").html("")
    let html = '<div class="d-flex mb-3">';
    html += '<span class="text-dark-50 flex-root font-weight-bold">Website URL</span>';
    html += '<span class="text-dark flex-root">' + url + '</span>';
    html += '</div>';
    html += '<div class="d-flex mb-3">';
    html += '<span class="text-dark-50 flex-root font-weight-bold">Total URLs</span>';
    html += '<span class="text-dark flex-root">' + total_urls + '</span>';
    html += '</div>';
    html += '<div class="d-flex mb-3">';
    html += '<span class="text-dark-50 flex-root font-weight-bold">Query String</span>';
    html += '<span class="text-dark flex-root">' + q + '</span>';
    html += '</div>';
    html += '<input type="text" class="form-control" id="domain_id" hidden value="' + id + '">';
    html += '<div class="form-row">';
    html += '<div class="form-group col-md-6">';
    html += '<label for="hour" class="font-weight-bold">Hours</label>';
    html += '<input type="number" class="form-control" id="hour" value="04" min="1" max="12" onchange="setTwoNumberDecimal(this)">';
    html += '</div>';
    html += '<div class="form-group col-md-6">';
    html += '<label for="minute" class="font-weight-bold">Minutes</label>';
    html += '<input type="number" class="form-control" id="minute" min="0" max="59" value="00" onchange="setTwoNumberDecimal(this)">';
    html += '</div>';
    html += '</div>';
    //
    html += '<div class="form-row">';
    html += '<div class="form-group col-md-6">';
    html += '<label for="resource" class="font-weight-bold">Number of Resources</label>';
    html += '<input type="number" class="form-control" id="resource" value="1" min="1" max="15">';
    html += '</div>';
    html += '<div class="form-group col-md-6">';
    html += '<label for="minute" class="font-weight-bold">Currency Type</label>';
    //html += '<input type="number" class="form-control" id="minute" min="0" max="59" value="00" onchange="setTwoNumberDecimal(this)">';
    html += '<select class="form-control" name="currency" id="currency">';
    html += '<option value="USD">&#36; USD</option>';
    html += '<option value="EURO">&#128; Euro</option>';
    html += '<option value="POUND">&#163; Pound</option>';
    html += '</select>';
    html += '</div>';
    html += '</div>';
    //


    $("#export_disp_data").html(html);
}

function setTwoNumberDecimal(e) {
    let m = e.value;
    if (parseInt(m, 10) < 10) {
        m = '0' + m;
    }
    // if (parseInt(m, 10) < 10) m = '0' + m;
    $(e).val(m);
}
$("#export_submit").click(function() {
    let h = $("#hour").val();
    let m = $("#minute").val();
    let resource = $("#resource").val();
    let domain_id = $("#domain_id").val();
    let currency = $("#currency").val();
    //
    if (h == 0) {
        show_swal_notify("error", "Invalid Hour");
        $("#hour").focus();
        return false;
    }
    if (h > 12) {
        show_swal_notify("error", "Hour should not be grater than 12");
        $("#hour").focus();
        return false;
    }
    if (h > 59) {
        show_swal_notify("error", "Minute should not be grater than 59");
        $("#hour").focus();
        return false;
    }
    if (resource == 0) {
        show_swal_notify("error", "Number of resource should be greater than 0");
        $("#resource").focus();
        return false;
    }
    if (resource > 15) {
        show_swal_notify("error", "Number of resource should be less than 15");
        $("#resource").focus();
        return false;
    }
    //
    g_loader("on", "Processing...");
    $("#export_cancel").click();
    $.ajax({
        type: 'POST',
        url: "/navibot/web-navigator/export-report/", // + domain_id + "/" + h + "/" + m + "/" + resource,
        data: {
            domain_id: domain_id,
            h: h,
            m: m,
            resource: resource
        },
        dataType: 'json'
    }).done(function(data) {
        // $("#export_cancel").click();
        if (data.file != undefined) {
            var $a = $("<a>");
            $a.attr("href", data.file);
            $("body").append($a);
            $a.attr("download", data.filename);
            $a[0].click();
            $a.remove();
        }
        g_loader("off");
        show_swal_notify(data.status, data.message);
    });
});

function start_bg_process(domain, id, query_string) {
    let data = { domain: domain, query_str: query_string, domainId: id };
    $.ajax({ data: data, url: "/navibot/web-navigator/start-bg-process", type: "POST" });
}