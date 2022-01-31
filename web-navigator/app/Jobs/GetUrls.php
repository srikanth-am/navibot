<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\CommonController as Common;
use Illuminate\Support\Facades\DB;
// use App\Jobs\GenerateTemplate;
use Carbon\Carbon;
use App\Models\Url;
use Mail;
use App\Mail\SendProcessCompletedEmail;
use Session;

class GetUrls implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $domain = '';
    protected $domainId = '';
    protected $query_str = false;
    protected $allSitemapUrls = [];
    protected $limit = 15000;
    protected $ToEmails = ["srikanth.manivannan@amnet-systems.com", "shahulhameedh.muneerbasha@amnet-systems.com", "sridhar.natrajan@amnet-systems.com"];
    protected $user_agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.81 Safari/537.36";
    public $curl_opt = [];
    public function __construct($domain, $domainId, $query_str)
    {

        set_time_limit(0);
        ini_set('memory_limit', '-1');
        libxml_use_internal_errors(true);
        $this->domainId = $domainId;
        $this->domain = $domain;
        $this->query_str = ($query_str == "yes") ? true : false;
        $this->curl_opt = array(
            CURLOPT_CUSTOMREQUEST  => "GET",        //set request type post or get
            CURLOPT_POST           => false,        //set to GET
            CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'], //set user agent
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 20,      // timeout on connect
            CURLOPT_TIMEOUT        => 20,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FORBID_REUSE   => 0,
            CURLOPT_VERBOSE        => 1,
            CURLOPT_FAILONERROR    => 0,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            // CURLOPT_HTTPHEADER     => array(
            //     'Content-Type: text/html',
            //     'Connection: keep-alive',
            //     "Keep-Alive: 300"
            // )
        );
        // die($this->domainId);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sitemapUrl = $this->domain . "/sitemap.xml";
        //
        $commonObj = new Common;
        //
        //$commonObj->writeSitemapLog("get urls");
        $sitemapRes = $commonObj->get_page_data($sitemapUrl);
        //
        if ($commonObj->isSitemapUrlExistInSitemapTbl($this->domainId) == 0) {
            $sitemap_insert_data = [
                "domain_id" => $this->domainId,
                "url" => $sitemapUrl,
                "is_crawled" => ($sitemapRes['status_code'] == 200) ? 1 : 0,
                "http_status" => $sitemapRes['status_code']
            ];
            DB::table('sitemaps')->insert($sitemap_insert_data);
            if ($sitemapRes['status_code'] == 200 && $sitemapRes['html'] != '') {
                $insertArray = [];
                $urls = [];
                $myXMLData = $sitemapRes['html'];
                $xml = @simplexml_load_string($myXMLData);
                $xmlArr = json_decode(json_encode($xml), TRUE);
                unset($xml);
                $isSitemap = (isset($xmlArr["sitemap"])) ? true : false;
                $index = ($isSitemap) ? "sitemap" : "url";
                foreach ($xmlArr[$index] as $key => $value) {
                    $urlStr = trim((string)$value['loc']);
                    $urlStr = rtrim($urlStr, '/');
                    $urlStr = rtrim($urlStr, '#');
                    $urls[] = $urlStr;
                    if ($isSitemap) {
                        $insertArray[] = ["url" => $urlStr, "domain_id" => $this->domainId];
                    } else {
                        $insertArray[] = ["url" => $urlStr, "type" => "sitemap", "domain_id" => $this->domainId, "is_crawled" => "1"];
                    }
                }
                unset($xmlArr);
                $tableName = "urls";
                if ($isSitemap) {
                    $tableName = "sitemaps";
                }
                if (count($insertArray)) {
                    DB::table($tableName)->insertOrIgnore($insertArray);
                }
                if ($isSitemap) {
                    DB::table('domains')->where('id', $this->domainId)->update(['total_sitemaps' => count($insertArray)]);
                    $this->allSitemapUrls = [];
                    $this->allSitemapUrls = $urls;
                    unset($urls);
                    $this->ExtractUrlsFromSitemap();
                }
            }
        } else {
            $this->allSitemapUrls = [];
            $this->allSitemapUrls = $this->getAllSiteMapUrlByDomainId();
            if (count($this->allSitemapUrls) > 0) {
                $this->ExtractUrlsFromSitemap();
            }
        }
        // $this->sendEmail();

        $this->get_html_urls(); //step -2
        DB::table('domains')->where('id', $this->domainId)->update(['temp_status' => '1']);
        $this->GenerateTemplates();
        $this->sendEmail();
        //
        //dispatch()
        //DB::table('domains')->where('id', $this->domainId)->update(['temp_status' => '1']);
        //GenerateTemplate::dispatch($this->domain, $this->domainId);
        //
    }
    public function ExtractUrlsFromSitemap()
    {
        $this->update_current_domain_progress("Sitemap is in progress");
        if (count($this->allSitemapUrls) > 0) {
            $chunkArr = array_chunk($this->allSitemapUrls, 50);
            foreach ($chunkArr as $k => $v) {
                if (count($v)) {
                    foreach ($v as $key => $url) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:19.0) Gecko/20100101 Firefox/19.0");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_URL, $url);    // get the url contents
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                        $myXMLData = curl_exec($ch); // execute curl request
                        $curl_info  = curl_getinfo($ch);
                        curl_close($ch);
                        //$regex = '/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';
                        preg_match_all('@<loc>(.+?)<\/loc>@', $myXMLData, $matches);
                        //preg_match_all($regex, $myXMLData, $matches);
                        $urls = (isset($matches[1]) && count($matches[1])) ? $matches[1] : [];
                        sort($urls);
                        //
                        $status = $curl_info['http_code'];
                        DB::table('sitemaps')->where('domain_id', $this->domainId)->where('url', $curl_info['url'])->update(["http_status" => $status]);
                        $this->validateUrls($urls, "sitemap");
                    }
                }
            }
        }
    }

    public function get_html_urls()
    {
        $this->update_current_domain_progress("HTML is in progress");
        //
        $urlsCount = DB::table("urls")->select("id")->where("domain_id", $this->domainId)->where("type", 'html')->where("is_crawled", "0")->count();
        if ($urlsCount > 0) {
            $this->ProcessNextUrls();
        } else {
            $this->GetParsedUrls(["url" => $this->domain]);
        }
        $this->update_current_domain_progress("HTML Completed");
        $this->update_domain_progress(true);
    }
    private function GetParsedUrls($data = [], $IgnoreHaderFooter = true)
    {
        if (count($data)) {;
            //
            $urlChunk = array_chunk($data, 15);
            $insertArr = [];
            $htmlUrls = [];
            libxml_use_internal_errors(true); //hide html error
            // get_page_data
            $commonObj = new Common;
            foreach ($urlChunk as $k => $v) {
                if (count($v)) {
                    foreach ($v as $key => $url) {
                        $arr = $commonObj->get_page_data($url);
                        $status = $arr['status_code'];
                        if ($status != 200) {
                            DB::table('urls')->where('domain_id', $this->domainId)->where("type", "html")->where('url', $url)->update(["http_status" => $status]);
                        }
                        if ($arr['html']) {
                            $dom = new \DOMDocument;
                            if ($dom->loadHTML($arr['html'], LIBXML_NOWARNING | LIBXML_NOERROR)) {
                                foreach ($dom->getElementsByTagName('a') as $link) {
                                    $htmlUrls[] = $link->getAttribute('href');
                                }
                            }
                        }
                        //
                    }
                    if (count($htmlUrls)) {
                        $htmlUrls = array_unique($htmlUrls);
                        $this->validateUrlsFromHtml($htmlUrls);
                    }
                    $htmlUrls = [];
                    DB::table('urls')->where('domain_id', $this->domainId)->where("type", 'html')->whereIn('url', $v)->update(["is_crawled" => '1']);
                }
                sleep(1);
            }
            //
            // $chs = [];
            // foreach ($urlChunk as $k => $v) {
            //     if (count($v)) {
            //         foreach ($v as $key => $url) {
            //             $chs[$key] = curl_init();
            //             curl_setopt_array($chs[$key], $this->curl_opt);
            //             // curl_setopt($chs[$key], CURLOPT_HTTPHEADER, $headers);
            //             curl_setopt($chs[$key], CURLOPT_URL, $url);
            //         }
            //         //create the multiple cURL handle
            //         $mh = curl_multi_init();
            //         //add the handles
            //         foreach ($chs as &$ch) {
            //             curl_multi_add_handle($mh, $ch);
            //         }

            //         $active = null;
            //         //execute the handles
            //         do {
            //             $mrc = curl_multi_exec($mh, $active);
            //         } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            //         //
            //         while ($active && $mrc == CURLM_OK) {
            //             if (curl_multi_select($mh) != -1) {
            //                 do {
            //                     $mrc = curl_multi_exec($mh, $active);
            //                 } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            //             }
            //         }
            //         foreach ($chs as $url => &$ch) {
            //             $curl_info  = curl_getinfo($ch);
            //             $status = $curl_info['http_code'];
            //             //if ($status != 200) {
            //             DB::table('urls')->where('domain_id', $this->domainId)->where("type", "html")->where('url', $curl_info['url'])->update(["http_status" => $status]);
            //             //}
            //             if ($status == 200) {
            //                 $html = curl_multi_getcontent($ch);
            //                 if (!$html) {
            //                     continue;
            //                 }
            //                 $dom = new \DOMDocument;
            //                 if ($dom->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR)) {
            //                     foreach ($dom->getElementsByTagName('a') as $link) {
            //                         $htmlUrls[] = $link->getAttribute('href');
            //                     }
            //                 }
            //             }
            //             curl_multi_remove_handle($mh, $ch); // remove the handle (assuming  you are done with it);
            //         }
            //         if (count($htmlUrls)) {
            //             $htmlUrls = array_unique($htmlUrls);
            //             $this->validateUrlsFromHtml($htmlUrls);
            //         }
            //         DB::table('urls')->where('domain_id', $this->domainId)->where("type", 'html')->whereIn('url', $v)->update(["is_crawled" => '1']);
            //     }
            //     sleep(1);
            // }
            $this->ProcessNextUrls();
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
                if (!$this->query_str) {

                    $queryStr = (isset($p["query"])) ? "?" . $p["query"] : '';
                }
                $scheme = (isset($p['scheme'])) ? $p['scheme'] : "https";
                $host = (isset($p['host'])) ? $p['host'] : $domain_url_arr['host'];
                $urlStr = $scheme . "://" . $host . $path . $queryStr;
                if ($commonObj->isValidUrl($urlStr) && strpos($urlStr, $this->domain) === 0) {
                    $urlStr = rtrim($urlStr, '/');
                    $urlStr = rtrim($urlStr, '#');
                    $insertArr[] = ["url" => $urlStr, "type" => "html", "domain_id" => $this->domainId];
                }
            }
            if (count($insertArr)) {
                uasort($insertArr, function ($a, $b) {
                    return strcasecmp($a['url'], $b['url']);
                });
                $this->DBinsertUrls($insertArr);
            }
        }
    }
    private function ProcessNextUrls()
    {
        $urls = DB::table("urls")->select("url")->where("domain_id", $this->domainId)->where("type", "html")->where("is_crawled", "0")->limit(1000)->get()->toArray();
        $temp = [];
        if (count($urls) > 0) {
            foreach ($urls as $data) {
                $temp[] = $data->url;
            }
            if (count($temp) > 0) {
                $this->GetParsedUrls($temp, true);
            }
        }
    }
    private function getAllSiteMapUrlByDomainId()
    {
        $urls = [];
        $res = DB::table("sitemaps")->select('url')->where('domain_id', $this->domainId)->where("is_crawled", "0")->get()->toArray();
        if (is_array($res) && count($res) > 0) {
            foreach ($res as $r) {
                $urls[] =  $r->url;
            }
        }
        return $urls;
    }
    public function validateUrls($urlsArr, $type)
    {
        $insertArr = [];
        //$commonObj = new Common;
        if (count($urlsArr)) {
            foreach ($urlsArr as $url) {
                $urlStr = trim($url);
                if ($urlStr && strpos($urlStr, $this->domain) === 0) {
                    $p = parse_url($urlStr);
                    $path = isset($p["path"]) ? $p["path"] : '';
                    $path = ($path != '' && $path[0] == '/') ? $path : "/" . $path;
                    $queryStr = (isset($p["query"]) && $this->query_str) ? "?" . $p["query"] : '';
                    $urlStr = $p['scheme'] . "://" . $p['host'] . $path . $queryStr;
                    //if ($commonObj->isValidUrl($urlStr)) {
                    $urlStr = rtrim($urlStr, '/');
                    $urlStr = rtrim($urlStr, '#');

                    if (preg_match("/\b(?:.pdf|.docx|.doc|.epub|.png|.mp4|.gif|.jpg|.jpeg|.ppt|.pptx|.xls|.xlsx)\b/i", $urlStr)) {
                        continue;
                    }
                    $insertArr[] = ["url" => $urlStr, "type" => $type, "domain_id" => $this->domainId, "is_crawled" => "1"];
                    // }
                }
            }
        }
        if (count($insertArr)) {
            $this->DBinsertUrls($insertArr);
        }
    }
    private function DBinsertUrls($insertArr = [])
    {
        if (count($insertArr) > 0) {
            $array = array_chunk($insertArr, 1000);
            for ($i = 0; $i < count($array); $i++) {
                DB::table("urls")->insertOrIgnore($array[$i]);
                sleep(1);
            }
        }
        $this->update_domain_progress(); //after insert update the progress details in domain table
    }
    private function update_current_domain_progress($status)
    {
        DB::table('domains')->where('id', $this->domainId)->update(['current_progress' => $status]);
    }
    private function update_domain_progress($completed = false)
    {
        $total_urls =  DB::table("urls")->select("id")->where("domain_id", $this->domainId)->count();
        // Url::select('id')->where('domain_id', $this->domainId)->distinct('url')->where('http_status', '200')->count();
        $total_urls_unique =  DB::table("urls")->select("id")->distinct('url')->where("domain_id", $this->domainId)->count();
        $total_urls_crawled =  DB::table("urls")->select("id")->where("domain_id", $this->domainId)->where("is_crawled", "1")->count();
        $progress = 0;
        $data = [];
        $data['total_urls'] = $total_urls_unique;
        if ($total_urls > 0 && $total_urls_crawled > 0) {
            $progress = round(($total_urls_crawled / $total_urls) * 100);
            $data["url_progress"] = $progress;
        }
        if ($completed && $total_urls == $total_urls_crawled) {
            $data["url_status"] = 2;
        }
        DB::table('domains')->where('id', $this->domainId)->update($data);
    }
    private function setUrlAsCrawledSitemap($urlArray = [])
    {
        DB::table('sitemaps')->where('domain_id', $this->domainId)->whereIn('url', $urlArray)->update(["is_crawled" => '1']);
    }
    private function GenerateTemplates()
    {
        $limit = $this->limit;
        $id = $this->domainId;
        $tempArr = [];
        $tempId = 1;
        $total_urls = DB::table('urls')->select('id', 'url')->where('domain_id', $id)->where('http_status', 200)->count();
        $completed = DB::table('urls')->select('id', 'url')->where('domain_id', $id)->where('http_status', 200)->whereNotNull("template")->count();
        $loop = (int) ceil($total_urls / $limit);
        if ($total_urls > 0 && $loop > 0) {
            if ($completed == $total_urls) {
                $this->update_template_progress_in_domains_table();
                $this->pushTemplateNotification();
            } else {
                for ($x = 0; $x < $loop; $x++) {
                    $urls = Url::select('id', 'url', 'template')->where('domain_id', $id)->where('http_status', 200)->offset($x * $limit)->limit($limit)->orderBy("url", "ASC")->get()->toArray();
                    if (count($urls) > 0) {
                        for ($u = 0; $u < count($urls); $u++) {
                            $segments = [];
                            $segments = explode('/', trim(parse_url($urls[$u]['url'], PHP_URL_PATH), '/'));
                            $path1 = isset($segments[0]) ? $segments[0] : '';
                            $path2 = isset($segments[1]) ? $segments[1] : '';
                            $path3 = isset($segments[2]) ? $segments[2] : '';
                            //
                            if ($path1 == '') {
                                $this->updateTemplate($urls[$u]['id'], 1);
                            } else if ($path1 != '' && $path2 == '') {
                                //$path = "single_path";


                                if (preg_match("/\b(?:form|login|signin|signup|privacy|privacy-policy|contact|contact-us|contactus)\b/i", $urls[$u]['url'])) {

                                    $path = $path1;
                                    if (!array_key_exists($path, $tempArr)) {
                                        $tempId++;
                                        $tempArr[$path1] = $tempId;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $tempArr[$path]);
                                } else {
                                    //$tempId++;
                                    //$tempArr["single_path"] = $tempId;
                                    if (!isset($tempArr["single_path"])) {
                                        $tempId++;
                                        $tempArr["single_path"] = $tempId;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $tempArr["single_path"]);
                                }
                            } else if ($path1 != '' && $path2 != '') {
                                if (preg_match("/\b(?:form|login|signin|signup|privacy|privacy-policy|contact|contact-us|contactus)\b/i", $urls[$u]['url'])) {
                                    $path1 = $path1 . "/" . $path2;
                                }
                                if (!array_key_exists($path1, $tempArr)) {
                                    $tempId++;
                                    $tempArr[$path1] = $tempId;
                                }
                                $this->updateTemplate($urls[$u]['id'], $tempArr[$path1]);
                            }
                            if ($u % 100 == 0) {
                                $this->update_template_progress_in_domains_table();
                            }
                        }
                    }
                    $this->update_template_progress_in_domains_table();
                }
                $this->pushTemplateNotification();
            }
        }
    }
    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return substr($haystack, 0, $length) === $needle;
    }
    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if (!$length) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }
    //
    private function pushTemplateNotification()
    {
        // $total_urls = Url::select('id')->where('domain_id', $this->domainId)->distinct('url')->where('http_status', '200')->count();
        // $not_working_urls = Url::select('id')->where('domain_id', $this->domainId)->distinct('url')->where('http_status', "!=", '200')->count();

        // $total_templates = DB::table('urls')->select('id')->distinct('template')->where('domain_id', $this->domainId)->count();
        // Session::put("total_template", $total_templates);
        // Session::put("total_url", $total_urls);
        // Session::put("not_working_urls", $not_working_urls);
        $this->update_template_status_domains_table(); //update the status in domains table
        $commonObj = new Common;
        $commonObj->writeHtmlLog("Template Completed " . $this->domain);
        $commonObj->insert_domain_e_time($this->domainId);
    }
    private function update_template_progress_in_domains_table()
    {
        $template = DB::table("urls")->select("id")->where("domain_id", $this->domainId)->whereNotNull("template")->count();
        $total_urls = DB::table("urls")->select("id")->where("domain_id", $this->domainId)->count();

        $progress = 0;
        if ($template > 0 && $total_urls) {
            $progress = round(($template / $total_urls) * 100);
        }
        DB::table('domains')->where('id', $this->domainId)->update(['temp_progress' => $progress]);
    }
    private function update_template_status_domains_table()
    {
        DB::table('domains')->where('id', $this->domainId)->update(['temp_status' => '2']);
    }
    private function updateTemplate($id, $temp_id)
    {
        $updateObj = Url::find($id);
        $updateObj->template = $temp_id;
        $updateObj->update();
    }
    private function sendEmail()
    {
        //$data = [];
        $date = Carbon::now()->setTimezone('Asia/Kolkata')->format('d-M-Y h:i A');
        $data = [];
        $domainId = $this->domainId;
        $total_urls = Url::select('id')->where('domain_id', $domainId)->distinct('url')->where('http_status', '200')->count();
        $total_templates = Url::select('id')->where('domain_id', $domainId)->distinct('template')->where('http_status', '200')->count();
        $not_working_urls = Url::select('id')->where('domain_id', $this->domainId)->distinct('url')->where('http_status', "!=", '200')->count();
        $data['domain'] = $this->domain;
        $data['total_url'] = $total_urls;
        $data['total_template'] = $total_templates;
        $data['not_working_urls'] = $not_working_urls;
        Mail::to($this->ToEmails)->send(new SendProcessCompletedEmail($data));
    }
}