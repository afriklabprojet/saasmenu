<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryArea;
use Illuminate\Support\Facades\Auth;
class ShippingareaController extends Controller
{
    public function index(){
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getshippingarealist = DeliveryArea::where('vendor_id',$vendor_id)->orderBy('reorder_id')->get();
        return view('admin.shippingarea.index',compact('getshippingarealist'));
    }
    public function add(){
        return view('admin.shippingarea.add');
    }
    public function show(Request $request){
        $shippingareadata = DeliveryArea::find($request->id);
        return view('admin.shippingarea.show',compact('shippingareadata'));
    }
    public function store(Request $request){
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $request->validate([
            'name' => 'required',
            'price' => 'required',
        ],[
            'name.required' => trans('messages.name_required'),
            'price.required' => trans('messages.price_required'),
        ]);
        $shippingarea = DeliveryArea::find($request->id);
        if(empty($shippingarea)){
            $shippingarea = new DeliveryArea();
            $shippingarea->vendor_id = $vendor_id;
        }
        $shippingarea->name = $request->name;
        $shippingarea->price = $request->price;
        $shippingarea->save();
        return redirect('/admin/shipping-area')->with('success',trans('messages.success'));
    }
    public function delete(Request $request){
        try {
            $shippingareadata = DeliveryArea::find($request->id);
            $shippingareadata->delete();
            return redirect('/admin/shipping-area')->with('success',trans('messages.success'));
        } catch (\Throwable $th) {
            return redirect()->back()->with('error',trans('messages.wrong'));
        }
    }
    public function reorder_shippingarea(Request $request)
    {
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
    
        $getarea = DeliveryArea::where('vendor_id', $vendor_id)->get();
        foreach ($getarea as $area) {
            foreach ($request->order as $order) {
                $area = DeliveryArea::where('id', $order['id'])->first();
                $area->reorder_id = $order['position'];
                $area->save();
            }
        }
        return response()->json(['status' => 1, 'msg' => trans('messages.success')], 200);
    }
}