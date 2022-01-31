<?php

namespace App\Http\Controllers\Admin;

use Datatables;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

//
use App\Models\UserRole;
class UserController extends Controller
{
    //
    protected $AllowedDomains = [];
    //
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'AdminOnly']);
        $this->AllowedDomains = explode(',', env('ALLOWED_DOMAINS_FOR_SIGNUP'));
    }
    public function index(Request $request){
        //
        $Users = DB::table('users')->join('user_roles', 'users.role_id', '=', 'user_roles.id')
                ->select('users.*', 'user_roles.role')->orderBy('users.created_at', 'DESC');
        //
        if ($request->ajax()) {
            return Datatables::of($Users)
            ->addIndexColumn()
            ->addColumn('email_verified_at', function ($row) {
                $verified = "<i class='fa fa-check text-success' aria-label='Email verified.' title='Verified'></i>";
                if(!$row->email_verified_at){
                    $verified = "<i class='fa fa-exclamation-triangle text-warning' aria-label='Email not verified.' title='Email not verified.'></i>";
                }
                return $verified;
            })
            ->addColumn('created_at', function ($row) {
                
                return Carbon::parse($row->created_at)->format('d-M-Y');;
            })
            ->addColumn('active', function ($row) {
                
                return ($row->active == 1) ? 'Active' : 'Inactive';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="users/edit/'.$row->id.'" class="edit btn bg-blue btn-sm ml-2" aria-label="Delete" title="Edit User"><i class="fa fa-pencil-alt text-white"></i></a>';
                $btn .= '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm ml-2" aria-label="Delete" title="Delete" onclick="deleteUser(' . $row->id . ',`'.$row->role.'`)"><i class="fa fa-trash-alt text-white"></i></a>';
                return $btn;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('name', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%");
                    });
                }
            })
            ->rawColumns(['email_verified_at','action'])
            ->make(true);
        }
        //
        return view("admin.users");
    }
    //
    public function RegisterUser(){
        $Roles = DB::table('user_roles')->select('id', 'role', 'status')->get()->toArray();
        return view('admin.manageusers')->with(['roles'=>$Roles]);
    }
    //
    public function GetEditUser($id){
        $output = [];
        if($id){
            $output = DB::table('users')->select('id', 'name', 'email', 'role_id', 'emp_id', 'tester_id')->where('id', $id)->get();
        }
        return $output;
    }
    public function SaveUser(Request $request){
        $output = ["status" => "error", "message" => "Failed"];
        $rule = [
    		'name' => 'required|string|max:255',
            'email' => 'required|email|max:225|unique:users,email,'.$request->id,
            'role_id' => 'required|numeric|exists:user_roles,id'
    	];
        $errorMsg = [
            'name.required' => 'Name is required',
            'name.string' => 'Name must be a string',
            'name.max' => 'Maximum 255 charactors are allowed for name',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email',
            'email.max' => 'Maximum 255 charactors are allowed for email',
            'email.unique' => 'Email is already exists',
            'role_id.required' => 'Role is required',
            'role_id.numeric' => 'Role must be a numeric value',
            'role_id.exists' => 'Invalid Role',
        ];
        //
        $validator = Validator::make($request->all(), $rule, $errorMsg);
        if ($validator->fails()){
            $output["message"] = $validator->errors()->first();
            return $output;
        }
        //
        $emailArr = explode("@", $request->email);
        if(!in_array($emailArr[1], $this->AllowedDomains)){
            $output['message'] = "Your Email domain are not allowed";
            return $output;
        }
        if($request->id){
            DB::table('users')->where('id', $request->id)->update($request->all());
        }else{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(12345),
                'role_id' => $request->role_id,
                'active' => 1,
            ]);
            event(new Registered($user));//fired for send email
        }
        $output["status"] = "success";
        $action = ($request->id) ? "updated" : "registered";
        $output["message"] = "User ".$action." successfully";
        //
        return $output;   
    }
    //
    public function GetUserRoleView(Request $request){
        //
        $Roles = DB::table('user_roles')->orderBy('created_at', 'DESC');
        //
        if ($request->ajax()) {
            return Datatables::of($Roles)
            ->addIndexColumn()
            ->addColumn('created_at', function ($row) {
                
                return Carbon::parse($row->created_at)->format('d-M-Y');;
            })
            ->addColumn('is_default', function ($row) {
                
                return ($row->is_default == 1) ? 'Yes' : 'No';
            })
            ->addColumn('active', function ($row) {
                
                // return 
                $status = ($row->status == 1) ? 'Active' : 'Inactive';
                $checked = ($status == 'Active') ? 'checked' : '';
                $switch = '<div class="text-center"><div class="form-check form-check-solid form-switch" data-toggle="tooltip"
                    title="'.$status.'">
                    <input class="form-check-input w-45px h-30px" type="checkbox" id="enable_disable_'.$row->id.'" '.$checked.'
                    onclick="change_role_status('.$row->id.')">
                    <label class="form-check-label" for="enable_disable_"'.$row->id.'></label>
                </div></div>';
                return $switch;

            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="user-roles/edit/'.$row->id.'" class="edit btn bg-blue btn-sm ml-2" aria-label="Delete" title="Edit User"><i class="fa fa-pencil-alt text-white"></i></a>';
                $btn .= '<a href="javascript:void(0)" class="edit btn bg-blue btn-sm ml-2" aria-label="Delete" title="Delete" onclick="deleteRole(' . $row->id . ')"><i class="fa fa-trash-alt text-white"></i></a>';
                return $btn;
            })

            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('name', 'LIKE', "%$search%");
                    });
                }
            })
            ->rawColumns(['active','action'])
            ->make(true);
        }
        //
        return view('admin.userroles');
    }
    //
    public function GetUserRoleMange(){
        return view('admin.manage_user_role');
    }
    public function GetUserRole($id){
        $output = [];
        if($id){
            $output = DB::table('user_roles')->select('id', 'role')->where('id', $id)->get();
        }
        return $output;
    }
    public function SaveRoles(Request $request){
        $output = ["status" => "error", "message" => "Failed"];
        $rule = [
    		'role' => 'required|string|max:255|unique:user_roles,role,'.$request->id,
    	];
        $errorMsg = [
            'role.required' => 'Role is required',
            'role.string' => 'Role must be a string',
            'role.max' => 'Maximum 255 charactors are allowed for role',
            'role.unique' => 'Role is already exists',
        ];
        //
        $validator = Validator::make($request->all(), $rule, $errorMsg);
        if ($validator->fails()){
            $output["message"] = $validator->errors()->first();
            return $output;
        }
        //
        if($request->id){
            DB::table('user_roles')->where('id', $request->id)->update($request->all());
        }else{
            $user = UserRole::create([
                'role' => $request->role,
            ]);
        }
        $output["status"] = "success";
        $action = ($request->id) ? "updated" : "created";
        $output["message"] = "User role ".$action." successfully";
        //
        return $output;  
    }
    //
    public function DeleteUserRole($id){
        $output = ["status" => "error", "message" => "failed"];
        //
        $isMapped = DB::table('users')->where('role_id', $id)->count();
        if($isMapped){
            $output['message'] = "Role is mapped with user.";
            return $output;
        }
        //
        $res = DB::table('user_roles')->where('id', '=', $id)->delete();
        //
        $output["status"] = "success";
        $output["message"] = "User role deleted successfully";
        return $output;
    }
    //
    public function ChangeStatus(Request $request){
        $output = ["status" => "error", "message" => "failed"];
        $id = $request->id;
        $action = $request->action;
        $v = 1;
        if($action == 'disable'){
            $v = 0;
        }
        $r = DB::table('user_roles')->where('id', $id)->update(['status'=>$v]);
        if($r){
            $concat = ($action == 'disable') ? 'disabled' : 'enabled';
            $output['status'] = 'success';
            $output['message'] = "User role has been successfully ".$concat;
        }
        return $output;
    }
    //
    public function DeleteUser(Request $request)
    {
        $domainTable = ($request->role == 'Production') ? "domains" : "sales_domains";
        $urlsTaable = ($request->role == 'Production') ? "urls" : "sales_urls";
        $sitemapsTable = ($request->role == 'Production') ? "sitemaps" : "sales_sitemaps";
        //
        if($request->role != 'Production'){
            $domainIds = DB::table($domainTable)->select('id')->where('created_by', $request->id)->get()->toArray();
            if(count($domainIds)){
                foreach($domainIds as $dId){
                    DB::table('sales_urls')->where('domain_id', '=', $dId->id)->delete();
                    DB::table('sales_domains')->where('id', '=', $dId->id)->delete();
                }
            }
        }
        DB::table('users')->where('id', '=', $request->id)->delete();
        return ['status'=>'success', 'message'=>"User has been deleted successfully"];
    }
    //
}

