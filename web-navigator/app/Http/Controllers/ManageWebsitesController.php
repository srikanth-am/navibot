<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domain;
// use Datatables;
use Illuminate\Support\Facades\DB;

class ManageWebsitesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    //
    // public function index(Request $request)
    // {
    //     $data = Domain::select('id', 'url', 'query_string', 'total_urls')->orderBy('created_at', 'DESC');
    //     if ($request->ajax()) {
    //         return Datatables::of($data)

    //             ->addIndexColumn()
    //             ->addColumn('q_str', function ($row) {
    //                 return ($row->query_string == 1) ? "No" : "Yes";
    //             })->addColumn('action', function ($row) {
    //                 $btn = '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm" aria-label="Delete sitemaps and URLs" title="Delete sitemaps and URL" onclick="deleteDomains(' . $row->id .
    //                     '"urls only")"><i class="fa fa-trash-alt text-white"></i></a>';
    //                 $btn .= '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm ml-2" aria-label="Delete All datas" title="Delete All datas" onclick="deleteDomains(' . $row->id . '"domain")"><i class="fa fa-trash text-white"></i></a>';
    //                 return $btn;
    //             })->filter(function ($instance) use ($request) {
    //                 if (!empty($request->get('search'))) {
    //                     $instance->where(function ($w) use ($request) {
    //                         $search = $request->get('search');
    //                         $w->orWhere('url', 'LIKE', "%$search%")
    //                             ->orWhere('total_urls', 'LIKE', "%$search%");
    //                     });
    //                 }
    //             })->rawColumns(['action'])->make(true);
    //     }
    //     return view('manage-websites');
    // }
    public function DeleteDomainData($id)
    {
        $output = ["status" => "error", "message" => "failed"];
        $res = DB::table('urls')->where('domain_id', '=', $id)->delete();
        $sitemap = DB::table('sitemaps')->where('domain_id', '=', $id)->delete();
        $domains = DB::table('domains')->where('id', '=', $id)->delete();
        //
        $output["status"] = "success";
        $output["message"] = "Domain deleted with all sitemaps and urls successfully";
        return $output;
    }
}