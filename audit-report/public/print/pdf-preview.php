<?php
// Start the session
session_start();
echo "<script>var fname = localStorage.getItem('rpt_fle_nme'); </script>";
$fname = "<script>document.writeln(fname);</script>";
$s = "amnet";
// print_r($s);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Document</title>
    <style>
        .column {
  float: left;
  width: 50%;
}

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}
    </style>
</head>
<body style="font-family: 'Roboto', sans-serif; overflow-x: hidden; color: #273c75;height: 100%;background: #EEF0F8;">
    <div style="background:#273c75; color:#fff;text-align:center">
        <div style="font-weight:bold;color:#fff;font-size:20px">Navi<span style="color:#ffc700;">B</span>ot</div>
    </div>
    <div style="display:-webkit-box; margin-top:10px">
        <div class="row">
            <div class="column">
                <ul style="list-style-type:none; padding-left:0">
                    <li>
                        <span style="font-weight:bold;">Date</span>
                    </li>
                    <li>
                        <span style="font-weight:bold;">Website</span>
                    </li>
                    <li>
                        <span style="font-weight:bold;">Total Working URLs</span>
                    </li>
                    <li>
                        <span style="font-weight:bold;">Total Not Working URLs</span>
                    </li>
                    <li>
                        <span style="font-weight:bold;">Total URLs of the website</span>
                    </li>
                    <li>
                        <span style="font-weight:bold;">Total Templates</span>
                    </li>
                </ul>
            </div>
            <div class="column">
                <ul style="list-style-type:none; padding-left:0">
                    <li>
                        <span style="font-weight:bold;">Date</span>
                    </li>
                    <li>
                        <span style="font-weight:bold;">Website</span>
                    </li>
                    <li>
                        <span style="font-weight:bold;">Total Working URLs</span>
                    </li>
                    <li>
                        <span style="font-weight:bold;">Total Not Working URLs</span>
                    </li>
                    <li>
                        <span style="font-weight:bold;">Total URLs of the website</span>
                    </li>
                    <li>
                        <span style="font-weight:bold;">Total Templates</span>
                    </li>
                </ul>
            </div>
        </div>
        
    </div>
</body>
</html>

