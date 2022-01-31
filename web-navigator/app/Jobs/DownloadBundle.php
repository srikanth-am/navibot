<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Session;
use Mail;
use Log;
use App\Models\url;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DownloadBundle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $limit = 100000;
    protected $types = ['sitemap', 'html'];
    protected $SheetsArr = ["Summary", "Sitemap - URLs", "Html - URLs", "Final Report"];
    protected $alphabet = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
    protected $reportTitle = array("Template", "URL", "Web Audit");
    protected $DataIndex = array("template", "url", "web_audit");
    //
    private $ToEmails = ["shahulhameedh.muneerbasha@amnet-systems.com", "sridhar.natrajan@amnet-systems.com", "srikanth.manivannan@amnet-systems.com"];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        $this->domainId = Session::get("domainId");
        ini_set('memory_limit', '-1');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $this->writeLog("Download Bundle started for ".Session::get("url"));
        //
        $type = Session::get("type");
        $domainId = Session::get("domainId");
        $TotalUrls = url::select('id', 'url')->where('type', $type)->where('domain_id', $this->domainId)->count();
        $TotalSheets = (int) ceil($TotalUrls / $this->limit);
        //
        $StartLimit = 0;
        $EndLimit = $this->limit;
        $SavedFiles = array();
        //
        $ObjSpreadsheet = new Spreadsheet();
        $WorkSheet = $ObjSpreadsheet->getActiveSheet();
        //
        for ($sheet = 0; $sheet < count($this->SheetsArr); $sheet++) {
            if ($this->SheetsArr[$sheet] == "Summary") {
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                $WorkSheet->mergeCells("G6:L8");
                $ObjDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $ObjDrawing->setName('Amnet Logo');
                $ObjDrawing->setDescription('Description');
                $ObjDrawing->setPath(public_path("assets/img/logo.png"));
                $ObjDrawing->setHeight(65);
                $ObjDrawing->setCoordinates('I6');
                $ObjDrawing->setOffsetX(80);
                $ObjDrawing->setOffsetY(0);
                $ObjDrawing->setWorksheet($ObjSpreadsheet->getActiveSheet());
                //
                $WorkSheet->mergeCells("G9:L9");
                $WorkSheet->setCellValue("G9", Session::get('url'))->getStyle("G9")->getFont()->setBold(true)->setSize(16);
                $ObjSpreadsheet->getActiveSheet()->getStyle('G9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                //
                $WorkSheet->mergeCells("I10:J10");
                $WorkSheet->setCellValue("I10", "Summary")->getStyle("I10")->getFont()->setBold(true)->setSize(14);
                $ObjSpreadsheet->getActiveSheet()->getStyle('I10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $summary_data = $this->get_summary_details();
                $WorkSheet->setCellValue("I11", "Total Sitemap URLs");
                $WorkSheet->setCellValue("J11", $summary_data['sitemap_total_urls'])->getStyle("J9")->getFont()->setBold(true);
                $WorkSheet->setCellValue("I12", "Total Sitemap Templates");
                $WorkSheet->setCellValue("J12", $summary_data['sitemap_total_temp'])->getStyle("J10")->getFont()->setBold(true);
                $WorkSheet->setCellValue("I13", "Total HTML URLs");
                $WorkSheet->setCellValue("J13", $summary_data['html_total_urls'])->getStyle("J11")->getFont()->setBold(true);
                $WorkSheet->setCellValue("I14", "Total HTML Templates");
                $WorkSheet->setCellValue("J14", $summary_data['html_total_temp'])->getStyle("J12")->getFont()->setBold(true);
                $WorkSheet->setCellValue("I15", "Total Unique URLs(Sitemap)");
                $WorkSheet->setCellValue("J15", $summary_data['unique_sitemap_urls'])->getStyle("J15")->getFont()->setBold(true);
                $WorkSheet->setCellValue("I16", "Total Unique URLs(HTML)");
                $WorkSheet->setCellValue("J16", $summary_data['unique_html_urls'])->getStyle("J16")->getFont()->setBold(true);
                //
                foreach (range('I11', 'I16') as $col) {
                    $ObjSpreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
                }
                $WorkSheet->getStyle("I11:J16")->applyFromArray(
                    array(
                        'borders' => array(
                            'allBorders' => array(
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                            )
                        ),
                        'alignment' => array(
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        )
                    )
                );
            } else if ($this->SheetsArr[$sheet] == "Sitemap - URLs") {
                $WorkSheet = $ObjSpreadsheet->createSheet($sheet);
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                //
                $count_data = url::select('id', 'url', 'template')->where('domain_id', $domainId)->where('type', "sitemap")->count();
                //
                $WorkSheet->mergeCells("A1:C1");
                $WorkSheet->setCellValue("A1", "Preflight Report")->getStyle("A1")->getFont()->setBold(true)->setSize(20);
                $ObjSpreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                //
                $WorkSheet->setCellValue("A2", "Domain URL")->getStyle("A2")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B2", Session::get('url'))->getStyle("B2")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A3", "Date Time")->getStyle("A3")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B3", date("d-M-Y, g:i a"))->getStyle("B3")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A4", "Total No Of URL")->getStyle("A4")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B4", $count_data)->getStyle("B4")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A5", "Total No Of Templates")->getStyle("A5")->getFont()->setBold(true)->setSize(14);
                $templateCount = url::select('template')->distinct("template")->where('domain_id', $domainId)->where('type', 'sitemap')->count();
                $WorkSheet->setCellValue("B5", $templateCount)->getStyle("B5")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A6", "Technology Used")->getStyle("A6")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B6", "")->getStyle("B6")->getFont()->setSize(14);
                //
                $Row = 7;
                for ($t = 0; $t < count($this->reportTitle); $t++) {
                    $Column = $this->alphabet[$t] . $Row;
                    $WorkSheet->setCellValue($Column, $this->reportTitle[$t]);
                    //$WorkSheet->getColumnDimension($alphabet[$t])->setAutoSize(true);
                    $WorkSheet->getStyle($Column)->getFont()->setBold(true);
                    $WorkSheet->getStyle($Column)->applyFromArray(
                        array(
                            'fill' => array(
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => array('argb' => 'AED6F1')
                            )
                        )
                    );
                }
                $Row++;
                $SNo = 1;
                $WorkSheet->getColumnDimension('A')->setAutoSize(true);
                $WorkSheet->getColumnDimension('B')->setAutoSize(false);
                $WorkSheet->getColumnDimension('C')->setAutoSize(true);
                $WorkSheet->getColumnDimension('B')->setWidth("120");
                $t = '';
                for ($s = 1; $s <= $TotalSheets; $s++) {
                    $StartLimit = (($s - 1) * $this->limit);
                    $SlNo = ($StartLimit + 1);
                    $data = url::select('id', 'url', 'template')->where('type', "sitemap")->where('domain_id', $this->domainId)->offset($StartLimit)->limit($this->limit)->orderBy("template", "ASC")->get()->toArray();
                    if (count($data)) {
                        for ($r = 0; $r < count($data); $r++) {
                            for ($i = 0; $i < count($this->DataIndex); $i++) {
                                $Column = $this->alphabet[$i] . $Row;


                                if (!isset($data[$r][$this->DataIndex[$i]])) {
                                    $Value = "";
                                } else {
                                    if ($i == 0) {
                                        $ddd = "Template - " . $data[$r]["template"];
                                        if ($t == $ddd) {
                                            $Value = '';
                                        } else {
                                            $t = $ddd;
                                            $Value = $ddd;
                                        }
                                    } else {
                                        $Value = $data[$r][$this->DataIndex[$i]];
                                    }
                                }

                                $WorkSheet->setCellValue($Column, $Value);
                            }
                            $Row++;
                            $SNo++;
                        }
                    } else {
                        $Column = $this->alphabet[0] . $Row;
                        $WorkSheet->setCellValue($Column, "No records found");
                    }
                }
                $WorkSheet->getStyle("A1:C" . $ObjSpreadsheet->getActiveSheet()->getHighestRow())->applyFromArray(
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
            } else if ($this->SheetsArr[$sheet] == "Html - URLs") {
                $WorkSheet = $ObjSpreadsheet->createSheet($sheet);
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                //
                //$data = url::select('id', 'url', 'template')->where('domain_id', $domainId)->where('type', "html")->orderBy("template", "ASC")->get();
                //
                $count_data = url::select('id', 'url', 'template')->where('domain_id', $domainId)->where('type', "html")->count();

                $WorkSheet->mergeCells("A1:C1");
                $WorkSheet->setCellValue("A1", "Preflight Report")->getStyle("A1")->getFont()->setBold(true)->setSize(20);
                $ObjSpreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                //
                $WorkSheet->setCellValue("A2", "Domain URL")->getStyle("A2")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B2", Session::get('url'))->getStyle("B2")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A3", "Date Time")->getStyle("A3")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B3", date("d-M-Y, g:i a"))->getStyle("B3")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A4", "Total No Of URL")->getStyle("A4")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B4", $count_data)->getStyle("B4")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A5", "Total No Of Templates")->getStyle("A5")->getFont()->setBold(true)->setSize(14);
                $templateCount = url::select('template')->distinct("template")->where('domain_id', $domainId)->where('type', "html")->count();
                $WorkSheet->setCellValue("B5", $templateCount)->getStyle("B5")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A6", "Technology Used")->getStyle("A6")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B6", "")->getStyle("B6")->getFont()->setSize(14);
                //
                $Row = 7;
                for ($t = 0; $t < count($this->reportTitle); $t++) {
                    $Column = $this->alphabet[$t] . $Row;
                    $WorkSheet->setCellValue($Column, $this->reportTitle[$t]);
                    //$WorkSheet->getColumnDimension($alphabet[$t])->setAutoSize(true);
                    $WorkSheet->getStyle($Column)->getFont()->setBold(true);
                    $WorkSheet->getStyle($Column)->applyFromArray(
                        array(
                            'fill' => array(
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => array('argb' => 'AED6F1')
                            )
                        )
                    );
                }
                $Row++;
                $SNo = 1;
                $WorkSheet->getColumnDimension('A')->setAutoSize(true);
                $WorkSheet->getColumnDimension('B')->setAutoSize(false);
                $WorkSheet->getColumnDimension('C')->setAutoSize(true);
                $WorkSheet->getColumnDimension('B')->setWidth("120");
                $t = '';
                for ($s = 1; $s <= $TotalSheets; $s++) {
                    $StartLimit = (($s - 1) * $this->limit);
                    $SlNo = ($StartLimit + 1);
                    $data = url::select('id', 'url', 'template')->where('type', "html")->where('domain_id', $this->domainId)->offset($StartLimit)->limit($this->limit)->orderBy("template", "ASC")->get()->toArray();
                    if (count($data)) {
                        for ($r = 0; $r < count($data); $r++) {
                            for ($i = 0; $i < count($this->DataIndex); $i++) {
                                $Column = $this->alphabet[$i] . $Row;


                                if (!isset($data[$r][$this->DataIndex[$i]])) {
                                    $Value = "";
                                } else {
                                    if ($i == 0) {
                                        $ddd = "Template - " . $data[$r]["template"];
                                        if ($t == $ddd) {
                                            $Value = '';
                                        } else {
                                            $t = $ddd;
                                            $Value = $ddd;
                                        }
                                    } else {
                                        $Value = $data[$r][$this->DataIndex[$i]];
                                    }
                                }

                                $WorkSheet->setCellValue($Column, $Value);
                            }
                            $Row++;
                            $SNo++;
                        }
                    } else {
                        $Column = $this->alphabet[0] . $Row;
                        $WorkSheet->setCellValue($Column, "No records found");
                    }
                }
                $WorkSheet->getStyle("A1:C" . $ObjSpreadsheet->getActiveSheet()->getHighestRow())->applyFromArray(
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
            } else if ($this->SheetsArr[$sheet] == "Final Report") {
                $WorkSheet = $ObjSpreadsheet->createSheet($sheet);
                $WorkSheet->setTitle($this->SheetsArr[$sheet]);
                //
                $count_data = url::select('url')->distinct("url")->where('domain_id', $domainId)->count();
                $WorkSheet->mergeCells("A1:C1");
                $WorkSheet->setCellValue("A1", "Preflight Report")->getStyle("A1")->getFont()->setBold(true)->setSize(20);
                $ObjSpreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                //
                $WorkSheet->setCellValue("A2", "Domain URL")->getStyle("A2")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B2", Session::get('url'))->getStyle("B2")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A3", "Date Time")->getStyle("A3")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B3", date("d-M-Y, g:i a"))->getStyle("B3")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A4", "Total No Of URL")->getStyle("A4")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B4", $count_data)->getStyle("B4")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A5", "Total No Of Templates")->getStyle("A5")->getFont()->setBold(true)->setSize(14);
                $templateCount = url::select('template')->distinct("template")->where('domain_id', $domainId)->count();
                $WorkSheet->setCellValue("B5", $templateCount)->getStyle("B5")->getFont()->setSize(14);
                $WorkSheet->setCellValue("A6", "Technology Used")->getStyle("A6")->getFont()->setBold(true)->setSize(14);
                $WorkSheet->setCellValue("B6", "")->getStyle("B6")->getFont()->setSize(14);
                //
                $Row = 7;
                for ($t = 0; $t < count($this->reportTitle); $t++) {
                    $Column = $this->alphabet[$t] . $Row;
                    $WorkSheet->setCellValue($Column, $this->reportTitle[$t]);
                    //$WorkSheet->getColumnDimension($alphabet[$t])->setAutoSize(true);
                    $WorkSheet->getStyle($Column)->getFont()->setBold(true);
                    $WorkSheet->getStyle($Column)->applyFromArray(
                        array(
                            'fill' => array(
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => array('argb' => 'AED6F1')
                            )
                        )
                    );
                }
                $Row++;
                $SNo = 1;
                $WorkSheet->getColumnDimension('A')->setAutoSize(true);
                $WorkSheet->getColumnDimension('B')->setAutoSize(false);
                $WorkSheet->getColumnDimension('C')->setAutoSize(true);
                $WorkSheet->getColumnDimension('B')->setWidth("120");
                $t = '';
                for ($s = 1; $s <= $TotalSheets; $s++) {
                    $StartLimit = (($s - 1) * $this->limit);
                    $SlNo = ($StartLimit + 1);
                    $data = url::select('id', 'url', 'template')->where('domain_id', $this->domainId)->offset($StartLimit)->limit($this->limit)->orderBy("template", "ASC")->groupBy("url")->get()->toArray();

                    for ($r = 0; $r < count($data); $r++) {
                        for ($i = 0; $i < count($this->DataIndex); $i++) {
                            $Column = $this->alphabet[$i] . $Row;


                            if (!isset($data[$r][$this->DataIndex[$i]])) {
                                $Value = "";
                            } else {
                                if ($i == 0) {
                                    $ddd = "Template - " . $data[$r]["template"];
                                    if ($t == $ddd) {
                                        $Value = '';
                                    } else {
                                        $t = $ddd;
                                        $Value = $ddd;
                                    }
                                } else {
                                    $Value = $data[$r][$this->DataIndex[$i]];
                                }
                            }

                            $WorkSheet->setCellValue($Column, $Value);
                        }
                        $Row++;
                        $SNo++;
                    }
                }
                $WorkSheet->getStyle("A1:C" . $ObjSpreadsheet->getActiveSheet()->getHighestRow())->applyFromArray(
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
        $this->writeLog("Download Bundle loop completed for ". Session::get("url"));
        //
        $ObjSpreadsheet->setActiveSheetIndex(0);
        $DownloadName = parse_url(Session::get('url'), PHP_URL_HOST) . " - Consolidate Report - " . Carbon::now()->timestamp . ".xlsx";
        $Path = __DIR__ . "/../../public/exports/";
        $File = $Path . $DownloadName;
        //ob_clean();
        $Writer = new Xlsx($ObjSpreadsheet);
        $Writer->save($File);
        $this->writeLog("Download Bundle Saved file for ". Session::get("url"));
        sleep(1);
        $this->SendEmail($File);
    }
    private function SendEmail($file)
    {
        $this->writeLog("Email Process initiated for ". Session::get("url"));
        $data["title"] = parse_url(Session::get('url'), PHP_URL_HOST) . " - Final report";
        Mail::send([], $data, function ($message) use ($data, $file) {

            $message->to($this->ToEmails, $this->ToEmails)->subject($data["title"]);
            $message->setBody('<html><p>Please find the attachment</p></html>', 'text/html');
            $message->attach($file);
        });
        $this->writeLog("Email Sent successfully for ". Session::get("url"));
    }
    private function writeLog($msg)
    {
        Log::channel('download')->info($msg . "\n\n");
    }
    private function get_summary_details()
    {
        $output = [];
        $domain_id = Session::get('domainId');
        $output["sitemap_total_urls"] = DB::table('urls')->select('url')->where('domain_id', $domain_id)->where('type', 'sitemap')->count();
        $output["html_total_urls"] = DB::table('urls')->select('url')->where('domain_id', $domain_id)->where('type', 'html')->count();
        $output["sitemap_total_temp"] = DB::table('urls')->select('template')->distinct()->where('domain_id', $domain_id)->where('type', 'sitemap')->count('template');
        $output["html_total_urls"] = DB::table('urls')->select('url')->where('domain_id', $domain_id)->where('type', 'html')->count();
        $output["html_total_temp"] = DB::table('urls')->select('template')->distinct()->where('domain_id', $domain_id)->where('type', 'html')->count('template');
        $output["unique_urls"] = DB::table('urls')->select('url', 'type')->where('domain_id', $domain_id)->orderBy("url", "DESC")->groupBy("url")->havingRaw('COUNT(*) < 2')->get()->toArray();
        $output["unique_html_urls"] = count(array_keys(array_column($output["unique_urls"], 'type'), 'html'));
        $output["unique_sitemap_urls"] = count($output["unique_urls"]) - $output["unique_html_urls"];
        return $output;
    }
}