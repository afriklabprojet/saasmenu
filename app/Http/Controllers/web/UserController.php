<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Helpers\helper;
use App\Models\User;
use App\Models\Settings;
use App\Models\Cart;
use App\Models\SystemAddons;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Lunaweb\RecaptchaV3\Facades\RecaptchaV3;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;

class UserController extends Controller
{
    /**
     * Méthode utilitaire pour résoudre le vendor et les données du store
     *
     * @param Request $request
     * @return array
     */
    private function resolveVendorData(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];

        if ($host == env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
            return compact('storeinfo', 'vdata');
        } else {
            $storeinfocustom = Settings::where('custom_domain', $host)->first();

            if (!$storeinfocustom) {
                // Fallback: utiliser le premier vendor disponible
                $vendorinfo = User::where('type', 2)->where('is_available', 1)->first();
                if (!$vendorinfo) {
                    abort(404, 'Aucun restaurant disponible');
                }
                $storeinfo = helper::storeinfo($vendorinfo->slug);
                $vdata = $vendorinfo->id;
            } else {
                $vendorinfo = User::where('id', $storeinfocustom->vendor_id)->first();
                if (!$vendorinfo) {
                    abort(404, 'Restaurant non trouvé');
                }
                $storeinfo = helper::storeinfo($vendorinfo->slug);
                $vdata = $storeinfocustom->vendor_id;
            }

            return compact('storeinfo', 'vdata');
        }
    }

    public function user_login(Request $request)
    {
        $vendorData = $this->resolveVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vdata'];

        if (helper::appdata(@$vdata)->checkout_login_required == 1) {
            $slug = $request->vendor;
            return view('front.auth.login', compact('slug', 'vdata','storeinfo'));
        } else {
            return redirect()->back();
        }
    }

    public function user_register(Request $request)
    {
        $vendorData = $this->resolveVendorData($request);
        $storeinfo = $vendorData['storeinfo'];
        $vdata = $vendorData['vdata'];

        if (helper::appdata(@$vdata)->checkout_login_required == 1) {
            $slug = $request->vendor;
            return view('front.auth.register', compact('slug', 'vdata','storeinfo'));
        } else {
            return redirect()->back();
        }
    }

    public function userforgotpassword(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfocustom = Settings::where('custom_domain', $host)->first();
            $vendorinfo = User::where('id', $storeinfocustom->vendor_id)->first();
            $storeinfo = helper::storeinfo($vendorinfo->slug);
            $vdata = $storeinfocustom->vendor_id;
        }
        if (helper::appdata(@$vdata)->checkout_login_required == 1) {
            $slug = $request->vendor;
            return view('front.auth.forgotpassword', compact('slug', 'vdata','storeinfo'));
        } else {
            return redirect()->back();
        }
    }
    public function send_password(Request $request)
    {

        $storeinfo = User::where('slug', $request->vendor)->first();

        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => trans('messages.email_required'),
            'email.email' => trans('messages.invalid_email'),
        ]);
        $checkuser = User::where('email', $request->email)->where('is_available', 1)->where('type', 3)->where('vendor_id', $storeinfo->id)->first();
        if (!empty($checkuser)) {
            $password = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, 6);
            $emaildata = helper::emailconfigration($storeinfo->id);
            Config::set('mail', $emaildata);

            $pass = helper::send_pass($request->email, $checkuser->name, $password, $storeinfo->id);
            if ($pass == 1) {
                $checkuser->password = Hash::make($password);
                $checkuser->save();
                return redirect('/' . $request->vendor . '/login')->with('success', trans('messages.success'));
            } else {
                return redirect('/' . $request->vendor . '/login')->with('error', trans('messages.wrong'));
            }
        } else {
            return redirect()->back()->with('error', trans('messages.invalid_user'));
        }
    }
    public function register_customer(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfocustom = Settings::where('custom_domain', $host)->first();
            $vendorinfo = User::where('id', $storeinfocustom->vendor_id)->first();
            $storeinfo = helper::storeinfo($vendorinfo->slug);
            $vdata = $storeinfocustom->vendor_id;
        }

        $validatoremail = Validator::make(['email' => $request->email], [
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where('vendor_id', $vdata)->where('is_deleted', 2)->where('type', 3),
            ]
        ]);
        if ($validatoremail->fails()) {
            return redirect()->back()->with('error', trans('messages.unique_email'));
        }
        $validatormobile = Validator::make(['mobile' => $request->mobile], [
            'mobile' => [
                'required',
                'numeric',
                Rule::unique('users')->where('vendor_id', $vdata)->where('is_deleted', 2)->where('type', 3),
            ]
        ]);
        if ($validatormobile->fails()) {
            return redirect()->back()->with('error', trans('messages.unique_mobile'));
        }
        if (
            SystemAddons::where('unique_identifier', 'google_recaptcha')->first() != null &&
            SystemAddons::where('unique_identifier', 'google_recaptcha')->first()->activated == 1
        ) {
            if (helper::appdata('')->recaptcha_version == 'v2') {
                $request->validate([
                    'g-recaptcha-response' => 'required'
                ], [
                    'g-recaptcha-response.required' => 'The g-recaptcha-response field is required.'
                ]);
            }

            if (helper::appdata('')->recaptcha_version == 'v3') {
                $score = RecaptchaV3::verify($request->get('g-recaptcha-response'), 'contact');
                if ($score <= helper::appdata('')->score_threshold) {
                    return redirect()->back()->with('error', 'You are most likely a bot');
                }
            }
        }



        $old_sid = session()->get('old_session_id');

        $newuser = new User();
        $newuser->name = $request->name;
        $newuser->email = $request->email;
        $newuser->vendor_id = $vdata;
        $newuser->password = hash::make($request->password);
        $newuser->mobile = $request->mobile;
        $newuser->type = "3";
        $newuser->login_type = "email";
        $newuser->image = "default-logo.png";
        $newuser->is_available = "1";
        $newuser->is_verified = "1";
        $newuser->save();


        Auth::login($newuser);

        Cart::where('session_id', $old_sid)->update(['user_id' => @Auth::user()->id, 'session_id' => NULL]);

        $count = Cart::where('user_id', @Auth::user()->id)->count();

        session()->put('cart', $count);


        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            return redirect($request->vendor)->with('success', trans('messages.success'));
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            return redirect('/')->with('sucess', trans('messages.success'));
        }
    }

    public function check_login(Request $request)
    {

        if ($request->vendor == null) {
            $vendor = User::where('slug', session()->get('slug'))->first();
        } else {
            $vendor = User::where('slug', $request->vendor)->first();
        }

        try {
            if ($request->logintype == "normal") {
                $request->validate([
                    'email' => 'required|email',
                    'password' => 'required',
                ], [
                    'email.required' => trans('messages.email_required'),
                    'email.email' =>  trans('messages.invalid_email'),
                    'password.required' => trans('messages.password_required'),
                ]);
                $old_sid = session()->get('old_session_id');
                session()->put('user_login', '1');
                if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'vendor_id' => $vendor->id, 'is_deleted' => 2, 'type' => 3])) {
                    if (Auth::user()->type == 3 && Auth::user()->is_deleted == 2) {
                        if (Auth::user()->is_available == 1) {
                            session()->put('old_sid', $old_sid);

                            Cart::where('session_id', $old_sid)->update(['user_id' => Auth::user()->id, 'session_id' => NULL]);

                            $count = Cart::where('user_id', Auth::user()->id)->count();

                            session()->put('cart', $count);

                            session()->put('vendor_id', $vendor->id);
                            session()->forget('user_login', '1');
                            $host = $_SERVER['HTTP_HOST'];
                            if ($host  ==  env('WEBSITE_HOST')) {
                                return redirect('/' . $request->vendor)->with('sucess', trans('messages.success'));
                            }
                            // if the current host doesn't contain the website domain (meaning, custom domain)
                            else {
                                return redirect('/')->with('sucess', trans('messages.success'));
                            }
                        } else {
                            Auth::logout();
                            return redirect()->back()->with('error', trans('messages.block'));
                        }
                    } else {
                        Auth::logout();
                        return redirect()->back()->with('error', trans('messages.email_password_not_match'));
                    }
                } else {
                    return redirect()->back()->with('error', trans('messages.email_password_not_match'));
                }
            }
        } catch (Exception $exception) {
            return back()->withError($exception->getMessage())->withInput();
        }
    }
    public function send_userpassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => trans('messages.email_required'),
            'email.email' =>  trans('messages.invalid_email'),
        ]);
        if ($request->vendor == null) {
            $vendor = User::where('slug', session()->get('slug'))->first();
        } else {
            $vendor = User::where('slug', $request->vendor)->first();
        }
        $checkuser = User::where('email', $request->email)->where('is_available', 1)->where('vendor_id', $vendor->id)->first();
        if (!empty($checkuser)) {
            $password = substr(str_shuffle($checkuser->password), 1, 6);
            $check_send_mail = helper::send_pass($request->email, $checkuser->name, $password, $vendor->id);
            if ($check_send_mail == 1) {
                $checkuser->password = Hash::make($password);
                $checkuser->save();
                return redirect('/' . $request->vendor . '/login')->with('success', trans('messages.success'));
            } else {
                return redirect('/' . $request->vendor . '/forgot_password')->with('error', trans('messages.wrong'));
            }
        } else {
            return redirect()->back()->with('error', trans('messages.invalid_user'));
        }
    }
    public function logout(Request $request)
    {
        session()->flush();
        Auth::logout();
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            return redirect($request->vendor);
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            return redirect('/')->with('sucess', trans('messages.success'));
        }
    }

    public function profile(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfocustom = Settings::where('custom_domain', $host)->first();
            $vendorinfo = User::where('id', $storeinfocustom->vendor_id)->first();
            $storeinfo = helper::storeinfo($vendorinfo->slug);
            $vdata = $storeinfocustom->vendor_id;
        }
        return view('front.profile', compact('vdata','vdata','storeinfo'));
    }

    public function updateprofile(Request $request)
    {
        try {
            $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfocustom = Settings::where('custom_domain', $host)->first();
            $vendorinfo = User::where('id', $storeinfocustom->vendor_id)->first();
            $storeinfo = helper::storeinfo($vendorinfo->slug);
            $vdata = $storeinfocustom->vendor_id;
        }
        $edituser = User::where('id', $request->id)->first();
        $validatoremail = Validator::make(['email' => $request->email], [
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where('vendor_id', $vdata)->where('is_deleted', 2)->where('type', 3)->ignore($edituser->id),
            ]
        ]);
        if ($validatoremail->fails()) {
            return redirect()->back()->with('error', trans('messages.unique_email'));
        }
        $validatormobile = Validator::make(['mobile' => $request->mobile], [
            'mobile' => [
                'required',
                'numeric',
                Rule::unique('users')->where('vendor_id', $vdata)->where('is_deleted', 2)->where('type', 3)->ignore($edituser->id),
            ]
        ]);
        if ($validatormobile->fails()) {
            return redirect()->back()->with('error', trans('messages.unique_mobile'));
        }
        $edituser->name = $request->name;
        $edituser->email = $request->email;
        $edituser->mobile = $request->mobile;
        $edituser->vendor_id = $vdata;
        if ($request->has('profile')) {
            $validator = Validator::make($request->all(), [
                'profile' => 'image|max:' . helper::imagesize() . '|' . helper::imageext(),
            ], [
                'profile.max' => trans('messages.image_size_message'),
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('error', trans('messages.image_size_message') . ' ' . helper::appdata('')->image_size . ' ' . 'MB');
            }
            if (env('Environment') == 'sendbox') {
                return $this->sendError("This operation was not performed due to demo mode");
            }
            if ($edituser->image != "" && file_exists(storage_path('app/public/admin-assets/images/profile/' . $edituser->image))) {
                unlink(storage_path('app/public/admin-assets/images/profile/' . $edituser->image));
            }
            $edit_image = $request->file('profile');
            $profileImage = 'profile-' . uniqid() . "." . $edit_image->getClientOriginalExtension();
            $edit_image->move(storage_path('app/public/admin-assets/images/profile/'), $profileImage);
            $edituser->image = $profileImage;
        }
        $edituser->update();
        if ($edituser) {
            return redirect($request->vendor . '/profile')->with('success', trans('messages.success'));
        } else {
            return redirect($request->vendor . '/profile')->with('error', trans('messages.wrong'));
        }
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }

    }

    public function changepassword(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfocustom = Settings::where('custom_domain', $host)->first();
            $vendorinfo = User::where('id', $storeinfocustom->vendor_id)->first();
            $storeinfo = helper::storeinfo($vendorinfo->slug);
            $vdata = $storeinfocustom->vendor_id;
        }
        return view('front.change-password', compact('vdata','storeinfo'));
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required',
        ], [
            'current_password.required' => trans('messages.cuurent_password_required'),
            'new_password.required' => trans('messages.new_password_required'),
            'confirm_password.required' => trans('messages.confirm_password_required'),
        ]);
        if (Hash::check($request->current_password, Auth::user()->password)) {
            if ($request->current_password == $request->new_password) {
                return redirect()->back()->with('error', trans('messages.new_old_password_diffrent'));
            } else {
                if ($request->new_password == $request->confirm_password) {
                    $changepassword = User::where('id', Auth::user()->id)->first();
                    $changepassword->password = Hash::make($request->new_password);
                    $changepassword->update();
                    return redirect()->back()->with('success', trans('messages.success'));
                } else {
                    return redirect()->back()->with('error', trans('messages.new_confirm_password_inccorect'));
                }
            }
        } else {
            return redirect()->back()->with('error', trans('messages.old_password_incorect'));
        }
    }

    public function orders(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfocustom = Settings::where('custom_domain', $host)->first();
            $vendorinfo = User::where('id', $storeinfocustom->vendor_id)->first();
            $storeinfo = helper::storeinfo($vendorinfo->slug);
            $vdata = $storeinfocustom->vendor_id;
        }
        $getorders = Order::where('user_id', Auth::user()->id)->where('vendor_id', $vdata);

        if (!empty($request->type)) {
            if ($request->type == "cancelled") {
                $getorders = $getorders->where('status_type',  4);
            }
            if ($request->type == "preparing") {
                $getorders = $getorders->whereIn('status_type', [1, 2]);
            }
            if ($request->type == "delivered") {
                $getorders = $getorders->where('status_type', 3);
            }
        }
        $vendordata = User::where('slug', $request->vendor)->first();
        $getorders = $getorders->orderByDesc('id')->get();
        $totalprocessing = Order::where('user_id', Auth::user()->id)->whereIn('status_type', [1, 2])->where('vendor_id', $vdata)->count();
        $totalrejected = Order::where('user_id', Auth::user()->id)->where('status_type',  4)->where('vendor_id', $vdata)->count();
        $totalcompleted = Order::where('user_id', Auth::user()->id)->where('status_type', 3)->where('vendor_id', $vdata)->count();
        return view('front.orders', compact('vdata','storeinfo', 'vendordata', 'getorders', 'totalprocessing', 'totalrejected', 'totalcompleted'));
    }

    public function loyality(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];
        if ($host  ==  env('WEBSITE_HOST')) {
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // if the current host doesn't contain the website domain (meaning, custom domain)
        else {
            $storeinfocustom = Settings::where('custom_domain', $host)->first();
            $vendorinfo = User::where('id', $storeinfocustom->vendor_id)->first();
            $storeinfo = helper::storeinfo($vendorinfo->slug);
            $vdata = $storeinfocustom->vendor_id;
        }

        $slug = $request->vendor;

        return view('front.loyality', compact('slug', 'vdata','storeinfo'));
    }

    public function deletepassword(Request $request)
    {
        $storeinfo = helper::storeinfo($request->vendor);
        return view('front.delete', compact('vdata','storeinfo'));
    }
    public function deleteaccount(Request $request)
    {

        if (Auth::user() && Auth::user()->type == 3) {
            $user  = User::where('id', Auth::user()->id)->first();
            $user->is_deleted = 1;
            $user->update();
            $emaildata = helper::emailconfigration('');
            Config::set('mail', $emaildata);
            helper::send_mail_delete_account($user);
            session()->flush();
            Auth::logout();
            $host = $_SERVER['HTTP_HOST'];
            if ($host  ==  env('WEBSITE_HOST')) {
                return redirect($request->vendor);
            }
            // if the current host doesn't contain the website domain (meaning, custom domain)
            else {
                return redirect('/')->with('sucess', trans('messages.success'));
            }
        }
    }
}
