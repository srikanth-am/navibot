<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domain;
use Carbon\Carbon;
use Datatables;
use Session;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
    //
    public function index(Request $request)
    {
        $data = Domain::orderBy('created_at', 'DESC');
        if ($request->ajax()) {
            return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('q_str', function ($row) {
                    return ($row->query_string == 1) ? "No" : "Yes";
                })
                ->addColumn('s_time', function ($row) {
                    return Carbon::parse($row->s_time)->format('d-M-Y h:i A');
                })
                ->addColumn('e_time', function ($row) {
                    return Carbon::parse($row->e_time)->format('d-M-Y h:i A');
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
                    $btn = '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm" aria-label="Download as excel file" title="Download as excel file" data-toggle="modal" data-target="#exampleModalScrollable" onclick="ExportReport(' . $row->id . ",'" . $row->url . "'," . $row->query_string . "," . $row->total_urls . ')"><i class="fa fa-download text-white"></i></a>';
                    if (Auth::user()->role_id == 1) {
                        $btn .= '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm ml-2" aria-label="Delete" title="Delete" onclick="deleteDomains(' . $row->id . ')"><i class="fa fa-trash-alt text-white"></i></a>';
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
        return view('dashboard');
    }
    public function get_time_now()
    {
        return Carbon::now()->format('d-M-Y h:i A');
    }
}