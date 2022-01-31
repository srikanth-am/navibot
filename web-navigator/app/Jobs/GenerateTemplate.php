<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CommonController as Common;

use App\Models\url;
use Session;
// use Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendSuccessNotification;

//use Pusher\Pusher;

class GenerateTemplate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $pusher = "";
    protected $temp_id = 0;
    private $domain = "";
    private $domainId = "";
    private $crawlType = "";
    private $scheme = "";
    private $doamin = "";
    private $tempArr = ['single' => 2];
    private $ToEmails = ["shahulhameedh.muneerbasha@amnet-systems.com", "sridhar.natrajan@amnet-systems.com", "srikanth.manivannan@amnet-systems.com"];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($domain, $id)
    {
        set_time_limit(0);
        //ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        if ($domain && $id) {
            $this->domain = $domain;
            $this->domainId = $id;
            //$this->crawlType = $type;
        } else {
            //return ["status" => "error", "message" => "Required Fields are missing"];
        }
        ignore_user_abort(1);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->GenerteTemplates();
        //
    }
    function sortAssociativeArrayByKey($array, $key, $direction)
    {
        switch ($direction) {
            case "ASC":
                usort($array, function ($first, $second) use ($key) {
                    return $first[$key] <=> $second[$key];
                });
                break;
            case "DESC":
                usort($array, function ($first, $second) use ($key) {
                    return $second[$key] <=> $first[$key];
                });
                break;
            default:
                break;
        }

        return $array;
    }
    private function GenerteTemplates($limit = 15000)
    {
        $domainId = $this->domainId;
        if ($domainId) {
            $this->tempArr = [];
            $total_urls = url::select('id', 'url')->where('domain_id', $domainId)->count();
            $completed = url::select('id', 'url')->where('domain_id', $domainId)->whereNotNull("template")->count();
            $loop = (int) ceil($total_urls / $limit);
            if ($total_urls > 0 && $loop > 0) {
                if ($completed == $total_urls) {
                    $this->update_template_progress_in_domains_table();
                    $this->pushTemplateNotification();
                } else {
                    $this->temp_id  = 2;
                    $filaArray = [];
                    for ($x = 0; $x < $loop; $x++) {
                        $urls = url::select('id', 'url', 'template')->where('domain_id', $domainId)->offset($x * $limit)->limit($limit)->orderBy("url", "ASC")->get()->toArray();
                        if (count($urls) > 0) {
                            //$domainPagedata = ($urls[0]["url"] != $this->domain) ? false : true;

                            for ($u = 0; $u < count($urls); $u++) {
                                $segments = [];
                                $segments = explode('/', trim(parse_url($urls[$u]['url'], PHP_URL_PATH), '/'));
                                //
                                $path1 = isset($segments[0]) ? $segments[0] : '';
                                $path2 = isset($segments[1]) ? $segments[1] : '';
                                $path3 = isset($segments[2]) ? $segments[2] : '';
                                $path4 = isset($segments[3]) ? $segments[3] : '';
                                $path5 = isset($segments[4]) ? $segments[4] : '';
                                $path6 = isset($segments[5]) ? $segments[5] : '';
                                $path7 = isset($segments[6]) ? $segments[6] : '';
                                $path8 = isset($segments[7]) ? $segments[7] : '';
                                $path9 = isset($segments[8]) ? $segments[8] : '';
                                $path10 = isset($segments[9]) ? $segments[9] : '';
                                $path11 = isset($segments[10]) ? $segments[10] : '';
                                $path12 = isset($segments[11]) ? $segments[11] : '';
                                $path13 = isset($segments[12]) ? $segments[12] : '';
                                $path14 = isset($segments[13]) ? $segments[13] : '';
                                $path15 = isset($segments[14]) ? $segments[14] : '';
                                //
                                if ($path1 == '') {
                                    $this->updateTemplate($urls[$u]['id'], 1);
                                } else if ($path1 != '' && $path2 == '') {
                                    $this->tempArr["single_path"] = 2;
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr["single_path"]);
                                } else if ($path1 != '' && $path2 != '' && $path3 == '') {
                                    $path = $path1;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 == '') {
                                    $path = $path1 . "-" . $path2;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 != '' && $path6 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . "/" . $path5 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 != '' && $path6 != '' && $path7 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . "/" . $path5 . "/" . $path6 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 != '' && $path6 != '' && $path7 != '' && $path8 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . "/" . $path5 . "/" . $path6 . "/" . $path7 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 != '' && $path6 != '' && $path7 != '' && $path8 != '' && $path9 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . "/" . $path5 . "/" . $path6 . "/" . $path7 . "/" . $path8 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 != '' && $path6 != '' && $path7 != '' && $path8 != '' && $path9 != '' && $path10 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . "/" . $path5 . "/" . $path6 . "/" . $path7 . "/" . $path8 . "/" . $path9 . "/" . $path10 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8 . "-" . $path9 . "-" . $path10;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 != '' && $path6 != '' && $path7 != '' && $path8 != '' && $path9 != '' && $path10 != '' && $path11 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . "/" . $path5 . "/" . $path6 . "/" . $path7 . "/" . $path8 . "/" . $path9 . "/" . $path10 . "/" . $path11 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8 . "-" . $path9 . "-" . $path10 . "-" . $path11;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 != '' && $path6 != '' && $path7 != '' && $path8 != '' && $path9 != '' && $path10 != '' && $path11 != '' && $path12 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . "/" . $path5 . "/" . $path6 . "/" . $path7 . "/" . $path8 . "/" . $path9 . "/" . $path10 . "/" . $path11 . "/" . $path12 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8 . "-" . $path9 . "-" . $path10 . "-" . $path11 . "-" . $path12;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 != '' && $path6 != '' && $path7 != '' && $path8 != '' && $path9 != '' && $path10 != '' && $path11 != '' && $path12 != '' && $path13 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . "/" . $path5 . "/" . $path6 . "/" . $path7 . "/" . $path8 . "/" . $path9 . "/" . $path10 . "/" . $path11 . "/" . $path12 . "/" . $path13 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8 . "-" . $path9 . "-" . $path10 . "-" . $path11 . "-" . $path12 . "-" . $path13;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 != '' && $path6 != '' && $path7 != '' && $path8 != '' && $path9 != '' && $path10 != '' && $path11 != '' && $path12 != '' && $path13 != '' && $path14 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . "/" . $path5 . "/" . $path6 . "/" . $path7 . "/" . $path8 . "/" . $path9 . "/" . $path10 . "/" . $path11 . "/" . $path12 . "/" . $path13 . "/" . $path14 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8 . "-" . $path9 . "-" . $path10 . "-" . $path11 . "-" . $path12 . "-" . $path13 . "-" . $path14;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else if ($path1 != '' && $path2 != '' && $path3 != '' && $path4 != '' && $path5 != '' && $path6 != '' && $path7 != '' && $path8 != '' && $path9 != '' && $path10 != '' && $path11 != '' && $path12 != '' && $path13 != '' && $path14 != '' && $path15 == '') {
                                    $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8;
                                    $childCount = url::select('id', 'url')->where('domain_id', $domainId)->where('url', 'LIKE', '/' . $path2 . "/" . $path2 . "/" . $path3 . "/" . $path4 . "/" . $path5 . "/" . $path6 . "/" . $path7 . "/" . $path8 . "/" . $path9 . "/" . $path10 . "/" . $path11 . "/" . $path12 . "/" . $path13 . "/" . $path14 . "/" . $path15 . '/%')->count();
                                    if ($childCount > 1) {
                                        $path = $path1 . "-" . $path2 . "-" . $path3 . "-" . $path4 . "-" . $path5 . "-" . $path6 . "-" . $path7 . "-" . $path8 . "-" . $path9 . "-" . $path10 . "-" . $path11 . "-" . $path12 . "-" . $path13 . "-" . $path14 . "-" . $path15;
                                    }
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                } else {
                                    $path = "infinity";
                                    if (!array_key_exists($path, $this->tempArr)) {
                                        $this->temp_id++;
                                        $this->tempArr[$path] = $this->temp_id;
                                    }
                                    $this->updateTemplate($urls[$u]['id'], $this->tempArr[$path]);
                                }
                            }
                        }
                        $this->update_template_progress_in_domains_table();
                    }
                    //
                    $this->pushTemplateNotification();
                }
            }
        } else {

            die("Domain Id missing");
        }
        $this->tempArr = [];
        return [];
    }
    private function pushTemplateNotification()
    {
        $total_urls = DB::table('urls')->select('id')->distinct('url')->where('domain_id', $this->domainId)->count();
        $total_templates = DB::table('urls')->select('id')->distinct('template')->where('domain_id', $this->domainId)->count();
        Session::put("total_url", $total_urls);
        Session::put("total_template", $total_templates);
        $this->update_template_status_domains_table(); //update the status in domains table
        $commonObj = new Common;
        $commonObj->writeHtmlLog("Template Completed " . $this->domain);
        $commonObj->insert_domain_e_time($this->domainId);
        // $data["title"] = $this->domain . " - Final report";
        // Notification::route('mail', $this->ToEmails)->notify(new SendSuccessNotification());
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
        $updateObj = url::find($id);
        $updateObj->template = $temp_id;
        $updateObj->update();
    }
}