<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;
use App\Models\ConformanceReportSummary as WCAG;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportSummaryExport;

class HomeController extends Controller
{
    //
    public function index(){
        $data = Session::get('summary');
        $summary = ($data && count($data)) ? $data : [];

        return view('welcome')->with('summary', $summary);
        
    }
    //
    public function WCAG_Issues_Chart(Request $request){
        $output = ['status'=>"error", "message"=>"Failed", "chart"=>[]];
        //
        $summary = Session::get('summary');
        $fname = $request->filename;
        $total_wcag_issues = WCAG::where('file_name', $fname)->sum('fail');
        $output['total_wcag_issues'] = $total_wcag_issues;
        $output['avg_wcag_issues'] = round($total_wcag_issues/$summary['Total Web Audit URLs'], 2);
        session()->put('total_wcag_issues', $total_wcag_issues);
        session()->put('avg_wcag_issues', $output['avg_wcag_issues']);
        $lavel_A_2_fail = WCAG::where('level', 'A')->where('wcag_version', '2')->where('file_name', $fname)->sum('fail');
        $lavel_AA_2_fail = WCAG::where('level', 'AA')->where('wcag_version', '2')->where('file_name', $fname)->sum('fail');
        $lavel_A_2_1_fail = WCAG::where('level', 'A')->where('wcag_version', '2.1')->where('file_name', $fname)->sum('fail');
        $lavel_AA_2_1_fail = WCAG::where('level', 'AA')->where('wcag_version', '2.1')->where('file_name', $fname)->sum('fail');
        //
        $output['chart'] = [
            "labels" => ["WCAG 2.0", "WCAG 2.1"],
            "datasets" => [
                [
                    "label" => "Level A",
                    "backgroundColor"=> "#273C75",
                    "borderColor"=> "#273C75",
                    "borderWidth"=> 1,
                    "borderRadius" => 10,
                    "data"=> [$lavel_A_2_fail, $lavel_AA_2_fail],

                ],
                [
                    "label" => "Level AA",
                    "backgroundColor"=> "#c0392b",
                    "borderColor"=> "#c0392b",
                    "borderWidth"=> 1,
                    "borderRadius" => 10,
                    "data"=> [$lavel_A_2_1_fail, $lavel_AA_2_1_fail]
                ],

            ]
        ];
        $output['status'] = "success";
        //
        return $output;
    }
    public function Severity_Chart(Request $request){
        $output = ['status'=>"error", "message"=>"Failed", "chart"=>[]];
        //
        $summary = Session::get('summary');
        $fname = $request->filename;
        $total_wcag_issues = WCAG::where('file_name', $fname)->sum('fail');
        $output['total_wcag_issues'] = $total_wcag_issues;
        $output['avg_wcag_issues'] = round($total_wcag_issues/$summary['Total Web Audit URLs'], 2);
        $low = WCAG::where('fail','>', 0)->where('file_name', $fname)->sum('severity_low');
        $medium = WCAG::where('fail','>', 0)->where('file_name', $fname)->sum('severity_medium');
        $high = WCAG::where('fail','>', 0)->where('file_name', $fname)->sum('severity_high');
        $na = WCAG::where('fail','>', 0)->where('file_name', $fname)->sum('severity_na');
        //
        $output['chart'] = [
            "labels" => ["Low", "Medium", "High", "NA"],
            "datasets" => [
                [
                    "lable" => "ss",
                    "data"=> [$low, $medium, $high, $na],
                    "backgroundColor" => "#273C75",
                    "borderColor" => "#273C75",
                    "borderRadius" => 10,
                    "borderWidth" => 1
                ]
                

            ]
        ];
        $output['status'] = "success";
        //
        return $output;
    }
    //
    public function TopTenIssues_2_0(Request $request){
        $output = ['status'=>"error", "message"=>"Failed", "chart"=>[]];
        //
        $summary = Session::get('summary');
        $fname = $request->filename;
        //
        $sc_v_2_0 = WCAG::select("sc_name", "fail")->where('wcag_version', '2')->where('file_name', $fname)->orderBy('fail', 'desc')->take(10)->get();
        $labels = [];
        $chartData = [];
        if(count($sc_v_2_0)){
            foreach($sc_v_2_0 as $sc){
                $labels[] = $sc['sc_name'];
                $chartData[] = $sc['fail'];
            }
        }
        $output['chart'] = [
            "labels" => $labels,
            "datasets" => [
                [
                    "data"=> $chartData,
                    "backgroundColor" => "#273C75",
                    "borderColor" => "#273C75",
                    "borderWidth" => 1,
                    "borderRadius" => 10,
                ]
                

            ]
        ];
        $output['sc_2'] = $labels;
        $output['sc_2_1'] = $chartData;
        $output['status'] = "success";
        //
        return $output;
    }
    //
    public function TopTenIssues_2_1(Request $request){
        $output = ['status'=>"error", "message"=>"Failed", "chart"=>[]];
        //
        $summary = Session::get('summary');
        $fname = $request->filename;
        //
        $sc_v_2_1 = WCAG::select("sc_name", "fail")->where('wcag_version', '2.1')->where('file_name', $fname)->orderBy('fail', 'desc')->take(10)->get();
        $labels = [];
        $chartData = [];
        if(count($sc_v_2_1)){
            foreach($sc_v_2_1 as $sc){
                $labels[] = $sc['sc_name'];
                $chartData[] = $sc['fail'];
            }
        }
        $output['chart'] = [
            "labels" => $labels,
            "datasets" => [
                [
                    "data"=> $chartData,
                    "backgroundColor" => "#273C75",
                    "borderColor" => "#273C75",
                    "borderWidth" => 1,
                    "borderRadius" => 10,
                ]
                

            ]
        ];
        $output['status'] = "success";
        //
        return $output;
    }
    //
    public function Conformance_Level_a(Request $request){
        $output = ['status'=>"error", "message"=>"Failed", "chart"=>[]];
        //
        $summary = Session::get('summary');
        $fname = $request->filename;
        //
        $sc_v_2_1 = WCAG::select("sc_name", "fail")->where('level', 'A')->where('file_name', $fname)->get();
        $labels = [];
        $chartData = [];
        if(count($sc_v_2_1)){
            foreach($sc_v_2_1 as $sc){
                $labels[] = $sc['sc_name'];
                $chartData[] = $sc['fail'];
            }
        }
        $output['chart'] = [
            "labels" => $labels,
            "datasets" => [
                [
                    "data"=> $chartData,
                    "backgroundColor" => "#273C75",
                    "borderColor" => "#273C75",
                    "borderWidth" => 1,
                    "borderRadius" => 10,
                ]
                

            ]
        ];
        $output['status'] = "success";
        //
        return $output;
    }
    public function Conformance_Level_aa(Request $request){
        $output = ['status'=>"error", "message"=>"Failed", "chart"=>[]];
        //
        $summary = Session::get('summary');
        $fname = $request->filename;
        //
        $sc_v_2_1 = WCAG::select("sc_name", "fail")->where('level', 'AA')->where('file_name', $fname)->get();
        $labels = [];
        $chartData = [];
        if(count($sc_v_2_1)){
            foreach($sc_v_2_1 as $sc){
                $labels[] = $sc['sc_name'];
                $chartData[] = $sc['fail'];
            }
        }
        $output['chart'] = [
            "labels" => $labels,
            "datasets" => [
                [
                    "data"=> $chartData,
                    "backgroundColor" => "#273C75",
                    "borderColor" => "#273C75",
                    "borderWidth" => 1,
                    "borderRadius" => 10,
                ]
                

            ]
        ];
        $output['status'] = "success";
        //
        return $output;
    }
    //
    public function PdfView(){
        return view("pdf-preview");
    }
    public function GetExcelData($fileName){
        $output = ["status"=>"error", "message"=>"","data"=>[]];
        //return $output;
        $filePath = public_path('uploads/'.$fileName);
        //
        if(!file_exists($filePath)){
            $output['message'] = "File Not Found";
            return $output;
        }
        //
        $sheetnames = ['Summary','ConformanceReport WCAG 2.1 AA'];

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(TRUE);
        //
        $reader->setLoadSheetsOnly($sheetnames);
        $spreadsheet = $reader->load($filePath);
        $summaryData = [];
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $spreadsheet->setActiveSheetIndexByName($sheet->getTitle());
            $objWorksheet = $spreadsheet->getActiveSheet();
            if($sheet->getTitle() === "Summary"){
                $s = $objWorksheet->rangeToArray('B1:C12');
                if(count($s)){
                    foreach($s as $v){
                        $output['summary'][$v[0]] = $v[1];
                    }
                }
                //Session::flash('website', $summaryData[1][1]); 

            }else if($sheet->getTitle() === "ConformanceReport WCAG 2.1 AA"){

                $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
                $highestColumn = "S";//$objWorksheet->getHighestColumn(); // e.g 'F'
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
                //
                
                //
                $headers = [
                    "S.No",
                    "Pages",
                    "URLs",
                    "WCAG SC",
                    "Level",
                    "WCAG Version",
                    "Issue Number",
                    "Testing Method",
                    "Test Result",
                    "Severity",
                    "Users Affected",
                    "Location/Screen",
                    "Issue Descriptions",
                    "Screenshot",
                    "Global Issue?",
                    "Recommendation for Remediation",
                    "Role",
                    "Remediation Details",
                    "Remediation Date"
                ];
                $output['data']['pages'] = [];
                $output['data']['total_pages'] = count($output['data']['pages']);
                $output['data']['wcag'] = [];
                // $output['data']['severity'] = ["low"=>0, "medium"=>0, "high"=>0, "na"=>0];
                // $output['data']['test-results'] = ["pass"=>0, "fail"=>0, "dna"=>0, "na"=>0];
                for ($row = 1; $row <= $highestRow; ++$row) {
                    for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                        $value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                        if($row > 1 && $col == 2 && !in_array($value, $output['data']['pages'])){
                            array_push($output['data']['pages'],$value);
                        }
                        //
                        if($row > 1 && $col == 4){
                            $level = $objWorksheet->getCellByColumnAndRow($col+1, $row)->getValue();
                            $version = $objWorksheet->getCellByColumnAndRow($col+2, $row)->getValue();
                            $test_result = $objWorksheet->getCellByColumnAndRow($col+5, $row)->getValue();
                            $pass = ($test_result == "Pass") ? 1 : 0;
                            $fail = ($test_result == "Fail") ? 1 : 0;
                            $dna = ($test_result == "Does Not Apply") ? 1 : 0;
                            //
                            $severity = $objWorksheet->getCellByColumnAndRow($col+6, $row)->getValue();
                            $severity_low = ($severity == "Low") ? 1 : 0;
                            $severity_medium = ($severity == "Medium") ? 1 : 0;
                            $severity_high = ($severity == "High") ? 1 : 0;
                            $severity_na = ($severity == "NA") ? 1 : 0;
                            //$sc_name_index = array_search($value, array_column($output['data']['wcag'], 'name'));
                            $sc = [
                                "name" => $value,
                                "level" => $level,
                                "version" => $version,
                                "pass" => $pass,
                                "fail"=> $fail,
                                "dna" => $dna,
                                "severity_low" => $severity_low,
                                "severity_medium" => $severity_medium,
                                "severity_high" => $severity_high,
                                "severity_na" => $severity_na
                            ];
                            $arr_k = $value.'_'.$version.'_'.$level;
                            if(!isset($output['data']['wcag'][$arr_k][$value])){
                                $output['data']['wcag'][$arr_k][$value] = $sc;
                            }else{
                                //
                                if($test_result == "Pass"){
                                    $output['data']['wcag'][$arr_k][$value]['pass']++;
                                }else if($test_result == "Fail"){
                                    $output['data']['wcag'][$arr_k][$value]['fail']++;
                                }else if($test_result == "Does Not Apply"){
                                    $output['data']['wcag'][$arr_k][$value]['dna']++;
                                }
                                //
                                if($severity == "Low"){
                                    $output['data']['wcag'][$arr_k][$value]['severity_low']++;
                                }else if($severity == "Medium"){
                                    $output['data']['wcag'][$arr_k][$value]['severity_medium']++;
                                }else if($severity == "High"){
                                    $output['data']['wcag'][$arr_k][$value]['severity_high']++;
                                }else if($severity == "NA"){
                                    $output['data']['wcag'][$arr_k][$value]['severity_na']++;
                                }
                            }
                        }
                    }
                }
                // $output['data']['total_no_of_issues'] = $output['data']['test-results']['fail'];
            }

        }
        if(count($output['data']['pages'])){
            $output['status'] = "success";
            $output['message'] = "success";
        }
        return view('summary',['data'=>$output]);
        // $start = microtime(true);
        // echo "Time Start".$start;
        // echo "<table style='border:1px solid'>";
        // $sno = 1;
        // echo "<thead>";
        // echo "<tr>";
        // echo "<th style='border:1px solid;'>#</th>";
        // echo "<th style='border:1px solid;'>Name</th>";
        // echo "<th style='border:1px solid;'>Level</th>";
        // echo "<th style='border:1px solid;'>Version</th>";
        // echo "<th style='border:1px solid;'>Pass</th>";
        // echo "<th style='border:1px solid;'>Fail</th>";
        // echo "<th style='border:1px solid;'>DNA</th>";
        // echo "<th style='border:1px solid;'>Severity Low</th>";
        // echo "<th style='border:1px solid;'>Severity Medium</th>";
        // echo "<th style='border:1px solid;'>Severity High</th>";
        // echo "<th style='border:1px solid;'>Severity NA</th>";
        // echo "<tr></thead>";
        // echo "<tbody>";
        // foreach ($output['data']['wcag'] as $wcag) {
        //     $wcag_sc = array_values($wcag);
        //     echo "<tr>";
        //     echo "<td style='border:1px solid'>".$sno."</td>";
        //     $sno++;
        //     echo "<td style='border:1px solid'>".$wcag_sc[0]['name']."</td>";
        //     echo "<td style='border:1px solid;text-align: center;'>".$wcag_sc[0]['level']."</td>";
        //     echo "<td style='border:1px solid;text-align: center;'>".$wcag_sc[0]['version']."</td>";
        //     echo "<td style='border:1px solid;text-align: center;'>".$wcag_sc[0]['pass']."</td>";
        //     echo "<td style='border:1px solid;text-align: center;'>".$wcag_sc[0]['fail']."</td>";
        //     echo "<td style='border:1px solid;text-align: center;'>".$wcag_sc[0]['dna']."</td>";
        //     echo "<td style='border:1px solid;text-align: center;'>".$wcag_sc[0]['severity_low']."</td>";
        //     echo "<td style='border:1px solid;text-align: center;'>".$wcag_sc[0]['severity_medium']."</td>";
        //     echo "<td style='border:1px solid;text-align: center;'>".$wcag_sc[0]['severity_high']."</td>";
        //     echo "<td style='border:1px solid;text-align: center;'>".$wcag_sc[0]['severity_na']."</td>";
        //     echo "</tr>";

        // }
        // echo "</tbody></table>";
        // $time_elapsed_secs = microtime(true) - $start;
        // echo "End time ".$time_elapsed_secs;
        // return $output;
    }
    public function SaveChartImg(Request $request){
        $fileName = $request->name;
        $base64string = $request->img;
        $file = public_path('/assets/img/'.$fileName);
        // $base64string = base64_decode($img);
        if($base64string && $fileName){
            return file_put_contents($file, file_get_contents($base64string));
        }
        return false;
    }
    public function ExportSummary($fileName) 
    {
        return Excel::download(new ReportSummaryExport($fileName), 'report.xlsx');
    }
}
