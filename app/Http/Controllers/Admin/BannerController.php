<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Promotionalbanner;
use App\Models\User;
use App\Helpers\helper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class BannerController extends Controller
{
    public function index()
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getbannerlist = Banner::orderBy('reorder_id')->where('vendor_id',$vendor_id)->get();
        return view('admin.banner.banner', compact('getbannerlist'));
    }
    public function add()
    {
        return view('admin.banner.add');
    }
    public function store(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        if (env('Environment') == 'sendbox') {
            return $this->sendError("This operation was not performed due to demo mode");
        }
        $validator = Validator::make($request->all(), [
            'image' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
        ], [
            'image.max' => trans('messages.image_size_message'),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', trans('messages.image_size_message'). ' ' . helper::appdata('')->image_size .' ' .'MB');
        }
        $image = 'banner-' . uniqid() . "." . $request->file('image')->getClientOriginalExtension();
        $request->file('image')->move(storage_path('app/public/admin-assets/images/banners/'), $image);
        $banner = new Banner();
        $banner->vendor_id = $vendor_id;
        $banner->banner_image = $image;
        $banner->save();
        return redirect('admin/banner')->with('success', trans('messages.success'));
    }
    public function show($id)
    {
        $getbannerdata = Banner::where('id', $id)->first();
        return view('admin.banner.edit', compact('getbannerdata'));
    }
    public function update(Request $request, $id)
    {
        if (env('Environment') == 'sendbox') {
            return $this->sendError("This operation was not performed due to demo mode");
        }
        $validator = Validator::make($request->all(), [
            'image' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
        ], [
            'image.max' => trans('messages.image_size_message'),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', trans('messages.image_size_message'). ' ' . helper::appdata('')->image_size .' ' .'MB');
        }
        $banner = Banner::where('id', $id)->first();
        if ($request->has('image')) {
            if (file_exists(storage_path('app/public/admin-assets/images/banners/' . $banner->banner_image))) {
                unlink(storage_path('app/public/admin-assets/images/banners/' . $banner->banner_image));
            }
            $image = 'banner-' . uniqid() . "." . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(storage_path('app/public/admin-assets/images/banners/'), $image);
            $banner->banner_image = $image;
        }
        $banner->update();
        return redirect('admin/banner')->with('success', trans('messages.success'));
    }
    public function delete($id)
    {
        $banner = Banner::where('id', $id)->first();
        if (file_exists(storage_path('app/public/admin-assets/images/banners/' . $banner->banner_image))) {
            unlink(storage_path('app/public/admin-assets/images/banners/' . $banner->banner_image));
        }
        $banner->delete();
        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function promotional_banner()
    {
        $getbannerlist = Promotionalbanner::with('vendor_info')->orderBy('reorder_id')->get();
        return view('admin.promotionalbanners.index',compact('getbannerlist'));
    }
    public function promotional_banneradd()
    {
        $vendors = User::where('is_available',1)->where('type',2)->get();
        return view('admin.promotionalbanners.add',compact('vendors'));
    }
    public function promotional_bannersave_banner(Request $request)
    {
        if (env('Environment') == 'sendbox') {
            return $this->sendError("This operation was not performed due to demo mode");
        }
        $validator = Validator::make($request->all(), [
            'image' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
        ], [
            'image.max' => trans('messages.image_size_message'),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', trans('messages.image_size_message'). ' ' . helper::appdata('')->image_size .' ' .'MB');
        }
       
        $banner = new Promotionalbanner();
        if ($request->has('image')) {
            $image = 'promotion-' . uniqid() . "." . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(storage_path('app/public/admin-assets/images/banners/'), $image);
            $banner->image = $image;
        }
        $banner->vendor_id = $request->vendor;
        $banner->save();
        return redirect('admin/promotionalbanners')->with('success', trans('messages.success'));
    }


    public function promotional_banneredit(Request $request)
    {
        $vendors = User::where('is_available',1)->where('type',2)->get();
        $banner = Promotionalbanner::where('id',$request->id)->first();
        return view('admin.promotionalbanners.edit',compact('vendors','banner'));
    }
    public function promotional_bannerupdate(Request $request)
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
        $banner = Promotionalbanner::where('id',$request->id)->first();
        if ($request->has('image')) {
            if (file_exists(storage_path('app/public/admin-assets/images/banners/' .$banner->image))) {
                unlink(storage_path('app/public/admin-assets/images/banners/' . $banner->image));
            }
            $image = 'promotion-' . uniqid() . "." . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(storage_path('app/public/admin-assets/images/banners/'), $image);
            $banner->image = $image;
        }
        $banner->vendor_id = $request->vendor;
        $banner->update();
        return redirect('admin/promotionalbanners')->with('success', trans('messages.success'));
    }
    public function promotional_bannerdelete(Request $request)
    {
        $banner = Promotionalbanner::where('id', $request->id)->first();
        if (file_exists(storage_path('app/public/admin-assets/images/banners/' .$banner->image))) {
            unlink(storage_path('app/public/admin-assets/images/banners/' . $banner->image));
        }
        $banner->delete();
        return redirect()->back()->with('success',trans('messages.success'));
    }
    public function reorder_banner(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getbanner = Banner::where('vendor_id',$vendor_id)->get();
        foreach ($getbanner as $banner) {
            foreach ($request->order as $order) {
               $banner = Banner::where('id',$order['id'])->first();
               $banner->reorder_id = $order['position'];
               $banner->save();
            }
        }
        return response()->json(['status' => 1,'msg' => trans('messages.success')], 200);
    }
    public function reorder_promotionalbanner(Request $request)
    {
        
        $getbanner = Promotionalbanner::get();
        foreach ($getbanner as $banner) {
            foreach ($request->order as $order) {
               $banner = Promotionalbanner::where('id',$order['id'])->first();
               $banner->reorder_id = $order['position'];
               $banner->save();
            }
        }
        return response()->json(['status' => 1,'msg' => trans('messages.success')], 200);
    }
}