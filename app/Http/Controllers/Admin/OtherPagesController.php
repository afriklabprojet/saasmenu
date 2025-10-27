<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Privacypolicy;
use App\Models\Terms;
use App\Models\About;
use App\Models\Areas;
use App\Models\Subscriber;
use App\Models\City;
use Illuminate\Support\Facades\Auth;
use App\Models\Faq;
use App\Models\Contact;
use App\Models\RefundPrivacypolicy;

class OtherPagesController extends Controller
{
    public function share(){
        return view('admin.otherpages.share');
    }
    // -----------------------------------------------------------------
    // -------------------  Privacy-Policy  ----------------------------
    // -----------------------------------------------------------------
    public function privacypolicy(){
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getprivacypolicy = Privacypolicy::where('vendor_id',$vendor_id)->first();
        return view('admin.otherpages.privacypolicy',compact('getprivacypolicy'));
    }
    public function privacypolicy_update(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $privacypolicy = Privacypolicy::where('vendor_id',$vendor_id)->first();
        if(empty($privacypolicy)){
            $privacypolicy = new Privacypolicy();
            $privacypolicy->vendor_id = $vendor_id;
        }
        $privacypolicy->privacypolicy_content = $request->privacypolicy;
        $privacypolicy->save();
        return redirect('admin/privacy-policy')->with('success', trans('messages.success'));
    }
    // -----------------------------------------------------------------
    // ------------------- Refund Privacy-Policy  ----------------------------
    // -----------------------------------------------------------------
    public function refundpolicy(){
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getrefundpolicy = RefundPrivacypolicy::where('vendor_id',$vendor_id)->first();
        return view('admin.otherpages.refund_privacypolicy',compact('getrefundpolicy'));
    }
    public function refundpolicy_update(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $refundpolicy = RefundPrivacypolicy::where('vendor_id',$vendor_id)->first();
        if(empty($refundpolicy)){
            $refundpolicy = new RefundPrivacypolicy();
            $refundpolicy->vendor_id = $vendor_id;
        }
        $refundpolicy->refund_policy_content = $request->refundpolicy;
        $refundpolicy->save();
        return redirect('admin/refund-policy')->with('success', trans('messages.success'));
        
    }
    // -----------------------------------------------------------------
    // ------------------- Terms-Condition -----------------------------
    // -----------------------------------------------------------------
    public function termscondition(){
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $gettermscondition = Terms::where('vendor_id',$vendor_id)->first();
        return view('admin.otherpages.termscondition',compact('gettermscondition'));
    }
    public function termscondition_update(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $termscondition = Terms::where('vendor_id',$vendor_id)->first();
        if(empty($termscondition)){
            $termscondition = new Terms();
            $termscondition->vendor_id = $vendor_id;
        }
        $termscondition->terms_content = $request->termscondition;
        $termscondition->save();
        return redirect('admin/terms-conditions')->with('success', trans('messages.success'));  
    }
    // -----------------------------------------------------------------
    // ------------------- About us -----------------------------
    // -----------------------------------------------------------------
    public function aboutus(){
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getaboutus = About::where('vendor_id',$vendor_id)->first();
        return view('admin.otherpages.aboutus',compact('getaboutus'));
    }
    public function aboutus_update(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $aboutus = About::where('vendor_id',$vendor_id)->first();
        if(empty($aboutus)){
            $aboutus = new About();
            $aboutus->vendor_id = $vendor_id;
        }
        $aboutus->about_content = $request->aboutus;
        $aboutus->save();
        return redirect('admin/aboutus')->with('success', trans('messages.success'));
        
    }
    public function faq_index(Request $request)
    {
        $faqs = Faq::orderBy('reorder_id')->get();
       
        return view('admin.faqs.index', compact('faqs'));
    }
    public function faq_add(Request $request)
    {
        return view('admin.faqs.add');
    }
    public function faq_save(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $faqs = new Faq();
        $faqs->vendor_id = $vendor_id;
        $faqs->question = $request->question;
        $faqs->answer = $request->answer;
        $faqs->save();
        return redirect('/admin/faqs')->with('success', trans('messages.success'));
    }
    public function faq_edit(Request $request)
    {
        $getfaq = Faq::where('id', $request->id)->first();
        return view('admin.faqs.edit', compact('getfaq'));
    }
    public function faq_update(Request $request)
    {   
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getfaq = Faq::where('id', $request->id)->first();
        $getfaq->vendor_id = $vendor_id;
        $getfaq->question = $request->question;
        $getfaq->answer = $request->answer;
        $getfaq->update();
        return redirect('/admin/faqs')->with('success', trans('messages.success'));
    }
    public function faq_delete(Request $request)
    {
      
        $deletefaq = Faq::where('id', $request->id)->first();
        $deletefaq->delete();
        return redirect('/admin/faqs')->with('success', trans('messages.success'));
    }
    public function subscribers(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getsubscribers = Subscriber::where('vendor_id', $vendor_id)->orderByDesc('id')->get();
        return view('admin.subscriber.index', compact('getsubscribers'));
    }
    public function subscribers_delete(Request $request)
    {
        $subscriber = Subscriber::find($request->id);
        if (!empty($subscriber)) {
            $subscriber->delete();
            return redirect('/admin/subscribers')->with('success', trans('messages.success'));
        }
        return redirect('/admin/subscribers')->with('error', trans('messages.wrong'));
    }
    public function inquiries(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getinquiries = Contact::where('vendor_id', $vendor_id)->orderByDesc('id')->get();
        return view('admin.inquiries.index', compact('getinquiries'));
    }
    public function inquiries_delete(Request $request)
    {
        $inquiry = Contact::find($request->id);
        if (!empty($inquiry)) {
            $inquiry->delete();
            return redirect('/admin/inquiries')->with('success', trans('messages.success'));
        }
        return redirect('/admin/inquiries')->with('error', trans('messages.wrong'));
    }
    public function cities(Request $request)
    {
        $allcities = City::where('is_deleted',2)->orderBy('reorder_id')->get();
        return view('admin.city.index',compact('allcities'));
    }
    public function add_city(Request $request)
    {
        return view('admin.city.add');
    }
    public function save_city(Request $request)
    {
        $city = new City();
        $city->name = $request->name;
        $city->save();
        return redirect('/admin/cities')->with('success', trans('messages.success'));
    }
    public function edit_city(Request $request)
    {
        $editcity = City::where('id',$request->id)->first();
        return view('admin.city.edit',compact('editcity'));
    }
    public function update_city(Request $request)
    {
        $editcity = City::where('id',$request->id)->first();
        $editcity->name = $request->name;
        $editcity->update();
        return redirect('/admin/cities')->with('success', trans('messages.success'));
    }
    public function delete_city(Request $request)
    {
        $city = City::where('id',$request->id)->first();
        $city->is_deleted = 1;
        $city->update();
        return redirect('/admin/cities')->with('success', trans('messages.success'));
    }
    public function statuschange_city(Request $request)
    {
        $city = City::where('id',$request->id)->first();
        $city->is_available = $request->status;
        $city->update();
        return redirect('/admin/cities')->with('success', trans('messages.success'));
    }
    public function areas(Request $request)
    {
        $allareas = Areas::with('city_info')->where('is_deleted',2)->orderBy('reorder_id')->get();
        return view('admin.areas.index',compact('allareas'));
    }
    public function add_area(Request $request)
    {
        $allcity = City::where('is_deleted',2)->get();
        return view('admin.areas.add',compact('allcity'));
    }
    public function save_area(Request $request)
    {
        $area = new Areas();
        $area->city_id = $request->city;
        $area->area = $request->name;
        $area->save();
        return redirect('/admin/areas')->with('success', trans('messages.success'));
    }
    public function edit_area(Request $request)
    {
        $allcity = City::where('is_deleted',2)->get();
        $editarea = Areas::where('id',$request->id)->first();
        return view('admin.areas.edit',compact('editarea','allcity'));
    }
    public function update_area(Request $request)
    {
        $editarea = Areas::where('id',$request->id)->first();
        $editarea->city_id = $request->city;
        $editarea->area = $request->name;
        $editarea->update();
        return redirect('/admin/areas')->with('success', trans('messages.success'));
    }
    public function delete_area(Request $request)
    {
        $area = Areas::where('id',$request->id)->first();
        $area->is_deleted = 1;
        $area->update();
        return redirect('/admin/areas')->with('success', trans('messages.success'));
    }
    public function statuschange_area(Request $request)
    {
        $area = Areas::where('id',$request->id)->first();
        $area->is_available = $request->status;
        $area->update();
        return redirect('/admin/areas')->with('success', trans('messages.success'));
    }
    
    public function reorder_city(Request $request)
    {
            $getcity = city::where('is_deleted', 2)->get();
            foreach ($getcity as $city) {
                foreach ($request->order as $order) {
                    $city = city::where('id', $order['id'])->first();
                    $city->reorder_id = $order['position'];
                    $city->save();
                }
            }
            return response()->json(['status' => 1, 'msg' => trans('messages.success')], 200);
        
    }

    public function reorder_area(Request $request)
    {
            $getarea = Areas::where('is_deleted', 2)->get();
            foreach ($getarea as $area) {
                foreach ($request->order as $order) {
                    $area = Areas::where('id', $order['id'])->first();
                    $area->reorder_id = $order['position'];
                    $area->save();
                }
            }
            return response()->json(['status' => 1, 'msg' => trans('messages.success')], 200);
        
    }
    public function reorder_faq(Request $request)
    {
        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }
        $getfaqs =  Faq::where('vendor_id',$vendor_id)->get();
        foreach ($getfaqs as $faq) {
            foreach ($request->order as $order) {
                $faq = Faq::where('id', $order['id'])->first();
                $faq->reorder_id = $order['position'];
                $faq->save();
            }
        }
        return response()->json(['status' => 1, 'msg' => trans('messages.success')], 200);
    }
}