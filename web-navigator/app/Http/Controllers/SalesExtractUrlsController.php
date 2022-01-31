<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CommonController as Common;
use Illuminate\Support\Facades\Auth;
use App\Mail\SendProcessCompletedEmail;
use Mail;
class SalesExtractUrlsController extends Controller
{
    protected $RemoveQryStr = true;
    protected $SALES_URL_LIMIT = "";
    protected $domain = '';
    protected $domainId = '';
    public function __construct()
    {
        set_time_limit(5);
        ini_set('max_execution_time', 10);


        $this->middleware(['auth', 'verified', 'Sales']);
        $this->SALES_URL_LIMIT = env('SALES_URL_LIMIT');
    }
    //
    public function index(){
        return view('sales_add_domain');
    }
    //
    public function get_urls(Request $request){
        $output = ["status" => "error", "message" => "Failed"];
        //
        $domain = trim($request->domain);
        if(!$domain){
            $output['message'] = "Website URL is required";
            return $output;
        }
        $urlArr = parse_url($domain);
        $domain = $urlArr["scheme"] . "://" . $urlArr["host"];
        //
        $commonObj = new Common;
        //
        if (!$commonObj->isValidUrl($domain)) {
            $output['message'] = "Invalid URL";
            return $output;
        }
        //
        $this->domain = $domain;
        $data = $commonObj->get_page_data($domain);
        //
        if ($data['status_code'] != 200) {
            $output['message'] = $data['status_code'] . " " . $data['error_message'];
            return $output;
        }
        $d = DB::table('sales_domains')->select('id')->where('url', $domain)->where('created_by', \Auth::user()->id)->get()->first();
        if (!$d) {
            $ins = [
                'url' => $domain,
                'http_status' => $data['status_code'],
                'url_status' => 1,
                's_time' => Carbon::now(),
                'created_by' => \Auth::user()->id
            ];
            DB::table('sales_domains')->insert($ins);
            $this->domainId = DB::getPdo()->lastInsertId();
        } else {
            $this->domainId = $d->id;
        }
        $html_url_count = DB::table('sales_urls')->select('id')->where('domain_id', $this->domainId)->where('is_crawled', 0)->count();
        if ($data['html'] && $html_url_count < $this->SALES_URL_LIMIT) {
            $dom = new \DOMDocument;
            $htmlUrls = [];
            if ($dom->loadHTML($data['html'], LIBXML_NOWARNING | LIBXML_NOERROR)) {
                foreach ($dom->getElementsByTagName('a') as $link) {
                    $htmlUrls[] = $link->getAttribute('href');
                }
            }
            if(count($htmlUrls)){
                $this->validateUrlsFromHtml($htmlUrls);
                $this->proccess_next_html_urls();
            }
        }
        //
        //$this->GenerateTemplates();
        //
        $this->update_domain_progress();
        //
        $this->update_domain_end_time();
        //
        //$this->sendEmail();
        //
        $output['status'] = "success";
        $output['message'] = "Process Completed Successfully";
        return $output;
    }
    //
    private function update_domain_end_time(){
        $obj = DB::table('sales_domains')->select('s_time', 'e_time')->where('id', $this->domainId)->get()->first();
        $s_time = "";
        if (isset($obj->s_time)) {
            $s_time = $obj->s_time;
        }
        $end_time = (isset($obj->e_time) && $obj->e_time) ? $obj->e_time : Carbon::now();
        $s_time = Carbon::parse($s_time);
        $differnce = $s_time->diffInHours($end_time) . ':' . $s_time->diff($end_time)->format('%I:%S');
        DB::table('sales_domains')->where('id', $this->domainId)->update(['e_time' => $end_time, 't_utilized' => $differnce]);
    }
    private function update_domain_progress()
    {
        $total_urls =  DB::table("sales_urls")->select("id")->where("domain_id", $this->domainId)->count();
        $progress = 100;
        $data = [];
        $data['total_urls'] = $total_urls;
        $data["url_progress"] = 100;
        $data["url_status"] = 2;
        $data["temp_status"] = 2;
        $data["temp_progress"] = 100;
        DB::table('sales_domains')->where('id', $this->domainId)->update($data);
    }
    //
    private function proccess_next_html_urls(){
        $htmlUrls = [];
        $commonObj = new Common;
        $urlArr = DB::table('sales_urls')->select('url')->where('domain_id', $this->domainId)->limit(10)->get()->toArray();
        foreach($urlArr as $Obj){
            $htmlArray = $commonObj->get_page_data($Obj->url);
            $status = $htmlArray['status_code'];
            if ($status != 200) {
                DB::table('urls')->where('domain_id', $this->domainId)->where("type", "html")->where('url', $url)->update(["http_status" => $status]);
            }
            if ($htmlArray['html']) {
                $dom = new \DOMDocument;
                if ($dom->loadHTML($htmlArray['html'], LIBXML_NOWARNING | LIBXML_NOERROR)) {
                    foreach ($dom->getElementsByTagName('a') as $link) {
                        $htmlUrls[] = $link->getAttribute('href');
                    }
                    $htmlUrls = array_unique($htmlUrls);
                    $html_url_count = DB::table('sales_urls')->select('id')->where('domain_id', $this->domainId)->count();
                    if($html_url_count < $this->SALES_URL_LIMIT){
                        $this->validateUrlsFromHtml($htmlUrls);
                    }
                }
            }
        }
    }
    private function validateUrlsFromHtml($urlsArr = [])
    {
        if (count($urlsArr)) {
            $domain_url_arr = parse_url($this->domain);
            $insertArr = [];
            $commonObj = new Common;
            foreach ($urlsArr as $url) {
                $urlStr = trim($url);
                $urlLength = strlen($urlStr);
                if ($urlLength > 2) {
                    if ($urlStr[0] == "/" && $urlStr[1] == "/") {
                        $urlStr = "https:" . $urlStr;
                    } else if ($urlStr[0] == '/' || $urlStr[0] == '?') {
                        $urlStr = $this->domain . $urlStr;
                    }
                }
                if (preg_match("/\b(?:.pdf|.docx|.doc|.epub|.png|.mp4|.gif|.jpg|.jpeg|.ppt|.pptx|.xls|.xlsx)\b/i", $urlStr)) {
                    continue;
                }
                // 
                $p = parse_url($urlStr);
                $path = isset($p["path"]) ? $p["path"] : '';
                $path = ($path != '' && $path[0] == '/') ? $path : "/" . $path;

                $queryStr = '';
                if (!$this->RemoveQryStr) {

                    $queryStr = (isset($p["query"])) ? "?" . $p["query"] : '';
                }
                $scheme = (isset($p['scheme'])) ? $p['scheme'] : "https";
                $host = (isset($p['host'])) ? $p['host'] : $domain_url_arr['host'];
                $urlStr = $scheme . "://" . $host . $path . $queryStr;
                if ($commonObj->isValidUrl($urlStr) && strpos($urlStr, $this->domain) === 0) {
                    $urlStr = rtrim($urlStr, '/');
                    $urlStr = rtrim($urlStr, '#');
                    $insertArr[] = ["url" => $urlStr, "type" => "html", "domain_id" => $this->domainId, "is_crawled" => 1];
                }
            }
            if (count($insertArr)) {
                uasort($insertArr, function ($a, $b) {
                    return strcasecmp($a['url'], $b['url']);
                });
                foreach($insertArr as $data){
                    $exist = DB::table('sales_urls')->where('url', $data['url'])->where('domain_id', $this->domainId)->exists();
                    if(!$exist){
                        DB::table('sales_urls')->insert($data);
                    }
                }
            }
           
        }
    }
    //
    private function GenerateTemplates()
    {
        $total_urls = DB::table('sales_urls')->select('id')->where('domain_id', $this->domainId)->where('http_status', 200)->count();
        $completed = DB::table('sales_urls')->select('id')->where('domain_id', $this->domainId)->where('http_status', 200)->whereNotNull("template")->count();
        if ($total_urls > 0) {
            if ($completed == $total_urls) {
                $this->update_template_progress_in_domains_table();
            } else {
                $urls = DB::table('sales_urls')->select('id', 'url', 'template')->where('domain_id', $this->domainId)->where('http_status', 200)->orderBy("url", "ASC")->get()->toArray();
                $tempArr = [];
                $tempId = 1;
                foreach($urls as $Obj){
                // for ($u = 0; $u < count($urls); $u++) {
                    $segments = [];
                    $segments = explode('/', trim(parse_url($Obj->url, PHP_URL_PATH), '/'));
                    $path1 = isset($segments[0]) ? $segments[0] : '';
                    $path2 = isset($segments[1]) ? $segments[1] : '';
                    $path3 = isset($segments[2]) ? $segments[2] : '';
                    if ($path1 == '') {
                        $this->updateTemplate($Obj->id, 1);
                    } else if ($path1 != '' && $path2 == '') {
                        if (preg_match("/\b(?:form|login|signin|signup|privacy|privacy-policy|contact|contact-us|contactus)\b/i", $Obj->url)) {
                            $path = $path1;
                            if (!array_key_exists($path, $tempArr)) {
                                $tempId++;
                                $tempArr[$path1] = $tempId;
                            }
                            $this->updateTemplate($Obj->id, $tempArr[$path]);
                        } else {
                            if (!isset($tempArr["single_path"])) {
                                $tempId++;
                                $tempArr["single_path"] = $tempId;
                            }
                            $this->updateTemplate($Obj->id, $tempArr["single_path"]);
                        }
                    } else if ($path1 != '' && $path2 != '') {
                        if (preg_match("/\b(?:form|login|signin|signup|privacy|privacy-policy|contact|contact-us|contactus)\b/i", $Obj->url)) {
                            $path1 = $path1 . "/" . $path2;
                        }
                        if (!array_key_exists($path1, $tempArr)) {
                            $tempId++;
                            $tempArr[$path1] = $tempId;
                        }
                        $this->updateTemplate($Obj->id, $tempArr[$path1]);
                    }
                }
            }
        }
    }
    //
    private function update_template_progress_in_domains_table()
    {
        $template = DB::table("sales_urls")->select("id")->where("domain_id", $this->domainId)->whereNotNull("template")->count();
        $total_urls = DB::table("sales_urls")->select("id")->where("domain_id", $this->domainId)->count();

        $progress = 0;
        if ($template > 0 && $total_urls) {
            $progress = round(($template / $total_urls) * 100);
        }
        DB::table('sales_domains')->where('id', $this->domainId)->update(['temp_progress' => $progress]);
    }
    //
    private function updateTemplate($id, $temp_id)
    {
        DB::table('sales_urls')->where('id', $id)->update(['template' => $temp_id]);
    }
    //
    private function sendEmail()
    {
        //$data = [];
        $date = Carbon::now()->setTimezone('Asia/Kolkata')->format('d-M-Y h:i A');
        $data = [];
        $domainId = $this->domainId;
        $total_urls = DB::table('sales_urls')->select('id')->where('domain_id', $domainId)->distinct('url')->where('http_status', '200')->count();
        $total_templates = DB::table('sales_urls')->select('id')->where('domain_id', $domainId)->distinct('template')->where('http_status', '200')->count();
        $not_working_urls = DB::table('sales_urls')->select('id')->where('domain_id', $this->domainId)->distinct('url')->where('http_status', "!=", '200')->count();
        $data['domain'] = $this->domain;
        $data['total_url'] = $total_urls;
        $data['total_template'] = $total_templates;
        $data['not_working_urls'] = $not_working_urls;
        Mail::to(Auth::user()->email)->send(new SendProcessCompletedEmail($data));
    }
    //
}
