<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Session;
use Mail;
use App\Models\Url;
use App\Models\Domain;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Http\Controllers\CommonController;

class DownloadMaster implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $domainId = '';
    protected $domain = '';
    protected $hour = '';
    protected $minute = '';
    protected $limit = 100000;
    protected $n_of_resource = 1;
    protected $currency = 1;
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
    private $ToEmails = ["shahulhameedh.muneerbasha@amnet-systems.com", "sridhar.natrajan@amnet-systems.com", "srikanth.manivannan@amnet-systems.com"];
    //
    public function __construct($id, $h, $m, $resource, $currency)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $this->domainId = $id;
        $this->hour = $h;
        $this->minute = $m;
        $this->domain = "";
        $this->n_of_resource = $resource;
        $this->currency = $currency;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $domainId = $this->domainId;
        //
        $h = $this->hour;
        $m = $this->minute;
        if ($this->currency == "EURO") {
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
        } else if ($this->currency == "POUND") {
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
        } else if ($this->currency == "CAD") {
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
        $currency_format = $currencyArr[$this->currency];
        $numberFormats = [
            'percentage' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00,
        ];
        $total_urls = Url::select('id')->where('domain_id', $domainId)->distinct('url')->where('http_status', '200')->count();
        $total_templates = Url::select('id')->where('domain_id', $domainId)->distinct('template')->where('http_status', '200')->count();
        $TotalSheets = (int) ceil($total_urls / $this->limit);
        //
        $dateNow = Carbon::now()->format('d-M-Y');
        $domain_details = Domain::where('id', $domainId)->first();
        //
        $StartLimit = 0;
        $EndLimit = $this->limit;
        //$SavedFiles = array();
        //
        $this->domain = parse_url($domain_details['url'], PHP_URL_HOST);
        $ObjSpreadsheet = new Spreadsheet();
        $ObjSpreadsheet->getProperties()->setCreator("Navibot::Amnet Systems")
            ->setLastModifiedBy("Navibot")
            ->setTitle(parse_url($domain_details['url'], PHP_URL_HOST) . " - Audit Proposal")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("This is system generated report");
        $WorkSheet = $ObjSpreadsheet->getActiveSheet();
        $timeZone = \PhpOffice\PhpSpreadsheet\Shared\Date::setDefaultTimezone("en_us");
        $hours = $h . ":" . $m . ':00';
        $secs = strtotime($hours) - strtotime("00:00:00");
        // $time = gmdate("H:i:s", $secs * $total_page_audit);
        $time = gmdate("H:i:s", $secs);

        $timestamp = new \DateTime($time);
        $excelTimestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($timestamp);
        $excelDate = floor($excelTimestamp);
        $v = $excelTimestamp - $excelDate;
        $WorkSheet->setCellValue("AZ1", $v);
        $WorkSheet->getStyle("AZ1")->getNumberFormat()->setFormatCode("[h]:mm:ss");
        $commonObj = new CommonController;
        $commonObj = new CommonController;
        for ($sheet = 0; $sheet < count($this->SheetsArr); $sheet++) {
            if ($this->SheetsArr[$sheet] == "Effort Estimation") {
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                $WorkSheet->mergeCells("A1:D1");
                $WorkSheet->setCellValue("A1", "Effort Estimations")->getStyle("A1")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                //
                $WorkSheet->setCellValue("A2", "Domain URL")->getStyle("A2")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B2", $domain_details['url'])->getStyle("B2")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A3", "Date Time")->getStyle("A3")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B3", Carbon::now()->format('d-M-Y h:i A'))->getStyle("B3")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A4", "Total No of URLs")->getStyle("A4")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B4", $total_urls)->getStyle("B4")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A5", "Total No of Templates")->getStyle("A5")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B5", $total_templates)->getStyle("B5")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A6", "Web Audit Testing Total Hours")->getStyle("A6")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B6", "")->getStyle("B6")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A7", "Web Audit Testing Total Pages")->getStyle("A7")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B7", "")->getStyle("B7")->getFont()->setSize(14);
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
                $template = 0;
                $tmp_arr = Url::select("template")->where('domain_id', $domainId)->where('http_status', '200')->orderBy("template", "ASC")->groupBy("template")->get()->toArray();
                for ($i = 0; $i < count($tmp_arr); $i++) {
                    $temp_no = $tmp_arr[$i]['template'];
                    $template++;
                    $name = "Template - " . $template;
                    $t_u = Url::select('id')->where('domain_id', $domainId)->distinct('url')->where('template', $temp_no)->where('http_status', '200')->count();
                    $total_page_audit = $commonObj->total_pages_for_audit($t_u);
                    $loop = 3;
                    for ($d = 0; $d <= $loop; $d++) {
                        $column = $this->alphabet[$d] . $Row;
                        $value = '';
                        if ($d == 0) {
                            $value = $name;
                        } else if ($d == 1) {
                            $value = $t_u;
                        } else if ($d == 2) {
                            $value = $total_page_audit;
                        } else if ($d == 3) {

                            $value = "=" . $this->alphabet[$d - 1] . $Row . '*$AZ$1'; //$v;
                        }
                        $WorkSheet->setCellValue($column, $value);
                    }
                    $Row++;
                }
                $timeFormatCode = "[h]:mm:ss";
                //appling formulas
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
                    ['Average', '', '', '=I6/H6', '', '=K6/I6', '', '', '', '', '', '=K6/' . $this->n_of_resource * 8, '', ''],
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
                    ['Average', '', '', '=I12/H12', '', '=K12/I12', '', '', '', '', '', '=K12/' . $this->n_of_resource * 8, '', ''],
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
                $WorkSheet->getStyle('K13')->getNumberFormat()->setFormatCode('#,##0.00');
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
                    ['Average', '', '', '=I18/H18', '', '=K18/I18', '', '', '', '', '', '=K18/' . $this->n_of_resource * 8, '', ''],
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
                $WorkSheet->getStyle('R18')->getNumberFormat()->setFormatCode('#,##0.00');
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
            } else if ($this->SheetsArr[$sheet] == "Preflight Information") {
                $WorkSheet = $ObjSpreadsheet->createSheet($sheet);
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                //Heading
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
                $WorkSheet->setCellValue("B4", $total_urls)->getStyle("B4")->getFont()->setSize(12);
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
                $WorkSheet->getColumnDimension('D')->setWidth(15);
                $WorkSheet->getColumnDimension('E')->setWidth(25);
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
                for ($s = 1; $s <= $TotalSheets; $s++) {
                    $StartLimit = (($s - 1) * $this->limit);
                    $SlNo = ($StartLimit + 1);
                    $data = url::select('id', 'url', 'template', "http_status")->where('domain_id', $this->domainId)->where('http_status', 200)->offset($StartLimit)->limit($this->limit)->orderBy("template", "ASC")->groupBy("url")->get()->toArray();
                    $tempA = [];
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
                                // $t_u = Url::select('id')->where('domain_id', $domainId)->distinct('url')->where('template', $data[$r]["template"])->where('http_status', '200')->count();
                                $total_page_audit = $commonObj->total_pages_for_audit($t_u);;

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
                }
                $web_audit = [];
                $web_audit_time = [];
                //final row
                $lastBeforeCell = $Row - 1;
                $WorkSheet->setCellValue("B7", '=SUM(C9:C' . $WorkSheet->getHighestRow() . ')');
                $WorkSheet->setCellValue("B6", "=SUM(D9:D" . $WorkSheet->getHighestRow() . ")");
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
                $WorkSheet->getStyle("A8:E" . $WorkSheet->getHighestRow())->applyFromArray(
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
                    $WorkSheet->getStyle($Column)->getFont()->setBold(true);
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
                                        $ObjSpreadsheet->getActiveSheet()
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
        $DownloadName = $this->domain . " - Audit Proposal.xlsx";
        $Path = __DIR__ . "/../../public/exports/";
        $File = $Path . $DownloadName;
        //ob_clean();
        $Writer = new Xlsx($ObjSpreadsheet);
        $Writer->save($File);
        sleep(1);
        $this->SendEmail($File);
    }
    private function SendEmail($file)
    {
        $data["title"] = $this->domain . " - Audit Proposal";
        // parse_url($domain_details['url'], PHP_URL_HOST)
        Mail::send([], $data, function ($message) use ($data, $file) {

            $message->to($this->ToEmails, $this->ToEmails)->subject($data["title"]);
            $message->setBody('<html><p>Please find the attachment</p></html>', 'text/html');
            $message->attach($file);
        });
    }
    private function writeLog($msg)
    {
        \Log::channel('download')->info($msg . "\n\n");
    }
}