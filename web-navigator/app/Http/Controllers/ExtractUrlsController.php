<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\CommonController;
use App\Jobs\GetUrls;
use App\Models\Domain;
use App\Models\Url;
use Carbon\Carbon;
use Session;
use App\Mail\SendProcessCompletedEmail;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Jobs\DownloadMaster;

class ExtractUrlsController extends Controller
{
    protected $domain = '';
    protected $scheme = '';
    protected $domainId = '';
    protected $limit = 100000;
    protected $types = ['sitemap', 'html'];
    protected $SheetsArr = ["Effort Estimation", "Preflight Information", "Not Working URLs"];
    protected $alphabet = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
    protected $reportTitle = array("Template", "URL", "Web Audit", "Audit Testing Hours", "Comments");
    protected $DataIndex = array("template", "url", "web_audit", "audit_testing_hours", "http_status");
    protected $AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_1 = 25;
    protected $AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_2 = 25;
    protected $AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_3 = 12;
    //
    protected $AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_1 = 12;
    protected $AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_2 = 25;
    protected $AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_3 = 12;
    //
    protected $AMNET_COST_PER_HOUR_ASSOCIATE = 20;
    protected $AMNET_COST_PER_HOUR_IN_HOUSE = 5;
    protected $ESTIMATED_HOURS_IN_HOUSE_QA = 10;
    //
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    public function index()
    {
        return view("crawl_urls");
    }
    public function get_urls(Request $request)
    {
        $output = ["status" => "error", "message" => "Failed"];
        //
        //return $output;
        $domain = trim($request->domain);
        $urlArr = parse_url($domain);
        $domain = $urlArr["scheme"] . "://" . $urlArr["host"];
        $query_str = $request->query_str;
        //
        $commonObj = new CommonController;
        //
        if (!$commonObj->isValidUrl($domain)) {
            $output['message'] = "Invalid URL";
            return $output;
        }
        //
        $data = $commonObj->get_page_data($domain);
        //
        if ($data['status_code'] != 200) {
            $output['message'] = $data['status_code'] . " " . $data['error_message'];
            return $output;
        }
        $q = ($query_str == 'yes') ? 1 : 0;
        //return $q;
        $d = Domain::select('id')->where('url', $domain)->where('query_string', $q)->get()->first();
        if (!$d) {
            $ins = new Domain;
            $ins->url = $domain;
            $ins->query_string = $q;
            $ins->http_status = $data['status_code'];
            $ins->url_status = 1;
            $ins->s_time = Carbon::now();
            $ins->save();
            $this->domainId = $ins->id;
        } else {
            $this->domainId = $d['id'];
        }
        $output["domain_id"] = $this->domainId;
        $output['domain'] = $domain;
        $output['query_str'] = $query_str;
        //
        $output['status'] = "success";
        $output['message'] = "Process Initiated Successfully";
        return $output;
    }
    public function start_bg_process(Request $request)
    {
        $domain = trim($request->domain);
        $query_str = $request->query_str;
        $domainId = trim($request->domainId);

        Session::put('domain', $domain);
        Session::put('domainId', $domainId);
        GetUrls::dispatchAfterResponse($domain, $domainId, $query_str);
    }

