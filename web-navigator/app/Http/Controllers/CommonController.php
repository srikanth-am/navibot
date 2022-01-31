<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Session;
use Mail;

class CommonController extends Controller
{
    //
    private $ToEmails = ["shahulhameedh.muneerbasha@amnet-systems.com", "sridhar.natrajan@amnet-systems.com", "srikanth.manivannan@amnet-systems.com"];
    public function isValidUrl($url = "")
    {
        $pattern = "%^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu";
        $res = preg_match($pattern, $url);
        return ($res == 1) ? true : false;
    }
    public function total_pages_for_audit($t_u)
    {
        $total_page_audit = 0;
        if ($t_u <= 150) {
            $total_page_audit = 1;
        } else if ($t_u > 150 && $t_u <= 500) {
            $total_page_audit = 2;
        } else if ($t_u > 500 && $t_u <= 1500) {
            $total_page_audit = 3;
        } else if ($t_u > 1500 && $t_u <= 3000) {
            $total_page_audit = 4;
        } else if ($t_u > 3000 && $t_u <= 5000) {
            $total_page_audit = 5;
        } else if ($t_u > 5000 && $t_u <= 7000) {
            $total_page_audit = 6;
        } else if ($t_u > 10000 && $t_u <= 20000) {
            $total_page_audit = 8;
        } else if ($t_u > 20000 && $t_u <= 50000) {
            $total_page_audit = 10;
        } else if ($t_u > 50000 && $t_u <= 90000) {
            $total_page_audit = 14;
        } else if ($t_u > 100000) {
            $total_page_audit = 15;
        }
        return $total_page_audit;
    }
    public function get_http_status_message($status_code)
    {
        $http_codes = [
            0 => "Need to check manually",
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            103 => 'Checkpoint',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        ];
        return $http_codes[$status_code];
    }
    public function get_page_data($url)
    {
        $output = [];
        //$user_agent = $_SERVER['HTTP_USER_AGENT']; //'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
        $user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36";
        $options = array(

            CURLOPT_CUSTOMREQUEST  => "GET",        //set request type post or get
            CURLOPT_POST           => false,        //set to GET
            CURLOPT_USERAGENT      => $user_agent, //set user agent
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 300,      // timeout on connect
            CURLOPT_TIMEOUT        => 300,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            // CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            // CURLOPT_SSL_VERIFYPEER => 0,
            // CURLOPT_ENCODING       => '',
            CURLOPT_URL            => $url,
        );
        $ch      = curl_init();
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err     = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);
        $output['status_code'] = $header['http_code'];
        $output['error_no'] = $err;
        $output['error_message'] = ($output['status_code'] != 200 && !$errmsg) ? $this->get_http_status_message($output['status_code']) : $errmsg;
        $output['html'] = ($output['status_code'] == 200) ? $content : "";
        // ob_clean();
        return $output;
    }
    public function insert_domain_s_time($id)
    {
        DB::table('domains')->where('id', $id)->update(['s_time' => Carbon::now()]);
    }
    public function insert_domain_e_time($id)
    {
        $obj = DB::table('domains')->select('s_time', 'e_time')->where('id', $id)->get()->first();
        $s_time = "";

        if (isset($obj->s_time)) {
            $s_time = $obj->s_time;
        }
        $end_time = (isset($obj->e_time) && $obj->e_time) ? $obj->e_time : Carbon::now();
        $s_time = Carbon::parse($s_time);
        $differnce = $s_time->diffInHours($end_time) . ':' . $s_time->diff($end_time)->format('%I:%S');
        DB::table('domains')->where('id', $id)->update(['e_time' => Carbon::now(), 't_utilized' => $differnce]);
    }
    public function isSitemapUrlExistInSitemapTbl($id)
    {
        return DB::table("sitemaps")->select('url')->where('domain_id', $id)->count();
    }
    public function writeDownloadLog($msg)
    {
        \Log::channel('job_download')->info($msg . "\n");
    }
    public function writeSitemapLog($msg)
    {
        \Log::channel('job_sitemap')->info($msg . "\n");
    }
    public function writeHtmlLog($msg)
    {
        \Log::channel('job_html')->info($msg . "\n");
    }
    // public function SendEmail($file)
    // {
    //     $this->writeLog("Email Process initiated for " . Session::get("url"));
    //     $data["title"] = parse_url(Session::get('url'), PHP_URL_HOST) . " - Final report";
    //     Mail::send([], $data, function ($message) use ($data, $file) {

    //         $message->to($this->ToEmails, $this->ToEmails)->subject($data["title"]);
    //         $message->setBody('<html><p>Please find the attachment</p></html>', 'text/html');
    //         $message->attach($file);
    //     });
    //     $this->writeLog("Email Sent successfully for " . Session::get("url"));
    // }
}