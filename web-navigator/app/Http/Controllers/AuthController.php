<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use App\Notifications\SignupActivate;

class AuthController extends Controller
{
    //
    protected $AllowedDomains = [];
    public function __construct()
    {
        $this->AllowedDomains = explode(',', env('ALLOWED_DOMAINS_FOR_SIGNUP'));
    }
    //
    public function Get_Signup(){
        $Roles = DB::table('user_roles')->select('id', 'role', 'status')->where("id", "!=", 1)->where('status', 1)->get()->toArray();
        return view('auth.register')->with(['roles'=>$Roles]);
    }
    //
    public function Login(Request $request)
    {
        $output = ["status" => "error", "message" => "Failed"];
        $email = trim($request->email);
        $password = trim($request->password);

        if (!$email || $email == '') {
            $output['message'] = "Email is required";
            return $output;
        }
        if (!$this->is_valid_email($email)) {
            $output['message'] = "Email is invalid";
            return $output;
        }
        if (!$password || $password == '') {
            $output['message'] = "Password is required";
            return $output;
        }
        $userData = User::where('email', $email)->first();
        if (!isset($userData['email'])) {
            $output['message'] = "The email is not registered with us";
            return $output;
        }
        if ($userData['active'] == 0) {
            $output['message'] = "Access Denied!. You dont have permission to access";
            return $output;
        }
        if (Auth::attempt(['email' => $email, "password" => $password], false)) {
            $output['status'] = "success";
            $output['message'] = "You have successfully logged in!";
        } else {
            $output['message'] = "Wrong password. Try again or click Forgot password to reset it.";
        }
        return response()->json($output);
    }
    //
    public function Signup(Request $request){
        $output = ["status" => "error", "message" => "Failed", "icon"=>"show"];
        $rule = [
    		'name' => 'required|string|max:255',
            'email' => 'required|email|max:225|unique:users,email',
            'password' => 'required|string|min:5|confirmed',
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
            'password.required' => 'Password is required',
            'password.string' => 'Password must be a string',
            'password.min' => 'Password length must be more than 5 charactors',
            'password.confirmed' => 'Password and Confirm password are mismatched',
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
            $output['icon'] = "hide";
            $output['message'] = "Your email domain is not registered! Please contact administrator<br/><br/><a href='mailto:webaccessibilitydeveloper@amnet-systems.com'>webaccessibilitydeveloper@amnet-systems.com</a>";
            return $output;
        }
        // return $emailArr;
        // $userData = User::where('email', $request->email)->first();
        // if (isset($userData['email'])) {
        //     $output['message'] = "The email is already registered with us";
        //     return $output;
        // }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'active' => 1,
            'activation_token' => Str::random(60),
        ]);
        event(new Registered($user));//fired for send email
        $output["message"] = "User registered successfully";
        $output["status"] = "success";
        return $output;
    }
    public function ForgotPassword(Request $request)
    {
        $output = ["status" => "error", "message" => "Failed"];
        $email = trim($request->email);
        if (!$email || $email == '') {
            $output['message'] = "Email is required";
            return $output;
        }
        if (!$this->is_valid_email($email)) {
            $output['message'] = "Email is invalid";
            return $output;
        }
        $userData = User::where('email', $email)->first();
        if (!isset($userData['email'])) {
            $output['message'] = "The email is not registered with us";
            return $output;
        }
        if ($userData['active'] == 0) {
            $output['message'] = "Access Denied!. You dont have permission to access";
            return $output;
        }
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            $output['status'] = "success";
            $output['message'] = "We have e-mailed your password reset link!";
        }
        return $output;
    }
    public function ResetPassword(Request $request)
    {
        $output = ["status" => "error", "message" => "Failed"];
        $email = trim($request->email);
        $token = trim($request->token);
        $password = trim($request->password);
        $password_confirmation = trim($request->password_confirmation);
        if (!$email || $email == '') {
            $output['message'] = "Email is required";
            return $output;
        }
        if (!$this->is_valid_email($email)) {
            $output['message'] = "Email is invalid";
            return $output;
        }
        $reset = DB::table("password_resets")->where('email', $email)->first();

        if (!$reset)
            return redirect()->route('login');

        $expiry  = Carbon::now()->subMinutes(60);
        //
        if ($reset->created_at <= $expiry) {
            $output['message'] = "Reset Password Link has been Expired";
            $output['redirect'] = 1;
            return $output;
        }
        if (!$password || $password == '') {
            $output['message'] = "Password is required";
            return $output;
        }
        if (!$password_confirmation || $password_confirmation == '') {
            $output['message'] = "Confirm Password is required";
            return $output;
        }
        if ($password != $password_confirmation) {
            $output['message'] = "Password and Confirm Password is not matched";
            return $output;
        }
        $userData = User::where('email', $email)->first();
        if (!isset($userData['email'])) {
            $output['message'] = "The email is not registered with us";
            return $output;
        }
        if ($userData['active'] == 0) {
            $output['message'] = "Access Denied!. You dont have permission to access";
            return $output;
        }
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );
        if ($status === Password::PASSWORD_RESET) {
            $output['status'] = "success";
            $output['message'] = "Your password has been changed successfully";
        } else {
            $output['message'] = "Reset Link Expired";
        }
        return $output;
    }
    private function is_valid_email($email)
    {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email)) ? FALSE : TRUE;
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->flush();
        return redirect()->route('/login');
    }

    // 
    public function Get_ConfirmationView(){
        return view('auth.confirmverification');
    }//
    //
    public function VerifyEmail(Request $request){
        $output = ["status"=>"error", "message"=>"Failed", 'email'=>''];
        $id = $request->id;
        $token = $request->token;
        $user = DB::table('users')->select('id','email', 'email_verified_at')->where('id', $id)->first();
        if($user){
            if(is_null($user->email_verified_at)){
                $res = DB::table('users')->where('activation_token', $token)->update(
                    [
                        'email_verified_at'=> Carbon::now(), 
                        'activation_token'=>""
                    ]
                );
                if($res){
                    $output['status'] = "success";
                    $output['message'] = "Email verified successfully";
                }
            }else{
                $output['status'] = "success";
                $output['message'] = "Email already verified. Please login!";
            }
            $output['email'] = $user->email;
        }else{
            $output['message'] = "Verification link expired";
        }
        return $output;
    }
    
}