    public function ExportReport(Request $request)
    {
        $output = ["status" => "error", "message" => "Failed"];
        // return $output;
        $h = trim($request->h);
        $m = trim($request->m);
        $domainId = trim($request->domain_id);
        $currency = trim($request->currency);
        $n_of_resource = trim($request->resource);
        if ($currency == "EURO") {
            $this->AMNET_COST_PER_HOUR_ASSOCIATE = $this->AMNET_COST_PER_HOUR_ASSOCIATE / 1.22;
            $this->AMNET_COST_PER_HOUR_IN_HOUSE = $this->AMNET_COST_PER_HOUR_IN_HOUSE / 1.22;
            //
            $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_1 = $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_1 / 1.22;
            $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_2 = $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_2 / 1.22;
            $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_3 = $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_3 / 1.22;
            //
            $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_1 = $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_1 / 1.22;
            $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_2 = $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_2 / 1.22;
            $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_3 = $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_3 / 1.22;
            //
        } else if ($currency == "POUND") {
            $this->AMNET_COST_PER_HOUR_ASSOCIATE = $this->AMNET_COST_PER_HOUR_ASSOCIATE / 1.31;
            $this->AMNET_COST_PER_HOUR_IN_HOUSE = $this->AMNET_COST_PER_HOUR_IN_HOUSE / 1.31;
            //
            $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_1 = $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_1 / 1.31;
            $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_2 = $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_2 / 1.31;
            $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_3 = $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_3 / 1.31;
            //
            $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_1 = $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_1 / 1.31;
            $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_2 = $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_2 / 1.31;
            $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_3 = $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_3 / 1.31;
        } else if ($currency == "CAD") {
            $this->AMNET_COST_PER_HOUR_ASSOCIATE = $this->AMNET_COST_PER_HOUR_ASSOCIATE / 0.77;
            $this->AMNET_COST_PER_HOUR_IN_HOUSE = $this->AMNET_COST_PER_HOUR_IN_HOUSE / 0.77;
            //
            $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_1 = $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_1 / 0.77;
            $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_2 = $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_2 / 0.77;
            $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_3 = $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_3 / 0.77;
            //
            $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_1 = $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_1 / 0.77;
            $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_2 = $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_2 / 0.77;
            $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_3 = $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_3 / 0.77;
        }
        $currencyArr = [
            "USD" => '_("$" * #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)',
            "EURO" => '_("€"* #,##0.00_);_("€"* \(#,##0.00\);_("€"* "-"??_);_(@_)',
            "POUND" => '_("£"* #,##0.00_);_("£"* \(#,##0.00\);_("£"* "-"??_);_(@_)',
            "CAD" => '_("CAD"* #,##0.00_);_("CAD"* \(#,##0.00\);_("CAD"* "-"??_);_(@_)',
        ];
        $currency_format = $currencyArr[$currency];
        $numberFormats = [
            'percentage' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00,
        ];
        $dollerSign = "";
        $percentage = "";
        //
        if (!Domain::where('id', $domainId)->exists()) {
            $output['message'] = "Domain Not Found or Maybe deleted";
            return $output;
        }
        //
        $total_urls = Url::select('id')->where('domain_id', $domainId)->where('http_status', '200')->count();
        $total_urls_unique = Url::select('id')->where('domain_id', $domainId)->distinct('url')->where('http_status', '200')->count();
        $html_count = Url::select('id')->where('domain_id', $domainId)->where('type', 'html')->where('http_status', '200')->count();
        $crawled = Url::select('id')->where('domain_id', $domainId)->where('is_crawled', 1)->where('http_status', '200')->count();
        $total_templates = Url::select('id')->where('domain_id', $domainId)->distinct('template')->where('http_status', '200')->count();
        if ($html_count == 0) {
            $output['message'] = "Html wise urls not found";
            return $output;
        }
        if ($total_urls != $crawled) {
            $output['message'] = "Domain is processing urls";
            return $output;
        }
        if ($total_urls_unique > 5000) {
            DownloadMaster::dispatchAfterResponse($domainId, $h, $m, $n_of_resource, $currency);
            return ["status" => "success", "message" => "Process started. you will receive the report by email once it done!"];
            exit();
        }
        //
        $dateNow = Carbon::now()->format('d-M-Y');
        $domain_details = Domain::where('id', $domainId)->first();
        $ObjSpreadsheet = new Spreadsheet();
        $ObjSpreadsheet->getProperties()->setCreator("Navibot::Amnet Systems")
            ->setLastModifiedBy("Navibot")
            ->setTitle(parse_url($domain_details['url'], PHP_URL_HOST) . " - Audit Proposal")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("This is system generated report - A11");
        $WorkSheet = $ObjSpreadsheet->getActiveSheet();
        //
        $timeZone = \PhpOffice\PhpSpreadsheet\Shared\Date::setDefaultTimezone("en_us");
        //
        $hours = $h . ":" . $m . ':00';
        $secs = strtotime($hours) - strtotime("00:00:00");
        $time = gmdate("H:i:s", $secs);

        $timestamp = new \DateTime($time);
        $excelTimestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($timestamp);
        $excelDate = floor($excelTimestamp);
        $v = $excelTimestamp - $excelDate;
        $WorkSheet->setCellValue("AZ1", $v);
        $WorkSheet->getStyle("AZ1")->getNumberFormat()->setFormatCode("[h]:mm:ss");
        $commonObj = new CommonController;
        //
        for ($sheet = 0; $sheet < count($this->SheetsArr); $sheet++) {
            if ($this->SheetsArr[$sheet] == "Preflight Information") {
                $WorkSheet = $ObjSpreadsheet->createSheet($sheet);
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                //$WorkSheet->setTitle($this->SheetsArr[$sheet]);
                $WorkSheet->mergeCells("A1:D1");
                $WorkSheet->setCellValue("A1", "Preflight Report")->getStyle("A1")->getFont()->setBold(true)->setSize(16);
                $WorkSheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                //Informations
                $WorkSheet->mergeCells("B2:D2");
                $WorkSheet->mergeCells("B3:D3");
                $WorkSheet->mergeCells("B4:D4");
                $WorkSheet->mergeCells("B5:D5");
                $WorkSheet->mergeCells("B6:D6");
                $WorkSheet->mergeCells("B7:D7");
                //
                $WorkSheet->setCellValue("A2", "Domain URL")->getStyle("A2")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B2", $domain_details['url'])->getStyle("B2")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A3", "Date Time")->getStyle("A3")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B3", Carbon::now()->format('d-M-Y h:i A'))->getStyle("B3")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A4", "Total No of URLs")->getStyle("A4")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B4", $total_urls_unique)->getStyle("B4")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A5", "Total No of Templates")->getStyle("A5")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B5", $total_templates)->getStyle("B5")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A6", "Web Audit Testing Total Hours")->getStyle("A6")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B6", "")->getStyle("B6")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A7", "Web Audit Testing Total Pages")->getStyle("A7")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B7", "")->getStyle("B7")->getFont()->setSize(12);
                //
                $Row = 8;

                //report headings
                for ($t = 0; $t < count($this->reportTitle); $t++) {
                    $Column = $this->alphabet[$t] . $Row;
                    $WorkSheet->setCellValue($Column, $this->reportTitle[$t]);
                }
                $WorkSheet->getStyle("A8:E8")->applyFromArray(
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
                $WorkSheet->getStyle("B2:B7")->applyFromArray(
                    array(
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        )
                    )
                );
                $WorkSheet->getStyle("A1:D8")->applyFromArray(
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
                $Row++;
                //End report headings
                $WorkSheet->getColumnDimension('A')->setWidth(33);
                $WorkSheet->getColumnDimension('B')->setWidth(90);
                $WorkSheet->getColumnDimension('C')->setWidth(15);
                $WorkSheet->getColumnDimension('D')->setWidth(19);
                $WorkSheet->getColumnDimension('E')->setWidth(25);
                //
                //
                $t = '';
                $web_audit = [];
                $web_audit_time = [];
                $hours = $h . ":" . $m . ':00';
                $secs = strtotime($hours) - strtotime("00:00:00");
                $time = gmdate("H:i:s", $secs);
                
                $timestamp = new \DateTime($time);
                $excelTimestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($timestamp);
                $excelDate = floor($excelTimestamp);
                $v = $excelTimestamp - $excelDate;
                $WorkSheet->setCellValue("AZ1", $v);
                $WorkSheet->getStyle("AZ1")->getNumberFormat()->setFormatCode("[h]:mm:ss");
                $tempA = [];
                $data = Url::select('url', 'template', 'http_status')->where('domain_id', $domainId)->where('http_status', 200)->orderBy("template", "ASC")->groupBy("url")->get()->toArray();
                // protected $DataIndex = array("template", "url", "web_audit", "audit_testing_hours", "http_status");
                for ($r = 0; $r < count($data); $r++) {
                    for ($i = 0; $i < count($this->DataIndex); $i++) {
                        $Column = $this->alphabet[$i] . $Row;
                        $Value = "";
                        if (isset($data[$r][$this->DataIndex[$i]])) {
                            if ($i == 0) {
                                $t = str_pad($data[$r]["template"], 2, "0", STR_PAD_LEFT);
                                $ddd = "Template " . $t;
                                $Value = $ddd;
                            } else {
                                $Value = $data[$r][$this->DataIndex[$i]];
                            }
                        } else {
                            if (!isset($tempA[$data[$r]["template"]])) {
                                $tempA[$data[$r]["template"]] = Url::select('id')->where('domain_id', $domainId)->distinct('url')->where('template', $data[$r]["template"])->where('http_status', '200')->count();
                            }
                            $t_u = $tempA[$data[$r]["template"]];
                            $total_page_audit = $commonObj->total_pages_for_audit($t_u);
                            if ($this->DataIndex[$i] == "web_audit") {
                                $Value = "";
                                if ($total_page_audit != 0) {
                                    if (!isset($web_audit[$data[$r]["template"]])) {
                                        $web_audit[$data[$r]["template"]] = $total_page_audit;
                                    }

                                    if ($web_audit[$data[$r]["template"]] > 0) {
                                        $Value = '1';
                                        $WorkSheet->setCellValue("D" . $Row, $Value);
                                        $web_audit[$data[$r]["template"]]--;
                                    }
                                }
                            } else if ($this->DataIndex[$i] == "audit_testing_hours") {
                                $cell = $this->alphabet[$i - 1] . $Row;
                                $Value = '=IF(' . $cell . '>0, ' . $cell . '*$AZ$1, "")';
                            }
                        }
                        if ($this->DataIndex[$i] == "http_status") {
                            $Value = "";
                        }
                        $WorkSheet->setCellValue($Column, $Value);
                    }
                    $Row++;
                }
                $web_audit = [];
                $web_audit_time = [];
                //final row
                $lastBeforeCell = $Row - 1;
                $WorkSheet->setCellValue("B4", "=COUNTA(B9:B" . $WorkSheet->getHighestRow() . ")");
                // $WorkSheet->setCellValue("B5", "=SUMPRODUCT(COUNTIF(A9:A" . $WorkSheet->getHighestRow() . ",A9:A" . $WorkSheet->getHighestRow() . "))");
                $WorkSheet->setCellValue("B6", "=SUM(D9:D" . $WorkSheet->getHighestRow() . ")");
                $WorkSheet->setCellValue("B7", '=SUM(C9:C' . $WorkSheet->getHighestRow() . ')');
                $WorkSheet->setCellValue("B" . $Row, "Total");
                $WorkSheet->setCellValue("C" . $Row, '=SUM(C9:C' . $lastBeforeCell . ')');
                $WorkSheet->setCellValue("D" . $Row, "=SUM(D9:D" . $lastBeforeCell . ")");
                //
                $WorkSheet->getStyle("B6")->getNumberFormat()->setFormatCode("[h]:mm:ss");
                $WorkSheet->getStyle("D9:D" . $WorkSheet->getHighestRow())->getNumberFormat()->setFormatCode("[h]:mm:ss");
                $WorkSheet->getStyle("B" . $Row . ":E" . $Row)->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        )
                    )
                );
                $WorkSheet->getStyle("A8:E" . $ObjSpreadsheet->getActiveSheet()->getHighestRow())->applyFromArray(
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
            } else if ($this->SheetsArr[$sheet] == "Effort Estimation") {
                //$WorkSheet = $ObjSpreadsheet->createSheet($sheet);
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                //
                $WorkSheet->mergeCells("A1:D1");
                $WorkSheet->setCellValue("A1", "Effort Estimations")->getStyle("A1")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                //
                $WorkSheet->setCellValue("A2", "Domain URL")->getStyle("A2")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B2", $domain_details['url'])->getStyle("B2")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A3", "Date Time")->getStyle("A3")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B3", Carbon::now()->format('d-M-Y h:i A'))->getStyle("B3")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A4", "Total No of URLs")->getStyle("A4")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B4", '')->getStyle("B4")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A5", "Total No of Templates")->getStyle("A5")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B5", '')->getStyle("B5")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A6", "Web Audit Testing Total Hours")->getStyle("A6")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B6", "")->getStyle("B6")->getFont()->setSize(12);
                $WorkSheet->setCellValue("A7", "Web Audit Testing Total Pages")->getStyle("A7")->getFont()->setBold(true)->setSize(12);
                $WorkSheet->setCellValue("B7", "")->getStyle("B7")->getFont()->setSize(12);
                //
                $WorkSheet->getStyle("B2:B7")->applyFromArray(
                    array(
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        )
                    )
                );
                //
                $Row = 8;
                $titleArr = ["Templates", "Total Pages", "Total Pages Selected for Audit", "Estimated Total Hours"];
                for ($t = 0; $t < count($titleArr); $t++) {
                    $Column = $this->alphabet[$t] . $Row;
                    $WorkSheet->setCellValue($Column, $titleArr[$t]);
                    //$WorkSheet->getStyle($Column)->getFont()->setBold(true);
                }
                $WorkSheet->getStyle("A8:D8")->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ),
                        'fill' => array(
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('argb' => 'AED6F1')
                        )
                    )
                );
                $Row++;
                $WorkSheet->getColumnDimension('A')->setAutoSize(true);
                $WorkSheet->getColumnDimension('B')->setWidth('40');
                $WorkSheet->getColumnDimension('C')->setAutoSize(true);
                $WorkSheet->getColumnDimension('D')->setAutoSize(true);
                //$t_name = "Template - ";
                $template = 0;
                $tmp_arr = Url::select("template")->where('domain_id', $domainId)->where('http_status', '200')->orderBy("template", "ASC")->groupBy("template")->get()->toArray();
                for ($i = 0; $i < count($tmp_arr); $i++) {
                    $temp_no = $tmp_arr[$i]['template'];
                    //$template++;
                    $name = "Template " . str_pad($temp_no, 2, "0", STR_PAD_LEFT);
                    //$t_u = Url::select('id')->where('domain_id', $domainId)->distinct('url')->where('template', $temp_no)->where('http_status', '200')->count();
                    // $total_page_audit = $commonObj->total_pages_for_audit($t_u);
                    $loop = 3;
                    for ($d = 0; $d <= $loop; $d++) {
                        $column = $this->alphabet[$d] . $Row;
                        $value = '';
                        if ($d == 0) {
                            $value = $name;
                        } else if ($d == 1) {
                            //$value = $t_u;
                            $value = "=COUNTIF('Preflight Information'!" . '$A:$A' . ",'Effort Estimation'!$" . 'A' . $Row . ")";
                        } else if ($d == 2) {
                            //$value = $total_page_audit;
                            $value = "=SUMIF('Preflight Information'!" . '$A:$A' . ",'Effort Estimation'!$" . 'A' . $Row . ",'Preflight Information'!" . '$C:$C' . ")";
                        } else if ($d == 3) {
                            $value = "=SUMIF('Preflight Information'!" . '$A:$A' . ",'Effort Estimation'!$" . 'A' . $Row . ",'Preflight Information'!" . '$D:$D' . ")";
                            // $value = "=" . $this->alphabet[$d - 1] . $Row . '*$AZ$1'; //$v;
                        }
                        $WorkSheet->setCellValue($column, $value);
                    }
                    $Row++;
                }
                $timeFormatCode = "[h]:mm:ss";
                //appling formulas
                $WorkSheet->setCellValue("B4", "=SUM(B9:B" . $WorkSheet->getHighestRow() . ")");
                $WorkSheet->setCellValue("B5", "=COUNTA(A9:A" . $WorkSheet->getHighestRow() . ")");
                $WorkSheet->setCellValue("B6", "=SUM(D9:D" . $WorkSheet->getHighestRow() . ")");
                $WorkSheet->setCellValue("B7", "=SUM(C9:C" . $WorkSheet->getHighestRow() . ")");
                //format as time format
                $WorkSheet->getStyle("B6")->getNumberFormat()->setFormatCode($timeFormatCode);
                $WorkSheet->getStyle("D1:D" . $WorkSheet->getHighestRow())->getNumberFormat()->setFormatCode($timeFormatCode);
                //Final row
                $lastBeforeCell = $Row - 1;
                $WorkSheet->setCellValue("A" . $Row, "Total");
                $WorkSheet->setCellValue("B" . $Row, "=SUM(B9:B" . $lastBeforeCell . ")");
                $WorkSheet->setCellValue("C" . $Row, "=SUM(C9:C" . $lastBeforeCell . ")");
                $WorkSheet->setCellValue("D" . $Row, "=SUM(D9:D" . $lastBeforeCell . ")");
                $WorkSheet->getStyle("D" . $Row)->getNumberFormat()->setFormatCode($timeFormatCode);
                $WorkSheet->getStyle("A" . $Row . ":D" . $Row)->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                        'fill' => array(
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('argb' => 'FFFFFF00')
                        ),
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                        )
                    )
                );
                //
                $WorkSheet->getStyle("A1:D" . $WorkSheet->getHighestRow())->applyFromArray(
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
                //second table
                $heading_arr = [
                    "Mode",
                    "Total URLs",
                    "Total Templates",
                    "Total URLs for Audit",
                    "Average URLs for Audit",
                    "Estimated Hours",
                    "Amnet/Vendor Cost Per Hour",
                    "Total Cost",
                    "Amnet Price Per Hour",
                    "Estimated Project Value",
                    "GP %",
                    "TAT",
                    "Average Audit Hours for a Page",
                    "Audit Sample %"
                ];
                //
                $Row = 3;
                for ($t = 0; $t < count($heading_arr); $t++) {
                    $Column = $this->alphabet[$t + 5] . $Row;
                    $WorkSheet->setCellValue($Column, $heading_arr[$t]);
                }
                $Row++;
                $WorkSheet->getStyle("F3:S3")->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ),
                        'fill' => array(
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('argb' => 'AED6F1')
                        )
                    )
                );
                $WorkSheet->getStyle('F3:S3')->getAlignment()->setWrapText(true);
                $WorkSheet->getRowDimension('3')->setRowHeight(30);
                //
                $TblVal_1 = [
                    ['Associate', '=$B$4', '=$B$5', '=$B$7', '=I4/H4', '=$B$6*24', $this->AMNET_COST_PER_HOUR_ASSOCIATE, '=L4*K4', $this->AMNET_PRICE_PER_HOUR_ASSOCIATE_TBL_1, '=N4*K4', '=1-(M4/O4)', '', '', '=I4/G4'],
                    ['In-house', '', '', '', '', '=(K4*15%)', $this->AMNET_COST_PER_HOUR_IN_HOUSE, '=L5*K5', $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_1, '=N5*K5', '=1-(M5/O5)', '', '', ''],
                    ['Total', '=G4', '=H4', '=I4', '=J4', '=SUM(K4:K5)', '', '=SUM(M4:M5)', '', '=SUM(O4:O5)', '=1-(M6/O6)', '', '=K6/I6', ''],
                    ['Average', '', '', '=I6/H6', '', '=K6/I6', '', '', '', '', '', '=K6/' . $n_of_resource * 8, '', ''],
                ];
                for ($t = 0; $t < count($TblVal_1); $t++) {
                    for ($u = 0; $u < count($TblVal_1[$t]); $u++) {
                        $Column = $this->alphabet[$u + 5] . $Row;
                        $WorkSheet->setCellValue($Column, $TblVal_1[$t][$u]);
                    }
                    $Row++;
                }
                //
                $WorkSheet->getStyle('I7')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle('J4:J6')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle('K4:K6')->getNumberFormat()->setFormatCode('#,##0');
                $WorkSheet->getStyle('K7')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle("L4:L6")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("M4:M6")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("N4:N5")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("O4:O6")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("P4:P6")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $WorkSheet->getStyle('Q7')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle('R6')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle("S4")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $WorkSheet->getStyle("F6:S7")->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                    )
                );
                //
                $WorkSheet->getStyle("F3:S7")->applyFromArray(
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
                //
                $Row++;
                $TblVal_2 = [
                    ['In-house', '=$B$4', '=$B$5', '=$B$7', '=I10/H10', '=$B$6*24', $this->AMNET_COST_PER_HOUR_IN_HOUSE, '=L10*K10', $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_2, '=N10*K10', '=1-(M10/O10)', '', '', '=I10/G10'],
                    ['Language Editing', '', '', '', '', '=(K10*15%)', $this->AMNET_COST_PER_HOUR_IN_HOUSE, '=L11*K11', $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_2, '=N11*K11', '=1-(M11/O11)', '', '', ''],
                    ['Total', '=G10', '=H10', '=I10', '=J10', '=SUM(K10:k11)', '', '=SUM(M10:M11)', '', '=SUM(O10:O11)', '=1-(M12/O12)', '', '=K12/I12', ''],
                    ['Average', '', '', '=I12/H12', '', '=K12/I12', '', '', '', '', '', '=K12/' . $n_of_resource * 8, '', ''],
                ];
                $cellValues = $WorkSheet->rangeToArray('F3:S3');
                $WorkSheet->fromArray($cellValues, null, 'F9');
                $WorkSheet->setCellValue("L9", "Amnet Cost Per Hour");
                $Row++;
                for ($t = 0; $t < count($TblVal_2); $t++) {
                    for ($u = 0; $u < count($TblVal_2[$t]); $u++) {
                        $Column = $this->alphabet[$u + 5] . $Row;
                        $WorkSheet->setCellValue($Column, $TblVal_2[$t][$u]);
                    }
                    $Row++;
                }
                $WorkSheet->getStyle("F9:S9")->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ),
                        'fill' => array(
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('argb' => 'AED6F1')
                        )
                    )
                );
                $WorkSheet->getStyle('F9:S9')->getAlignment()->setWrapText(true);
                $WorkSheet->getRowDimension('9')->setRowHeight(30);
                $WorkSheet->getStyle("F12:S13")->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                    )
                );
                //
                $WorkSheet->getStyle("F9:S13")->applyFromArray(
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
                $WorkSheet->getStyle('I13')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle('J10:J13')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle('K10:K11')->getNumberFormat()->setFormatCode('#,##0');
                $WorkSheet->getStyle('K12')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle("L10:L13")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("M10:M13")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("N10:N13")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("O10:O13")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("P10:P13")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $WorkSheet->getStyle('Q13')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle('R13')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle("S10")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                //
                $Row++;
                $TblVal_3 = [
                    ['In-house', '=$B$4', '=$B$5', '=$B$7', '=I10/H10', '=$B$6*24', $this->AMNET_COST_PER_HOUR_IN_HOUSE, '=L16*K16', $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_3, '=N16*K16', '=1-(M16/O16)', '', '', '=I16/G16'],
                    ['Language Editing', '', '', '', '', '=(K16*15%)', $this->AMNET_COST_PER_HOUR_IN_HOUSE, '=L17*K17', $this->AMNET_PRICE_PER_HOUR_IN_HOUSE_TBL_3, '=N17*K17', '=1-(M17/O17)', '', '', ''],
                    ['Total', '=G16', '=H16', '=I16', '=J16', '=SUM(K16:K17)', '', '=SUM(M16:M17)', '', '=SUM(O16:O17)', '=1-(M18/O18)', '', '=K18/I18', ''],
                    ['Average', '', '', '=I18/H18', '', '=K18/I18', '', '', '', '', '', '=K18/' . $n_of_resource * 8, '', ''],
                ];
                $cellValues = $WorkSheet->rangeToArray('F9:S9');
                $WorkSheet->fromArray($cellValues, null, 'F15');
                $Row++;
                for ($t = 0; $t < count($TblVal_3); $t++) {
                    for ($u = 0; $u < count($TblVal_3[$t]); $u++) {
                        $Column = $this->alphabet[$u + 5] . $Row;
                        $WorkSheet->setCellValue($Column, $TblVal_3[$t][$u]);
                    }
                    $Row++;
                }
                $WorkSheet->getStyle('F15:S15')->getAlignment()->setWrapText(true);
                $WorkSheet->getRowDimension('15')->setRowHeight(30);
                $WorkSheet->getStyle("F15:S15")->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ),
                        'fill' => array(
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => array('argb' => 'AED6F1')
                        )
                    )
                );
                $WorkSheet->getStyle("F18:S19")->applyFromArray(
                    array(
                        'font' => array(
                            'bold' => true
                        ),
                    )
                );
                //
                $WorkSheet->getStyle("F15:S19")->applyFromArray(
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
                $WorkSheet->getStyle('I19')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle('J16:J19')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle('K16:K19')->getNumberFormat()->setFormatCode('#,##0');
                $WorkSheet->getStyle('K19')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle("L16:L19")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("M16:M19")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("N16:N19")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("O16:O19")->getNumberFormat()->setFormatCode($currency_format);
                $WorkSheet->getStyle("P16:P19")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $WorkSheet->getStyle('Q19')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle('R19')->getNumberFormat()->setFormatCode('#,##0.00');
                $WorkSheet->getStyle("S16")->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                //
                $WorkSheet->getColumnDimension('F')->setWidth(16);
                $WorkSheet->getColumnDimension('G')->setWidth(12);
                $WorkSheet->getColumnDimension('H')->setWidth(10);
                $WorkSheet->getColumnDimension('I')->setWidth(14);
                $WorkSheet->getColumnDimension('J')->setWidth(14);
                $WorkSheet->getColumnDimension('K')->setWidth(10);
                $WorkSheet->getColumnDimension('L')->setWidth(14);
                $WorkSheet->getColumnDimension('M')->setWidth(14);
                $WorkSheet->getColumnDimension('N')->setWidth(10);
                $WorkSheet->getColumnDimension('O')->setWidth(14);
                $WorkSheet->getColumnDimension('P')->setWidth(10);
                $WorkSheet->getColumnDimension('Q')->setWidth(10);
                $WorkSheet->getColumnDimension('R')->setWidth(16);
                $WorkSheet->getColumnDimension('S')->setWidth(14);

                //
                $WorkSheet->getColumnDimension('L')->setWidth('15');
                $WorkSheet->getColumnDimension('M')->setWidth('15');
                $WorkSheet->getColumnDimension('N')->setWidth('15');
                $WorkSheet->getColumnDimension('O')->setWidth('15');
            } else if ($this->SheetsArr[$sheet] == "Not Working URLs") {
                $WorkSheet = $ObjSpreadsheet->createSheet($sheet);
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                $titleArr = ["#", "URLs", "Comments"];
                $Row = 1;
                for ($t = 0; $t < count($titleArr); $t++) {
                    $Column = $this->alphabet[$t] . $Row;
                    $WorkSheet->setCellValue($Column, $titleArr[$t]);
                    //$WorkSheet->getStyle($Column)->getFont()->setBold(true);
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
                $WorkSheet->getColumnDimension('D')->setAutoSize(true);
                //write urls
                //$commonObj = new CommonController;
                $DataIndex = array("#", "url", "http_status");
                $data = Url::select('url', 'http_status')->where('domain_id', $domainId)->where('http_status', "!=", 200)->orderBy("id", "ASC")->groupBy("url")->get()->toArray();
                //$ManualTestingNeeded = ['500', '502', '503', '504', '0', '410', '302', '203', '100', '403'];
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
        $DownloadName = parse_url($domain_details['url'], PHP_URL_HOST) . " - Audit Proposal - " . $dateNow . ".xlsx";
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
    private function EffortEstimation($domainId)
    {
        $output = [];
        $domain_details = Domain::where('id', $domainId)->first();
        $output['domain'] = (isset($domain_details['url'])) ? $domain_details['url'] : "Domain Not Found";
        $output['audit_page_count'] = 0;
        $output['audit_hours'] = 0;
        $output['total_templates'] = Url::where('id', $domainId)->distinct('template')->where('http_status', 200)->count();
        return $output;
    }
    public function show_email_template()
    {
        $data = [];
        $domainId = Session::get('domainId');
        $total_urls = DB::table('urls')->select('id')->distinct('url')->where('domain_id', $domainId)->count();
        $total_templates = DB::table('urls')->select('id')->distinct('template')->where('domain_id', $domainId)->count();
        $data['domain'] = Session::get('domain');
        $data['total_url'] = $total_urls;
        $data['total_template'] = $total_templates;
        return (new SendProcessCompletedEmail($data));
    }
}