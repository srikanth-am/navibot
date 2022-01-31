$(document).ready(function () {
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }

    });
    //
    let excel = localStorage.getItem('rpt_fle_nme');
    if(excel){
        var href = "/navibot/audit-report/summary/"+excel;
        $("#summary_link").attr("href", href);
    }
    //
    let percentage = '0';
    $('#fileUploadForm').ajaxForm({
        beforeSend: function () {
            //var percentage = '0';
            var file = $('#file').val();
            if(file){
                $('#progressbar').removeClass('d-none');
            }
        },
        uploadProgress: function (event, position, total, percentComplete) {
            percentage = percentComplete;
            localStorage.removeItem('rpt_fle_nme');
            $('#progress_label').text(percentage+'%');
            $('.progress .progress-bar').css("width", percentage+'%', function() {
                return $(this).attr("aria-valuenow", percentage) + "%";
            })
        },
        complete: function (xhr) {},
        success: function(data){
            
            if(data.status == "success"){
                localStorage.setItem('rpt_fle_nme', data.fname);
                save_data(data.fname);
            }else{
                show_swal_notify(data.status, data.message); 
            }
        }
    });
    //
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
function save_data(filename){
    if(filename){
        g_loader("on", "Processing...");
        $.ajax({
            data: { fname:filename },
            url: "/navibot/audit-report/save-excel",
            type: "POST",
            dataType: 'json',
            cache: false,
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
                        confirmButtonText: 'Go to Report'
                    }).then(function(e) {
                        if (e.value) {
                            window.location.href = "/navibot/audit-report/report";
                        }
                    })
                } else {

                    show_swal_notify(data.status, data.message);
                }
            },
        });
    }
    //alert();
}
function show_swal_notify(status, message) {
    if (!status || !message) {
        return false;
    }
    swal(status.toUpperCase(), message, status);
}
//
function wcag_issues_chart(){
    // Chart.defaults.global.defaultFontColor = "#273C75";

    var chartOptions = {
        animation: false,
        responsive: true,
        scales: {
            y: {
                ticks: {
                    beginAtZero:true,
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    color: "#eee",
                }
            },
          x: {
                ticks: {
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    display: false,
                    color: "#273C75",
                }
            }
        },
        maintainAspectRatio: true,
        plugins:{   
            legend: {
                display: true,
                labels: {
                    usePointStyle: true,
                    color:'#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                  },
                
            },
            datalabels: {
                anchor :'end',
                align :'top',
                formatter: (value, ctx) => {
                    return "  " +value;
                },
                color: '#273C75',
                font:{
                    size: 14,
                    family: "'Roboto', sans-serif",
                    weight: '600'
                }
            },
        },
        barPercentage:0.3
     }
     //
     var file = localStorage.getItem('rpt_fle_nme');
     
     //
     $.ajax({
         data: { filename : file},
         url: "/navibot/audit-report/wcag-issues-chart",
         type: "POST",
         dataType: 'json',
         cache: false,
         success: function(data) {
             if(data.status == 'success'){
                $('#wcag_issues').text(data.total_wcag_issues);
                $('#wcag_issues_per_page').text(data.avg_wcag_issues);
                // $('#wcag_issues_chart #wcag_issues_chart_canvas').remove(); 
				// $('#wcag_issues_chart #chart_div').append('<canvas id="wcag_issues_chart_canvas" class="chartjs-render-monitor"><canvas>');
                //
                var ctx = document.getElementById("wcag_issues_chart_canvas");

                 var barChart = new Chart(ctx, {
                     type: 'bar',
                     data: JSON.parse(JSON.stringify(data.chart)) ,
                     options: chartOptions,
                     plugins: [ChartDataLabels],
                 });
                var chart_1 = barChart.toBase64Image();
                // console.log("chart_1", chart_1);
                convert_chart_to_img('chart_1.png', chart_1);

             }
        }
    });
}
function user_impact_chart(){
    var ctx = document.getElementById("user_impact_chart_canvas");
    var chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation:{
            onComplete: function(){
                dataURL2 = ctx.toDataURL('image/png');
                convert_chart_to_img('chart_2.png', dataURL2);
            }
        },
        scales: {
            y: {
                ticks: {
                    beginAtZero:true,
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    color: "#eee",
                }
            },
          x: {
                ticks: {
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    display: false,
                    color: "#273C75",
                }
            }
        },
        plugins:{   
            legend: {
                display: false,
            },
            datalabels: {
                anchor :'end',
                align :'right',
                formatter: (value, ctx) => {
                    return "  " +value;
                },
                color: '#273C75',
                font:{
                    size: 14,
                    family: "'Roboto', sans-serif",
                    weight: '600'
                },
            },
        },
        indexAxis: 'y',
        barPercentage:0.4   
     }
     //
     var file = localStorage.getItem('rpt_fle_nme');
     //
     $.ajax({
         data: { filename : file},
         url: "/navibot/audit-report/user-impact-chart",
         type: "POST",
         dataType: 'json',
         cache: false,
         success: function(data) {
             if(data.status == 'success'){
                var chart_2 = new Chart(ctx, {
                    type: 'bar',
                    data: JSON.parse(JSON.stringify(data.chart)) ,
                    options: chartOptions,
                    plugins: [ChartDataLabels],
                 });
                // var img = chart_2.toBase64Image();
                
                // console.log(img);
             }
        }
    });
}
function wcag_top_ten_2_0_chart(){
    var ctx = document.getElementById("wcag_top_ten_2_0_chart_canvas");
    var chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation:{
            onComplete: function(){
                dataURL3 = ctx.toDataURL('image/png');
                convert_chart_to_img('chart_3.png', dataURL3);
            }
        },
        scales: {
            y: {
                ticks: {
                    beginAtZero:true,
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    color: "#eee",
                }
            },
          x: {
                ticks: {
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    display: false,
                    color: "#273C75",
                }
            }
        },
        plugins:{   
            legend: {
                display: false
            },
            datalabels: {
                anchor :'end',
                align :'right',
                formatter: (value, ctx) => {
                    return "  " +value;
                },
                color: '#273C75',
                font:{
                    size: 14,
                    family: "'Roboto', sans-serif",
                    weight: '600'
                },
            },
        },
        indexAxis: 'y',
        // barPercentage:0.4   
     };
     //
     var file = localStorage.getItem('rpt_fle_nme');
     //
     $.ajax({
         data: { filename : file},
         url: "/navibot/audit-report/top-ten-issues-2-0-chart",
         type: "POST",
         dataType: 'json',
         cache: false,
         success: function(data) {
             if(data.status == 'success'){
                var barChart = new Chart(ctx, {
                    type: 'bar',
                    data: JSON.parse(JSON.stringify(data.chart)) ,
                    options: chartOptions,
                    plugins: [ChartDataLabels],
                 });
             }
        }
    });
}
function wcag_top_ten_2_1_chart(){
    var ctx = document.getElementById("wcag_top_ten_2_1_chart_canvas");
    var chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation:{
            onComplete: function(){
                dataURL4 = ctx.toDataURL('image/png');
                convert_chart_to_img('chart_4.png', dataURL4);
            }
        },
        scales: {
            y: {
                ticks: {
                    beginAtZero:true,
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    color: "#eee",
                }
            },
          x: {
                ticks: {
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    display: false,
                    color: "#273C75",
                }
            }
        },
        plugins:{   
            legend: {
                display: false
            },
            datalabels: {
                anchor :'end',
                align :'right',
                formatter: (value, ctx) => {
                    return "  " +value;
                },
                color: '#273C75',
                font:{
                    size: 14,
                    family: "'Roboto', sans-serif",
                    weight: '600'
                },
            },
        },
        indexAxis: 'y',
        // barPercentage:0.9  
    };
        //
    var file = localStorage.getItem('rpt_fle_nme');
        //
    $.ajax({
        data: { filename : file},
        url: "/navibot/audit-report/top-ten-issues-2-1-chart",
        type: "POST",
        dataType: 'json',
        cache: false,
        success: function(data) {
            if(data.status == 'success'){
                var barChart = new Chart(ctx, {
                    type: 'bar',
                    data: JSON.parse(JSON.stringify(data.chart)) ,
                    options: chartOptions,
                    plugins: [ChartDataLabels],
                });
            }
        }
    });
}
function wcag_sc_lavel_a_chart(){
    var ctx = document.getElementById("wcag_sc_lavel_a_chart_canvas");
    var chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation:{
            onComplete: function(){
                dataURL5 = ctx.toDataURL('image/png');
                convert_chart_to_img('chart_5.png', dataURL5);
            }
        },
        scales: {
            y: {
                ticks: {
                    beginAtZero:true,
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    color: "#eee",
                }
            },
          x: {
                ticks: {
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    display: false,
                    color: "#273C75",
                }
            }
        },
        plugins:{   
            legend: {
                display: false
            },
            datalabels: {
                anchor :'end',
                align :'right',
                formatter: (value, ctx) => {
                    return "  " +value;
                },
                color: '#273C75',
                font:{
                    size: 14,
                    family: "'Roboto', sans-serif",
                    weight: '600'
                },
            },
        },
        indexAxis: 'y',
        // barPercentage:0.4   
     }
     //
    var file = localStorage.getItem('rpt_fle_nme');
     //
    $.ajax({
         data: { filename : file},
         url: "/navibot/audit-report/wcag-conformance-level-a-chart",
         type: "POST",
         dataType: 'json',
         cache: false,
         success: function(data) {
             if(data.status == 'success'){
                var barChart = new Chart(ctx, {
                    type: 'bar',
                    data: JSON.parse(JSON.stringify(data.chart)) ,
                    options: chartOptions,
                    plugins: [ChartDataLabels],
                 });
               
             }
        }
    });
}
function wcag_sc_lavel_aa_chart(){
    var ctx = document.getElementById("wcag_sc_lavel_aa_chart_canvas");
    var chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation:{
            onComplete: function(){
                dataURL6 = ctx.toDataURL('image/png');
                convert_chart_to_img('chart_6.png', dataURL6);
            }
        },
        scales: {
            y: {
                ticks: {
                    beginAtZero:true,
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    color: "#eee",
                }
            },
          x: {
                ticks: {
                    color: '#273C75',
                    font:{
                        size: 14,
                        family: "'Roboto', sans-serif",
                        weight: '600'
                    }
                },
                grid:{
                    display: false,
                    color: "#273C75",
                }
            }
        },
        plugins:{   
            legend: {
                display: false
            },
            datalabels: {
                anchor :'end',
                align :'right',
                formatter: (value, ctx) => {
                    return "  " +value;
                },
                color: '#273C75',
                font:{
                    size: 14,
                    family: "'Roboto', sans-serif",
                    weight: '600'
                },
            },
        },
        indexAxis: 'y',
        // barPercentage:0.4   
     }
     //
    var file = localStorage.getItem('rpt_fle_nme');
     //
    $.ajax({
         data: { filename : file},
         url: "/navibot/audit-report/wcag-conformance-level-aa-chart",
         type: "POST",
         dataType: 'json',
         cache: false,
         success: function(data) {
             if(data.status == 'success'){
                var barChart = new Chart(ctx, {
                    type: 'bar',
                    data: JSON.parse(JSON.stringify(data.chart)) ,
                    options: chartOptions,
                    plugins: [ChartDataLabels],
                 });
             }
        }
    });
}

function convert_chart_to_img(name, imgdata){
    $.ajax({
        data: { name : name, img:imgdata},
        url: "/navibot/audit-report/save-chart-img",
        type: "POST",
        dataType: 'json',
        cache: false,
        success: function(data) {
            console.log("data", data);
        }
   });
}