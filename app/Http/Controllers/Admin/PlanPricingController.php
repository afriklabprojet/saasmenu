<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\PricingPlan;
use App\Models\User;
use App\Models\Item;
use App\Models\Tax;
use App\Models\Transaction;
use App\Models\SystemAddons;
use App\Helpers\helper;
use Stripe;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class PlanPricingController extends Controller
{
    public function view_plan(Request $request)
    {
        if (SystemAddons::where('unique_identifier', 'subscription')->first() == null) {
            return redirect()->back()->with(['error' => 'You can not charge your end customers in regular license. Please purchase extended license to charge your end customers']);
        } else {
            $allplan = PricingPlan::orderBy('id');
            if (Auth::user()->type == 2) {
                $allplan = $allplan->where('is_available', '1');
            }
            $allplan = $allplan->get();
            return view('admin.plan.plan', compact("allplan"));
        }
    }
    public function add_plan(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $gettaxlist = Tax::where('vendor_id', $vendor_id)->where('is_deleted', 2)->where('is_available', 1)->orderBy('reorder_id')->get();
        $vendors = User::where('type', 2)->where('is_available', 1)->where('is_deleted',2)->get();
        return view('admin.plan.add_plan', compact('gettaxlist', 'vendors'));
    }
    public function save_plan(Request $request)
    {
        $check = User::where('id', '1')->first();

        $request->validate([
            'plan_name' => 'required',
            'plan_price' => 'required',
            'plan_duration' => 'required_if:type,1',
            'plan_max_business' => 'required_if:service_limit_type,1',
            'plan_description' => 'required',
            'plan_features' => 'required',
            'plan_appoinment_limit' => 'required_if:booking_limit_type,1',
            'plan_days' => 'required_if:type,2',
        ], [
            'plan_name.required' => trans('messages.name_required'),
            'plan_price.required' => trans('messages.price_required'),
            'plan_duration.required_if' => trans('messages.duration_required'),
            'plan_max_business.required_if' => trans('messages.plan_max_business'),
            'plan_description.required' => trans('messages.description_required'),
            'plan_features.required' => trans('messages.plan_features'),
            'plan_appoinment_limit.required_if' => trans('messages.appoinment_limit'),
            'plan_days.required_if' => trans('messages.days_required'),
        ]);
        if ($request->custom_domain == "on") {
            $custom_domain = 1;
        } else {
            $custom_domain = "2";
        }
        if ($request->vendor_app == "on") {
            $vendor_app = 1;
        } else {
            $vendor_app = "2";
        }
        if ($request->google_analytics == "on") {
            $google_analytics = 1;
        } else {
            $google_analytics = "2";
        }

        if ($request->coupons == "on") {
            $coupons = 1;
        } else {
            $coupons = "2";
        }

        if ($request->blogs == "on") {
            $blogs = 1;
        } else {
            $blogs = "2";
        }

        if ($request->google_login == "on") {
            $google_login = 1;
        } else {
            $google_login = "2";
        }

        if ($request->facebook_login == "on") {
            $facebook_login = 1;
        } else {
            $facebook_login = "2";
        }

        if ($request->sound_notification == "on") {
            $sound_notification = 1;
        } else {
            $sound_notification = "2";
        }

        if ($request->whatsapp_message == "on") {
            $whatsapp_message = 1;
        } else {
            $whatsapp_message = "2";
        }
        if ($request->employee == "on") {
            $employee = 1;
        } else {
            $employee = "2";
        }
        if ($request->telegram_message == "on") {
            $telegram_message = 1;
        } else {
            $telegram_message = "2";
        }

        if ($request->pos == "on") {
            $pos = 1;
        } else {
            $pos = "2";
        }
        if ($request->pwa == "on") {
            $pwa = 1;
        } else {
            $pwa = "2";
        }
        if ($request->tableqr == "on") {
            $tableqr = 1;
        } else {
            $tableqr = "2";
        }

        // Handle WhatsApp Integration
        if ($request->whatsapp_integration == "on") {
            $whatsapp_integration = 1;
        } else {
            $whatsapp_integration = "2";
        }

        // Handle Analytics
        if ($request->analytics == "on") {
            $analytics = 1;
        } else {
            $analytics = "2";
        }

        $saveplan = new PricingPlan();
        $saveplan->name = $request->plan_name;
        $saveplan->themes_id = "0";
        $saveplan->description = $request->plan_description;
        $saveplan->features = implode("|", $request->plan_features);
        $saveplan->price = $request->plan_price;
        $saveplan->plan_type = $request->type;
        $saveplan->tax = empty($request->plan_tax)  ? null : implode("|", $request->plan_tax);
        if ($request->type == "1") {
            $saveplan->duration = $request->plan_duration;
            $saveplan->days = "";
        }
        if ($request->type == "2") {
            $saveplan->duration = "";
            $saveplan->days = $request->plan_days;
        }
        if ($request->service_limit_type == "1") {
            $saveplan->order_limit = $request->plan_max_business;
        } elseif ($request->service_limit_type == "2") {
            $saveplan->order_limit = -1;
        }
        if ($request->booking_limit_type == "1") {
            $saveplan->appointment_limit = $request->plan_appoinment_limit;
        } elseif ($request->booking_limit_type == "2") {
            $saveplan->appointment_limit = -1;
        }

        // Handle products_limit
        if ($request->products_limit_type == "1") {
            $saveplan->products_limit = $request->products_limit;
        } elseif ($request->products_limit_type == "2") {
            $saveplan->products_limit = -1;
        }

        // Handle categories_limit
        if ($request->categories_limit_type == "1") {
            $saveplan->categories_limit = $request->categories_limit;
        } elseif ($request->categories_limit_type == "2") {
            $saveplan->categories_limit = -1;
        }

        // Handle staff_limit
        if ($request->staff_limit_type == "1") {
            $saveplan->staff_limit = $request->staff_limit;
        } elseif ($request->staff_limit_type == "2") {
            $saveplan->staff_limit = -1;
        }

        $saveplan->custom_domain = $custom_domain;
        $saveplan->vendor_app = $vendor_app;
        $saveplan->google_analytics = $google_analytics;
        $saveplan->coupons = $coupons;
        $saveplan->blogs = $blogs;
        $saveplan->google_login = $google_login;
        $saveplan->facebook_login = $facebook_login;
        $saveplan->sound_notification = $sound_notification;
        $saveplan->whatsapp_message = $whatsapp_message;
        $saveplan->telegram_message = $telegram_message;
        $saveplan->pos = $pos;
        $saveplan->pwa = $pwa;
        $saveplan->tableqr = $tableqr;
        $saveplan->role_management = $employee;
        $saveplan->whatsapp_integration = $whatsapp_integration;
        $saveplan->analytics = $analytics;
        $saveplan->themes_id = implode(",", $request->themecheckbox);
        $saveplan->vendor_id = $request->vendors != "" && $request->vendors != null ? implode("|", $request->vendors) : $request->vendors;
        $saveplan->save();
        return redirect('admin/plan')->with('success', trans('messages.success'));
    }
    public function edit_plan($id)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $editplan = PricingPlan::where('id', $id)->first();
        $gettaxlist = Tax::where('vendor_id',$vendor_id)->where('is_deleted', 2)->where('is_available', 1)->get();
        $vendors = User::where('type', 2)->where('is_available', 1)->where('is_deleted',2)->get();
        return view('admin.plan.edit_plan', compact("editplan", "gettaxlist", "vendors"));
    }
    public function update_plan(Request $request, $id)
    {

        $request->validate([
            'plan_name' => 'required',
            'plan_price' => 'required',
            'plan_duration' => 'required_if:type,1',
            'plan_max_business' => 'required_if:service_limit_type,1',
            'plan_description' => 'required',
            'plan_features' => 'required',
            'plan_appoinment_limit' => 'required_if:booking_limit_type,1',
            'plan_days' => 'required_if:type,2',
        ], [
            'plan_name.required' => trans('messages.name_required'),
            'plan_price.required' =>  trans('messages.price_required'),
            'plan_duration.required_if' => trans('messages.plan_duration'),
            'plan_max_business.required_if' => trans('messages.plan_max_business'),
            'plan_description.required' => trans('messages.description_required'),
            'plan_features.required' => trans('messages.plan_features'),
            'plan_appoinment_limit.required_if' => trans('messages.appoinment_limit'),
            'plan_days.required_if' => trans('messages.days_required'),
        ]);

        if ($request->custom_domain == "on") {
            $custom_domain = 1;
        } else {
            $custom_domain = "2";
        }
        if ($request->google_analytics == "on") {
            $google_analytics = 1;
        } else {
            $google_analytics = "2";
        }
        if ($request->vendor_app == "on") {
            $vendor_app = 1;
        } else {
            $vendor_app = "2";
        }
        if ($request->coupons == "on") {
            $coupons = 1;
        } else {
            $coupons = "2";
        }
        if ($request->blogs == "on") {
            $blogs = 1;
        } else {
            $blogs = "2";
        }
        if ($request->google_login == "on") {
            $google_login = 1;
        } else {
            $google_login = "2";
        }
        if ($request->facebook_login == "on") {
            $facebook_login = 1;
        } else {
            $facebook_login = "2";
        }
        if ($request->sound_notification == "on") {
            $sound_notification = 1;
        } else {
            $sound_notification = "2";
        }
        if ($request->whatsapp_message == "on") {
            $whatsapp_message = 1;
        } else {
            $whatsapp_message = "2";
        }
        if ($request->telegram_message == "on") {
            $telegram_message = 1;
        } else {
            $telegram_message = "2";
        }
        if ($request->pos == "on") {
            $pos = 1;
        } else {
            $pos = "2";
        }
        if ($request->pwa == "on") {
            $pwa = 1;
        } else {
            $pwa = "2";
        }
        if ($request->tableqr == "on") {
            $tableqr = 1;
        } else {
            $tableqr = "2";
        }
        if ($request->employee == "on") {
            $employee = 1;
        } else {
            $employee = "2";
        }

        // Handle WhatsApp Integration
        if ($request->whatsapp_integration == "on") {
            $whatsapp_integration = 1;
        } else {
            $whatsapp_integration = "2";
        }

        // Handle Analytics
        if ($request->analytics == "on") {
            $analytics = 1;
        } else {
            $analytics = "2";
        }

        $exitplan = PricingPlan::where('price', '0')->count();
        if ($exitplan > 1 && $request->plan_price == '0') {
            return redirect('admin/plan/edit-' . $id)->with('error', trans('messages.already_exist_plan'));
        } else {
            $editplan = PricingPlan::where('id', $id)->first();
            $editplan->name = $request->plan_name;
            $editplan->themes_id = "0";
            $editplan->description = $request->plan_description;
            $editplan->features = implode("|", $request->plan_features);
            $editplan->price = $request->plan_price;
            $editplan->plan_type = $request->type;
            $editplan->tax = empty($request->plan_tax)  ? null : implode("|", $request->plan_tax);
            if ($request->type == "1") {
                $editplan->duration = $request->plan_duration;
                $editplan->days = "";
            }
            if ($request->type == "2") {
                $editplan->duration = "";
                $editplan->days = $request->plan_days;
            }
            if ($request->service_limit_type == "1") {
                $editplan->order_limit = $request->plan_max_business;
            } elseif ($request->service_limit_type == "2") {
                $editplan->order_limit = -1;
            }
            if ($request->booking_limit_type == "1") {
                $editplan->appointment_limit = $request->plan_appoinment_limit;
            } elseif ($request->booking_limit_type == "2") {
                $editplan->appointment_limit = -1;
            }

            // Handle products_limit
            if ($request->products_limit_type == "1") {
                $editplan->products_limit = $request->products_limit;
            } elseif ($request->products_limit_type == "2") {
                $editplan->products_limit = -1;
            }

            // Handle categories_limit
            if ($request->categories_limit_type == "1") {
                $editplan->categories_limit = $request->categories_limit;
            } elseif ($request->categories_limit_type == "2") {
                $editplan->categories_limit = -1;
            }

            // Handle staff_limit
            if ($request->staff_limit_type == "1") {
                $editplan->staff_limit = $request->staff_limit;
            } elseif ($request->staff_limit_type == "2") {
                $editplan->staff_limit = -1;
            }

            $editplan->custom_domain = $custom_domain;
            $editplan->google_analytics = $google_analytics;
            $editplan->vendor_app = $vendor_app;
            $editplan->coupons = $coupons;
            $editplan->blogs = $blogs;
            $editplan->google_login = $google_login;
            $editplan->facebook_login = $facebook_login;
            $editplan->sound_notification = $sound_notification;
            $editplan->whatsapp_message = $whatsapp_message;
            $editplan->telegram_message = $telegram_message;
            $editplan->pos = $pos;
            $editplan->pwa = $pwa;
            $editplan->tableqr = $tableqr;
            $editplan->role_management = $employee;
            $editplan->whatsapp_integration = $whatsapp_integration;
            $editplan->analytics = $analytics;
            $editplan->themes_id = implode(",", $request->themecheckbox);
            $editplan->vendor_id = $request->vendors != "" && $request->vendors != null ? implode("|", $request->vendors) : $request->vendors;
            $editplan->update();
            return redirect('admin/plan')->with('success', trans('messages.success'));
        }
    }
    public function status_change($id, $status)
    {
        PricingPlan::where('id', $id)->update(['is_available' => $status]);
        return redirect('admin/plan')->with('success', trans('messages.success'));
    }
    public function select_plan($id)
    {

        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $plan = PricingPlan::where('id', $id)->first();
        $totalitem = Item::where('vendor_id', $vendor_id)->count();
        if (!empty($totalitem)) {
            if ($plan->order_limit != -1) {
                if ($plan->order_limit < $totalitem) {
                    return redirect('admin/plan')->with('error', trans('messages.not_eligible_for_plan'));
                }
            }
        }
        $checkbanktransfer = helper::checkplan($vendor_id, '');
        $data = json_decode(json_encode($checkbanktransfer), true);
        if ($data['original']['status'] == 2 && $data['original']['bank_transfer'] == 1) {
            return redirect('admin/plan')->with('error', $data['original']['message']);
        }
        if ($plan->price > 0) {
            $paymentmethod = Payment::where('vendor_id', '1')->where('is_available', '1')->where('is_activate', 1)->orderBy('reorder_id')->get();
            return view('admin.plan.plan_payment', compact('plan', 'paymentmethod'));
        } else {
            $transaction = new Transaction();
            $transaction->vendor_id = $vendor_id;
            $transaction->plan_name = $plan->name;
            $transaction->plan_id = $id;
            $transaction->payment_type = "";
            $transaction->payment_id = "";
            $transaction->amount = $plan->price;
            $transaction->service_limit = $plan->order_limit;
            $transaction->appoinment_limit = $plan->appointment_limit;
            $tax_amount = [];
            $tax_name = [];
            if ($plan->tax != null && $plan->tax != "") {
                $tax_detail = helper::gettax($plan->tax);
                $tax_amount = [];
                $tax_name = [];
                foreach ($tax_detail as $tax) {
                    if ($tax->type == 1) {
                        $tax_amount[] = $tax->tax;
                    } else {
                        $tax_amount[] = ($tax->tax / 100) * $plan->price;
                    }

                    $tax_name[] = $tax->name;
                }
            }
            $transaction->tax = implode('|', $tax_amount);
            $transaction->tax_name = implode('|', $tax_name);
            $transaction->grand_total =  0;
            $transaction->expire_date = helper::get_plan_exp_date($plan->duration, $plan->days);
            $transaction->duration = $plan->duration;
            $transaction->days = $plan->days;
            $transaction->purchase_date = date("Y-m-d h:i:sa");
            $transaction->duration = $plan->duration;
            $transaction->themes_id = $plan->themes_id;
            $transaction->custom_domain = $plan->custom_domain;
            $transaction->google_analytics = $plan->google_analytics;
            $transaction->vendor_app = $plan->vendor_app;
            $transaction->coupons = $plan->coupons;
            $transaction->blogs = $plan->blogs;
            $transaction->google_login = $plan->google_login;
            $transaction->facebook_login = $plan->facebook_login;
            $transaction->sound_notification = $plan->sound_notification;
            $transaction->whatsapp_message = $plan->whatsapp_message;
            $transaction->telegram_message = $plan->telegram_message;
            $transaction->pos = $plan->pos;
            $transaction->pwa = $plan->pwa;
            $transaction->tableqr = $plan->tableqr;
            $transaction->transaction_number = Str::upper(Str::random(8));
            $transaction->save();
            if(session()->has('discount_data'))
            {
               session()->forget('discount_data');
            }
            User::where('id', $vendor_id)->update(['plan_id' => $id, 'purchase_amount' => $plan->price, 'purchase_date' => Carbon::now()->toDateTimeString()]);
            $emaildata = helper::emailconfigration(helper::appdata('')->id);
            Config::set('mail', $emaildata);
            helper::send_subscription_email(Auth::user()->email, Auth::user()->name, $plan->name, helper::get_plan_exp_date($plan->duration, $plan->days), helper::currency_formate($plan->price, ""), "-", "-");
            return redirect()->back()->with('success', trans('messages.success'));
        }
    }


    public function success(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        try {
            if (@$request->paymentId != "") {
                $paymentid = $request->paymentId;
            }
            if (@$request->payment_id != "") {
                $paymentid = $request->payment_id;
            }
            if (@$request->transaction_id != "") {
                $paymentid = $request->transaction_id;
            }

            if (session()->get('payment_type') == "11") {
                if ($request->code == "PAYMENT_SUCCESS") {
                    $paymentid = $request->transactionId;
                } else {
                    return redirect('admin/plan')->with('error', trans('messages.wrong'));
                }
            }

            if (session()->get('payment_type') == "12") {
                $checkstatus = app('App\Http\Controllers\addons\PayTabController')->checkpaymentstatus(session()->get('tran_ref'), '1');

                if ($checkstatus == "A") {
                    $paymentid = session()->get('tran_ref');
                } else {
                    return redirect('admin/plan')->with('error', session()->get('paytab_response'));
                }
            }

            if (session()->get('payment_type') == "13") {

                $checkstatus = app('App\Http\Controllers\addons\MollieController')->checkpaymentstatus(session()->get('tran_ref'), '1');

                if ($checkstatus == "A") {
                    $paymentid = session()->get('tran_ref');
                } else {
                    return redirect('admin/plan')->with('error', session()->get('messages.wrong'));
                }
            }

            if (session()->get('payment_type') == "14") {
                if ($request->status == "Completed") {
                    $paymentid = $request->transaction_id;
                } else {
                    return redirect('admin/plan')->with('error', trans('messages.wrong'));
                }
            }

            if (session()->get('payment_type') == "15") {
                $checkstatus = app('App\Http\Controllers\addons\XenditController')->checkpaymentstatus(session()->get('tran_ref'), '1');

                if ($checkstatus == "PAID") {
                    $paymentid = session()->get('payment_id');
                } else {
                    return redirect('admin/plan')->with('error', session()->get('messages.wrong'));
                }
            }

            $plan = PricingPlan::where('id', session()->get('plan_id'))->first();
            $checkuser = User::find($vendor_id);
            $checkuser->plan_id = session()->get('plan_id');
            $checkuser->purchase_amount = session()->get('amount');
            $checkuser->purchase_date = date("Y-m-d h:i:sa");
            $checkuser->save();
            $transaction = new Transaction;
            $transaction->vendor_id = $vendor_id;
            $transaction->plan_name = $plan->name;
            $tax_amount = [];
            $tax_name = [];
            if ($plan->tax != null && $plan->tax != "") {
                $tax_detail = helper::gettax($plan->tax);
                $tax_amount = [];
                $tax_name = [];
                foreach ($tax_detail as $tax) {
                    if ($tax->type == 1) {
                        $tax_amount[] = $tax->tax;
                    } else {
                        $tax_amount[] = ($tax->tax / 100) * $plan->price;
                    }
                    $tax_name[] = $tax->name;
                }
            }
            $transaction->tax = implode('|', $tax_amount);
            $transaction->tax_name = implode('|', $tax_name);
            $transaction->plan_id = session()->get('plan_id');
            $transaction->payment_type = session()->get('payment_type');
            $transaction->amount = $plan->price;
            if (session()->has('discount_data')) {
                $transaction->offer_code = session()->get('discount_data')['offer_code'];
                $transaction->offer_amount = session()->get('discount_data')['offer_amount'];
            }
            $transaction->grand_total =  session()->get('amount');
            $transaction->payment_id = @$paymentid;
            $transaction->service_limit = $plan->order_limit;
            $transaction->appoinment_limit = $plan->appointment_limit;
            $transaction->expire_date = helper::get_plan_exp_date($plan->duration, $plan->days);
            $transaction->duration = $plan->duration;
            $transaction->days = $plan->days;
            $transaction->custom_domain = $plan->custom_domain;
            $transaction->google_analytics = $plan->google_analytics;
            $transaction->vendor_app = $plan->vendor_app;
            $transaction->coupons = $plan->coupons;
            $transaction->blogs = $plan->blogs;
            $transaction->google_login = $plan->google_login;
            $transaction->facebook_login = $plan->facebook_login;
            $transaction->sound_notification = $plan->sound_notification;
            $transaction->whatsapp_message = $plan->whatsapp_message;
            $transaction->telegram_message = $plan->telegram_message;
            $transaction->pos = $plan->pos;
            $transaction->pwa = $plan->pwa;
            $transaction->tableqr = $plan->tableqr;
            $transaction->status = "2";
            $transaction->themes_id = $plan->themes_id;
            $transaction->role_management = $plan->role_management;
            $transaction->purchase_date = date("Y-m-d h:i:sa");
            $transaction->transaction_number = Str::upper(Str::random(8));
            $transaction->save();
            $emaildata = helper::emailconfigration(helper::appdata('')->id);
            Config::set('mail', $emaildata);

            helper::send_subscription_email(Auth::user()->email, Auth::user()->name, $plan->name, helper::get_plan_exp_date($plan->duration, $plan->days), helper::currency_formate($plan->price, ""), $request->payment_type, @$paymentid);

            session()->forget(['amount', 'plan_id', 'payment_type', 'currency', 'returnUrl', 'successurl', 'failureurl', 'invoicepay']);
            return redirect('admin/plan')->with('success', trans('messages.success'));
        } catch (\Throwable $th) {
            return $th;
        }
    }
    public function buyplan(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        try {
            $paymenttype = "";
            //payment_type = COD : 1,RazorPay : 2, Stripe : 3, Flutterwave : 4, Paystack : 5, Mercado Pago : 7, PayPal : 8, MyFatoorah : 9, toyyibpay : 10, phonepe : 11, paytab : 12, Mollie : 13, Khalti = 14, Xendit = 15
            $plan = PricingPlan::where('id', $request->plan_id)->first();
            if ($request->payment_type == "stripe") {
                $paymentmethod = Payment::where('payment_type', 3)->where('is_available', 1)->first();
                Stripe\Stripe::setApiKey($paymentmethod->secret_key);
                $charge = Stripe\Charge::create([
                    'amount' => $request->amount * 100,
                    'currency' => $request->currency,
                    'description' => 'Plan Subscription',
                    'source' => $request->payment_id,
                ]);
                $payment_id = $charge->id;
            } else {
                $payment_id = $request->payment_id;
            }
            if ($request->payment_type == '6') {
                if ($request->hasFile('screenshot')) {
                    if (env('Environment') == 'sendbox') {
                        return $this->sendError("This operation was not performed due to demo mode");
                    }
                    $validator = Validator::make($request->all(), [
                        'screenshot' => 'image|max:' . helper::imagesize() . '|' . helper::imageext(),
                    ], [
                        'screenshot.max' => trans('messages.image_size_message'),
                    ]);

                    if ($validator->fails()) {
                        return redirect()->back()->withErrors($validator)->withInput();
                    } else {
                        $filename = 'screenshot-' . uniqid() . "." . $request->file('screenshot')->getClientOriginalExtension();
                        $request->file('screenshot')->move(env('ASSETSPATHURL') . 'admin-assets/images/screenshot/', $filename);
                    }
                }
                $payment_id = "";
                $status = 1;
            } elseif ($request->payment_type == "1") {
                $status = 1;
            } else {
                $status = 2;
            }

            $checkuser = User::find($vendor_id);
            $checkuser->plan_id = $request->plan_id;
            $checkuser->purchase_amount = $request->amount;
            $checkuser->purchase_date = date("Y-m-d h:i:sa");
            $checkuser->save();
            $transaction = new Transaction();
            if ($request->payment_type == '6') {
                $transaction->screenshot = $filename;
            }
            $tax_amount = [];
            $tax_name = [];
            if ($plan->tax != null && $plan->tax != "") {
                $tax_detail = helper::gettax($plan->tax);
                $tax_amount = [];
                $tax_name = [];
                foreach ($tax_detail as $tax) {
                    if ($tax->type == 1) {
                        $tax_amount[] = $tax->tax;
                    } else {
                        $tax_amount[] = ($tax->tax / 100) * $plan->price;
                    }
                    $tax_name[] = $tax->name;
                }
            }
            $transaction->tax = implode('|', $tax_amount);
            $transaction->tax_name = implode('|', $tax_name);
            $transaction->vendor_id = $vendor_id;
            $transaction->plan_name = $plan->name;
            $transaction->plan_id = $request->plan_id;
            $transaction->payment_type = $request->payment_type;
            $transaction->payment_id = $payment_id;
            $transaction->amount = $plan->price;
            $transaction->grand_total = $request->amount;
            $transaction->service_limit = $plan->order_limit;
            $transaction->appoinment_limit = $plan->appointment_limit;
            $transaction->status = $status;
            $transaction->purchase_date = date("Y-m-d h:i:sa");
            $transaction->expire_date = helper::get_plan_exp_date($plan->duration, $plan->days);
            $transaction->duration = $plan->duration;
            $transaction->days = $plan->days;
            $transaction->themes_id = $plan->themes_id;
            $transaction->custom_domain = $plan->custom_domain;
            $transaction->google_analytics = $plan->google_analytics;
            $transaction->vendor_app = $plan->vendor_app;
            $transaction->coupons = $plan->coupons;
            $transaction->blogs = $plan->blogs;
            $transaction->google_login = $plan->google_login;
            $transaction->facebook_login = $plan->facebook_login;
            $transaction->sound_notification = $plan->sound_notification;
            $transaction->whatsapp_message = $plan->whatsapp_message;
            $transaction->telegram_message = $plan->telegram_message;
            $transaction->pos = $plan->pos;
            $transaction->pwa = $plan->pwa;
            $transaction->tableqr = $plan->tableqr;
            $transaction->role_management = $plan->role_management;
            if ($request->payment_type == 6) {
                $transaction->offer_code = $request->modal_offer_code;
                $transaction->offer_amount = $request->modal_discount;
            } else {
                $transaction->offer_code = $request->offer_code;
                $transaction->offer_amount = $request->discount;
            }
            $transaction->transaction_number = Str::upper(Str::random(8));
            $transaction->save();
            $emaildata = helper::emailconfigration(helper::appdata('')->id);

            Config::set('mail', $emaildata);
            $user = User::where('id',$vendor_id)->where('is_available',1)->where('is_deleted',2)->first();
            if ($request->payment_type == '6') {
                $admindata = User::select('name', 'email')->where('id', '1')->first();

                helper::bank_transfer_request($user->email, $user->name, $admindata->email, $admindata->name);
                return redirect('admin/plan')->with('success', trans('messages.success'));
            } else {

                helper::send_subscription_email($user->email, $user->name, $plan->name, helper::get_plan_exp_date($plan->duration, $plan->days), helper::currency_formate($plan->price, ""), $request->payment_type, @$payment_id);
                return response()->json(['status' => 1, 'message' => trans('messages.success')], 200);
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
            if ($request->payment_type == '6') {
                return redirect()->back()->with('error', trans('messages.wrong'));
            } else {
                return response()->json(['status' => 0, 'message' => trans('messages.wrong')], 200);
            }
        }
    }
    public function delete($id)
    {
        PricingPlan::where('id', $id)->delete();
        return redirect('admin/plan')->with('success', trans('messages.success'));
    }
    public function plan_details(Request $request)
    {
        $plan = Transaction::with('vendor_info', 'plan_info')->where('id', $request->id)->first();
        return view('admin.plan.plan_details', compact('plan'));
    }
    public function generatepdf(Request $request)
    {
        $plan = Transaction::where('id', $request->id)->first();
        $user = User::where('id', $plan->vendor_id)->first();
        $pdf = Pdf::loadView('admin.plan.plandetailspdf', ['plan' => $plan, 'user' => $user]);
        return $pdf->download(trans('labels.transaction'). $plan->transaction_number . '.pdf');
    }

    public function reorder_plan(Request $request)
    {

        if ($request->has('ids')) {

            $arr = explode('|', $request->input('ids'));
            foreach ($arr as $sortOrder => $id) {
                $menu = PricingPlan::find($id);
                $menu->reorder_id = $sortOrder;
                $menu->save();
            }
        }
        return response()->json(['status' => 1, 'msg' => trans('messages.success')], 200);
    }
    public function themeimages(Request $request)
    {

        $newpath = [];
        $output = '';
        foreach ($request->theme_id as $theme_id) {
            $image = 'theme-' . $theme_id;
            if (file_exists(storage_path('app/public/admin-assets/images/theme/' . $image . '.png'))) {
                $image = 'theme-' . $theme_id . '.png';
                $path = url(env('ASSETSPATHURL') . 'admin-assets/images/theme/' . $image);
            } elseif (file_exists(storage_path('app/public/admin-assets/images/theme/' . $image . '.jpeg'))) {
                $image = 'theme-' . $theme_id . '.jpeg';
                $path = url(env('ASSETSPATHURL') . 'admin-assets/images/theme/' . $image);
            } elseif (file_exists(storage_path('app/public/admin-assets/images/theme/' . $image . '.jpg'))) {
                $image = 'theme-' . $theme_id . '.jpg';
                $path = url(env('ASSETSPATHURL') . 'admin-assets/images/theme/' . $image);
            } elseif (file_exists(storage_path('app/public/admin-assets/images/theme/' . $image . '.webp'))) {
                $image = 'theme-' . $theme_id . '.webp';
                $path = url(env('ASSETSPATHURL') . 'admin-assets/images/theme/' . $image);
            } else {
                $path =  asset('storage/app/public/admin-assets/images/about/defaultimages/item-placeholder.png');
            }
            $newpath[] = $path;
        }
        $html = view('admin.theme.themeimages', compact('newpath'))->render();
        return response()->json(['status' => 1, 'output' => $html], 200);
    }
}
