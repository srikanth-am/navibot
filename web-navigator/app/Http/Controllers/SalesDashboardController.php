<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domain;
use Carbon\Carbon;
use Datatables;
use Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CommonController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalesDashboardController extends Controller
{
    //
    protected $SheetsArr = ["Preflight Information", "Not Working URLs"];
    protected $alphabet = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
    protected $reportTitle = array("#","URL");
    protected $DataIndex = array("#","url");
    //
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    public function index(Request $request)
    {
        $data = DB::table('sales_domains')->where("created_by", Auth::user()->id)->orderBy('id', 'DESC');
        if ($request->ajax()) {
            return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('s_time', function ($row) {
                    
                    return Carbon::parse($row->s_time)->format('d-M-Y h:i A');
                })
                ->addColumn('e_time', function ($row) {

                    return Carbon::parse($row->e_time)->format('d-M-Y h:i A');
                })
                ->addColumn('t_utilized', function ($row) {
                    // $timeArr = explode(":", $row->t_utilized);
                    // if(isset($timeArr[2]) && $timeArr[2] > 25){
                    //     return "00:00:25";
                    // }
                    return $row->t_utilized;
                })
                ->addColumn('url_status', function ($row) {
                    if ($row->url_status == "1") {
                        $lable = "<i class='fa fa-hourglass-half fa-spin text-warning mr-1' aria-hidden='true'></i>Processing";

                        return  '<div class="d-flex flex-column w-100 mt-12" title="' . $row->url_progress . '% Completed!">
                                    <span class="text-dark font-size-sm">' . $lable . '</span>
                                    <div class="progress progress-xs w-100 border">
                                        <div class="progress-bar bg-blue" role="progressbar" style="width: ' . $row->url_progress . '%;" aria-valuenow="' . $row->url_progress . '" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>';
                    } else if ($row->url_status == "2") {
                        return "<i class='fa fa-check text-success mr-1' aria-hidden='true'></i>Completed";
                    } else {
                        return "YTS";
                    }
                })->addColumn('temp_status', function ($row) {
                    if ($row->temp_status == "1") {
                        $label = "<i class='fa fa-hourglass-half fa-spin text-warning mr-1' aria-hidden='true'></i>Processing";
                        return  '<div class="d-flex flex-column w-100 mt-12" title="' . $row->temp_progress . '% Completed!">
                                    <span class="text-dark font-size-sm">' . $label . '</span>

                                    <div class="progress progress-xs w-100 border">
                                        <div class="progress-bar bg-blue" role="progressbar" style="width: ' . $row->temp_progress . '%;" aria-valuenow="' . $row->temp_progress . '" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>';
                    } else if ($row->temp_status == "2") {
                        return "<i class='fa fa-check text-success mr-1' aria-hidden='true'></i>Completed";
                    } else {
                        return "YTS";
                    }
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (Auth::user()->role_id == 1) {
                        $btn .= '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm" aria-label="Download as excel file" title="Download as excel file" data-toggle="modal" data-target="#exampleModalScrollable" onclick="ExportReport(' . $row->id . ",'" . $row->url . "'," . $row->total_urls . ')"><i class="fa fa-download text-white"></i></a>';
                        $btn .= '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm ml-2" aria-label="Delete" title="Delete" onclick="deleteDomains(' . $row->id . ')"><i class="fa fa-trash-alt text-white"></i></a>';
                    }else if(Auth::user()->role_id == 3){
                        $btn .= '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm ml-2" aria-label="Delete" title="Delete" onclick="salesExport(' . $row->id . ')"><i class="fa fa-download text-white"></i></a>';
                        $btn .= '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm ml-2" aria-label="Delete" title="Delete" onclick="deleteSalesDomains(' . $row->id . ')"><i class="fa fa-trash-alt text-white"></i></a>';
                    }
                    return $btn;
                })
                ->filter(function ($instance) use ($request) {
                    if (!empty($request->get('search'))) {
                        $instance->where(function ($w) use ($request) {
                            $search = $request->get('search');
                            $w->orWhere('url', 'LIKE', "%$search%")
                                ->orWhere('total_urls', 'LIKE', "%$search%");
                        });
                    }
                })
                ->rawColumns(['url_status', 'temp_status', 'action'])
                ->make(true);
        }
        return view('sales_dashboard');
    }
    
    public function get_time_now()
    {
        return Carbon::now()->format('d-M-Y h:i A');
    }
    public function ExportReport(Request $request){
        $output = ["status" => "error", "message" => "Failed"];
        $domainId = trim($request->domain_id);
        if(!$domainId){
            $output['message'] = "Domain is required";
            return $output;
        }
        $domainExist = DB::table('sales_domains')->where('id', $domainId)->exists();
        if(!$domainExist){
            $output['message'] = "Domain not found or may be deleted";
            return $output;
        }
        $total_urls = DB::table('sales_urls')->select('id')->where('domain_id', $domainId)->where('http_status', '200')->count();
        $total_templates = DB::table('sales_urls')->select('id')->where('domain_id', $domainId)->distinct('template')->where('http_status', '200')->count();
        $dateNow = Carbon::now()->format('d-M-Y');
        $domain_details = DB::table('sales_domains')->where('id', $domainId)->first();
        $ObjSpreadsheet = new Spreadsheet();
        $ObjSpreadsheet->getProperties()->setCreator("Navibot::Web-Navigator - Amnet Systems")
            ->setLastModifiedBy("Navibot")
            ->setTitle(parse_url($domain_details->url, PHP_URL_HOST))
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("This is system generated report - A11");
        $WorkSheet = $ObjSpreadsheet->getActiveSheet();
        $commonObj = new CommonController;
        for ($sheet = 0; $sheet < count($this->SheetsArr); $sheet++) {
            if ($this->SheetsArr[$sheet] == "Preflight Information") {
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                $WorkSheet->mergeCells("A1:B1");
                $WorkSheet->setCellValue("A1", "Preflight Information")->getStyle("A1")->getFont()->setBold(true)->setSize(16);
                $WorkSheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $WorkSheet->setCellValue("A2", "Domain URL")->getStyle("A2")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B2", $domain_details->url)->getStyle("B2")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A3", "Date Time")->getStyle("A3")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B3", Carbon::now()->format('d-M-Y h:i A'))->getStyle("B3")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A4", "Total No of Sample URLs")->getStyle("A4")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B4", $total_urls)->getStyle("B4")->getFont()->setSize(12);
                // $WorkSheet->setCellValue("A5", "Total No of Templates")->getStyle("A5")->getFont()->setBold(true)->setSize(12);
                // $WorkSheet->setCellValue("B5", $total_templates)->getStyle("B5")->getFont()->setSize(12);
                //
                $Row = 5;
                //report headings
                for ($t = 0; $t < count($this->reportTitle); $t++) {
                    $Column = $this->alphabet[$t] . $Row;
                    $WorkSheet->setCellValue($Column, $this->reportTitle[$t]);
                }
                $WorkSheet->getStyle("A5:B5")->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                        'fill' => array(
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('argb' => 'AED6F1')
                        ),
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        )
                    )
                );
                $WorkSheet->getStyle("B2:B4")->applyFromArray(
                    array(
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        )
                    )
                );
                $Row++;
                //End report heading
                $WorkSheet->getColumnDimension('A')->setWidth(30);
                $WorkSheet->getColumnDimension('B')->setWidth(100);
                //
                //
                $data = DB::table("sales_urls")->select('url')->where('domain_id', $domainId)->where('http_status', 200)->orderBy("template", "ASC")->groupBy("url")->get()->toArray();
                $t = '';
                $tempA = [];
                $data = json_decode( json_encode($data), true);
                $sno = 0;
                for ($r = 0; $r < count($data); $r++) {
                    for ($i = 0; $i < count($this->DataIndex); $i++) {
                        $Column = $this->alphabet[$i] . $Row;
                        $Value = "";
                        //
                        if ($i == 0) {
                            $sno++;
                            $Value = $sno;
                        } else {
                            $Value = $data[$r][$this->DataIndex[$i]];
                        }
                        $WorkSheet->setCellValue($Column, $Value);
                    }
                    $Row++;
                }
                //final row
                $WorkSheet->setCellValue("B4", "=COUNTA(A6:A" . $WorkSheet->getHighestRow() . ")");
                $WorkSheet->getStyle("A1:B" . $ObjSpreadsheet->getActiveSheet()->getHighestRow())->applyFromArray(
                    array(
                        'borders' => array(
                            'allBorders' => array(
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        ),
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            //'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        )
                    )
                );
            } else if ($this->SheetsArr[$sheet] == "Not Working URLs") {
                $WorkSheet = $ObjSpreadsheet->createSheet($sheet);
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                $titleArr = ["#", "URLs", "Comments"];
                $Row = 1;
                for ($t = 0; $t < count($titleArr); $t++) {
                    $Column = $this->alphabet[$t] . $Row;
                    $WorkSheet->setCellValue($Column, $titleArr[$t]);
                }
                $WorkSheet->getStyle("A1:C1")->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                        'fill' => array(
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('argb' => 'AED6F1')
                        )
                    )
                );
                $Row++;
                $WorkSheet->getColumnDimension('B')->setWidth('100');
                $WorkSheet->getColumnDimension('C')->setAutoSize(true);
                $DataIndex = array("#", "url", "http_status");
                $data = DB::table('sales_urls')->select('url', 'http_status')->where('domain_id', $domainId)->where('http_status', "!=", 200)->orderBy("id", "ASC")->groupBy("url")->get()->toArray();
                $data = json_decode( json_encode($data), true);
                $ManualTestingNeeded = [500, 502, 503, 504, 0, 410, 302, 203, 100, 403];
                if (count($data)) {
                    for ($r = 0; $r < count($data); $r++) {
                        for ($i = 0; $i < count($DataIndex); $i++) {
                            $Column = $this->alphabet[$i] . $Row;
                            $Value = "";
                            if ($DataIndex[$i] == "#") {
                                $Value = $r + 1;
                            } else {
                                $Value = $data[$r][$DataIndex[$i]];
                                if ($DataIndex[$i] == "http_status") {
                                    $Value = $commonObj->get_http_status_message($data[$r][$DataIndex[$i]]);

                                    if (in_array($data[$r][$DataIndex[$i]], $ManualTestingNeeded)) {
                                        $WorkSheet
                                            ->getStyle($Column)
                                            ->getFill()
                                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                            ->getStartColor()
                                            ->setARGB('FFFFFF00');
                                    }
                                }
                            }
                            $WorkSheet->setCellValue($Column, $Value);
                        }
                        $Row++;
                    }
                } else {
                    $Column = $this->alphabet[0] . $Row;
                    $WorkSheet->setCellValue($Column, "No records found");
                }
                $WorkSheet->getStyle("A1:C" . $WorkSheet->getHighestRow())->applyFromArray(
                    array(
                        'borders' => array(
                            'allBorders' => array(
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        ),
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            //'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        )
                    )
                );
            }
        }
        $ObjSpreadsheet->setActiveSheetIndex(0);
        $DownloadName = parse_url($domain_details->url, PHP_URL_HOST) . " - " . $dateNow . ".xlsx";
        ob_start();
        $Writer = new Xlsx($ObjSpreadsheet);
        $Writer->save('php://output');
        $xlsData = ob_get_contents();
        $fileData = base64_encode($xlsData);
        ob_end_clean();
        if ($fileData) {
            $output['status'] = "success";
            $output['message'] = "Downloaded Successfully";
            $output['filename'] = $DownloadName;
            $output['file'] = "data:application/vnd.ms-excel;base64," . $fileData;
        } else {
            $output['status'] = "error";
            $output['message'] = "File data is empty";
        }
        //
        $fileData = '';
        return $output;
    }
    //
    public function DeleteDomain($id){
        $output = ["status" => "error", "message" => "failed"];
        $res = DB::table('sales_urls')->where('domain_id', '=', $id)->delete();
        $domains = DB::table('sales_domains')->where('id', '=', $id)->delete();
        //
        $output["status"] = "success";
        $output["message"] = "A domain deleted successfully";
        return $output;
    }
    //

}
