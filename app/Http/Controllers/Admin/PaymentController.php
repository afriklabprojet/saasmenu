<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\SystemAddons;
use App\Helpers\helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class PaymentController extends Controller
{
    public function index(){

        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        if (SystemAddons::where('unique_identifier', 'subscription')->first() == null && Auth::user()->type == 1) {
            return redirect()->back()->with(['error' => 'You can not charge your end customers in regular license. Please purchase extended license to charge your end customers']);
        } else {

                $getpayment = Payment::where('payment_name','!=','wallet')->where('vendor_id',$vendor_id)->orderBy('reorder_id')->where('is_activate',1)->get();


            return view('admin.payment.payment',compact("getpayment"));
        }
    }
    public function update(Request $request)
    {

        try {
            $data = Payment::find($request->transaction_type);

            if(isset($request->is_available)){
                $data->is_available = $request->is_available;
            }else{
                $data->is_available = 2;
            }
            if(in_array($data->payment_type,['2','3','4','5','7','8','9','10','11','12','13','14','15','16'])){
                $data->environment = @$request->environment != "" ? $request->environment : "";
                $data->public_key = @$request->public_key != "" ? $request->public_key : "";
                $data->secret_key = @$request->secret_key != "" ? $request->secret_key : "";
                $data->currency = @$request->currency != "" ? $request->currency : "";
            }

            if($data->payment_type == '4'){
                $data->encryption_key = $request->encryption_key;
            }else{
                $data->encryption_key = "";
            }

            if($data->payment_type == '12'){
                $data->base_url_by_region = $request->base_url_by_region;
            }else{
                $data->base_url_by_region = "";
            }

            if($request->image)
            {
                $validator = Validator::make($request->all(), [
                    'image' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
                ], [
                    'image.max' => trans('messages.image_size_message'),
                ]);
                if ($validator->fails()) {
                    return redirect()->back()->with('error', trans('messages.image_size_message'). ' ' . helper::appdata('')->image_size .' ' .'MB');
                }
                if (env('Environment') == 'sendbox') {
                    return $this->sendError("This operation was not performed due to demo mode");
                }
                if($data->image != $data->payment_name.".png" && file_exists(config('app.assets_path_url') . 'admin-assets/images/about/payment/'.$data->image)){
                    unlink(config('app.assets_path_url') . 'admin-assets/images/about/payment/'.$data->image);
                }
                $image = 'payment-' . uniqid() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(config('app.assets_path_url') . 'admin-assets/images/about/payment/', $image);
                $data->image = $image;
            }
            $data->payment_name = $request->name;
            if ($data->payment_type == '6') {
                $validator = Validator::make(
                    $request->all(),
                    ['payment_description' => 'required'],
                    ["payment_description.required" => trans('messages.content_required')]
                );
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->withInput();
                } else {
                    $data->payment_description = $request->payment_description;
                }
            }
            $data->save();
            return redirect()->back()->with('success', trans('messages.success'));
        } catch (\Throwable $th) {

            return redirect()->back()->with('error', trans('messages.wrong'));
        }
    }
    public function reorder_payment(Request $request)
    {

        if ($request->has('ids')) {


            $arr = explode('|', $request->input('ids'));
            foreach ($arr as $sortOrder => $id) {
                $menu = Payment::find($id);
                $menu->reorder_id = $sortOrder;
                $menu->save();
            }
        }

        return response()->json(['status' => 1, 'msg' => trans('messages.success')], 200);
    }
}
