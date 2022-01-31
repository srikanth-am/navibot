<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ConformanceReportSummary as WCAG;
//use LMPDF;
// use Mpdf\Mpdf;
// use Mpdf\Output\Destination;
use Session;
use LaravelPDF;
use Spatie\Browsershot\Browsershot;
// use VerumConsilium\Browsershot\Facades\PDF;
// use VerumConsilium\Browsershot\Facades\Screenshot;
// use Spatie\Browsershot\Manipulations;


// require_once __DIR__ . '/vendor/autoload.php';
class FileUploadController extends Controller
{
    //
    protected $mimes = [];
    public function __construct(){
        $this->mimes = ['xlsx', 'xls', 'xlsm'];
    }
    public function index(){
        return view('file_upload');
    }
    public function uploadToServer(Request $request)
    {
        $output = ['status'=>"error", "message"=>"Failed", "fname"=>''];
        
        if($request->file()) {
            $extension = request()->file->getClientOriginalExtension();
            if(!in_array($extension, $this->mimes)){
                $output['message'] = "Excel file only allowed";
                return $output;
            }
            $name = request()->file->getClientOriginalName();//.'.'.request()->file->getClientOriginalExtension();
            $request->file->move(public_path('uploads'), $name);
            $output['fname'] = $name;
            $output['status'] = "success";
            $output['message'] = "Successfully Uploaded";
        }else{
            $output['message'] = "File is required";

        }
        return $output;
    }
    //
    public function ParseAndSaveExcelData(Request $request){
        $output = ["status"=>"error", "message"=>"Failed"];
        $fname = $request->fname;
        if(!$fname){
            $output['message'] = "Invalid Request";
            return $output;
        }
        //
        $filePath = public_path('uploads/'.$fname);
        //
        if(!file_exists($filePath)){
            $output['message'] = "File Not Found";
            return $output;
        }
        //
        $sheetnames = ['Summary','ConformanceReport WCAG 2.1 AA'];
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(TRUE);
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
                        $summaryData[$v[0]] = $v[1];
                    }
                }
                $total_wcag_issues = WCAG::where('file_name', $fname)->sum('fail');
                $summaryData['total_wcag_issues'] = $total_wcag_issues;
                $summaryData['avg_wcag_issues'] = round($total_wcag_issues/$summaryData['Total Web Audit URLs'], 2);
                session()->put('summary', $summaryData);

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
                $output['data']['severity'] = ["low"=>0, "medium"=>0, "high"=>0, "na"=>0];
                $output['data']['test-results'] = ["pass"=>0, "fail"=>0, "dna"=>0, "na"=>0];
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
            }

        }
        //
        $insertArr = [];
        WCAG::truncate();

        $order = 1;
        foreach ($output['data']['wcag'] as $wcag) {
            $wcag_sc = array_values($wcag);
            $wcag_sc = $wcag_sc[0];
            $insertArr[] = [
                'file_name' => $fname,
                'order' => $order,
                'sc_name' => $wcag_sc['name'],
                'wcag_version' => $wcag_sc['version'],
                'level' => $wcag_sc['level'],
                'pass' => $wcag_sc['pass'],
                'fail' => $wcag_sc['fail'],
                'dna' => $wcag_sc['dna'],
                'severity_low' => $wcag_sc['severity_low'],
                'severity_medium' => $wcag_sc['severity_medium'],
                'severity_high' => $wcag_sc['severity_high'],
                'severity_na' => $wcag_sc['severity_na'],
            ];
            $order++;
        }
        $insert = WCAG::insert($insertArr);
        if($insert){
            $output['status'] = "success";
            $output['message'] = "Process completed successfully";
            unset($output['data']);
        }
        return $output;
       
    }
    //
    public function ExportAsPdf (Request $request) {
       
        $data = Session::get('summary');
        $domain = parse_url($data['Website']);
        $view = view('pdf-preview',['summary'=>$data])->render();
        $fileName = $domain['host'].".pdf";
        $file = public_path($fileName);
        $url = url('/pdf-preview');
        // die($url);
        $width = 297;
        $height = 820; 
        $pdf = Browsershot::html($view)
        ->showBrowserHeaderAndFooter()
        ->margins(0, 0, 0, 0)
        ->format('A3')
        ->showBackground()// ->pages('1-2')
        // ->waitUntilNetworkIdle()
        // ->paperSize($width, $height)
        ->save($file);
        return response()->download($file, $fileName);


    }
}

