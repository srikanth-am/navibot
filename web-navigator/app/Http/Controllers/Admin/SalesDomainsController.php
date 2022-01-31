<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
//
use App\Http\Controllers\Controller;
use App\Notifications\SalesDeleteDomainNotification;
use App\Models\UserRole;
use Mail;
use Illuminate\Support\Facades\Notification;
class SalesDomainsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'AdminOnly']);
    }
    public function index(Request $request){
        //
        $data = DB::table('sales_domains')
                ->join('users', 'sales_domains.created_by', '=', 'users.id')
                ->select('sales_domains.*', 'users.name')->orderBy('sales_domains.created_at', 'DESC');
        if ($request->ajax()) {
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('s_time', function ($row) {
                    return Carbon::parse($row->s_time)->format('d-M-Y h:i A');
                })
                
                ->addColumn('action', function ($row) {
                    
                    $btn = '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm ml-2" aria-label="Delete" title="Delete" onclick="deleteSalesDomainsAsAdmin(' . $row->id . ')"><i class="fa fa-trash-alt text-white"></i></a>';
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
        //
        return view('admin.salesdomains');
    }
    public function DeleteSalesDomain($id){
        $output = ["status" => "error", "message" => "failed"];
        //
        $domain = DB::table('sales_domains')->join('users', 'sales_domains.created_by', '=', 'users.id')
        ->select('sales_domains.*', 'users.name', 'users.email')->where('sales_domains.id', $id)->orderBy('sales_domains.created_at', 'DESC')->first();
        //
        $res = DB::table('sales_urls')->where('domain_id', '=', $id)->delete();
        $domains = DB::table('sales_domains')->where('id', '=', $id)->delete();
        //
        Notification::route('mail', $domain->email)->notify(new SalesDeleteDomainNotification($domain));
        //
        $output["status"] = "success";
        $output["message"] = "A domain has been deleted and notification has sent successfully";
        return $output;
    }
}