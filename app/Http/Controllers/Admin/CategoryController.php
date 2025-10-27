<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Helpers\helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $allcategories = Category::where('vendor_id', $vendor_id)->where('is_deleted', 2)->orderby('reorder_id')->get();
        return view('admin.category.category', compact("allcategories"));
    }
    public function add_category(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }

        // Check category limit before allowing access to add page
        $planInfo = helper::getPlanInfo($vendor_id);
        if ($planInfo['categories_limit'] != -1) {
            $current_categories = Category::where('vendor_id', $vendor_id)->where('is_deleted', 2)->count();
            if ($current_categories >= $planInfo['categories_limit']) {
                return redirect('admin/categories')->with('error', trans('labels.category_limit_reached') . '. ' . trans('labels.upgrade_to_add_more') . '.');
            }
        }

        return view('admin.category.add');
    }
    public function save_category(Request $request)
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

        // Check category limit before insertion
        $planInfo = helper::getPlanInfo($vendor_id);
        if ($planInfo['categories_limit'] != -1) {
            $current_categories = Category::where('vendor_id', $vendor_id)->where('is_deleted', 2)->count();
            if ($current_categories >= $planInfo['categories_limit']) {
                return redirect('admin/categories/add')->with('error', trans('labels.category_limit_reached') . '. ' . trans('labels.upgrade_to_add_more') . '.')->withInput();
            }
        }

        $slug = Str::slug($request->category_name . ' ', '-') . '-' . Str::random(5);
        $savecategory = new Category();
        $validator = Validator::make($request->all(), [
            'category_image' => 'image|max:' . helper::imagesize() . '|' . helper::imageext(),
        ], [
            'category_image.max' => trans('messages.image_size_message'),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', trans('messages.image_size_message') . ' ' . helper::appdata('')->image_size . ' ' . 'MB');
        }

        if ($request->hasfile('category_image')) {

            $image = 'category-' . uniqid() . '.' . $request->category_image->getClientOriginalExtension();
            $request->file('category_image')->move(storage_path('app/public/admin-assets/images/category/'), $image);
            $savecategory->image = $image;
        }
        $savecategory->vendor_id = $vendor_id;
        $savecategory->name = $request->category_name;
        $savecategory->slug = $slug;
        $savecategory->save();
        return redirect('admin/categories/')->with('success', trans('messages.success'));
    }
    public function edit_category(Request $request)
    {
        $editcategory = category::where('slug', $request->slug)->first();
        return view('admin.category.edit', compact("editcategory"));
    }
    public function update_category(Request $request)
    {
        if (env('Environment') == 'sendbox') {
            return $this->sendError("This operation was not performed due to demo mode");
        }
        $validator = Validator::make($request->all(), [
            'category_image' => 'image|max:' . helper::imagesize() . '|' . helper::imageext(),
        ], [
            'category_image.max' => trans('messages.image_size_message'),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', trans('messages.image_size_message') . ' ' . helper::appdata('')->image_size . ' ' . 'MB');
        }
        $slug = Str::slug($request->category_name . ' ', '-') . '-' . Str::random(5);
        $editcategory = Category::where('slug', $request->slug)->first();
        if ($request->hasfile('category_image')) {

            if (file_exists(storage_path('app/public/admin-assets/images/category/' . $editcategory->image))) {
                unlink(storage_path('app/public/admin-assets/images/category/' . $editcategory->image));
            }
            $image = 'category-' . uniqid() . '.' . $request->category_image->getClientOriginalExtension();
            $request->file('category_image')->move(storage_path('app/public/admin-assets/images/category/'), $image);
            $editcategory->image = $image;
        }
        $editcategory->name = $request->category_name;
        $editcategory->slug = $slug;
        $editcategory->update();
        return redirect('admin/categories')->with('success', trans('messages.success'));
    }
    public function change_status(Request $request)
    {
        Category::where('slug', $request->slug)->update(['is_available' => $request->status]);
        return redirect('admin/categories')->with('success', trans('messages.success'));
    }
    public function delete_category(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $checkcategory = Category::where('slug', $request->slug)->where('vendor_id',$vendor_id)->first();
        if (!empty($checkcategory)) {
            $getitem = Item::where('cat_id', $checkcategory->id)->where('vendor_id',$vendor_id)->get();
            foreach ($getitem as $product) {
                $product->cat_id = "";
                $product->update();
            }
            $checkcategory->delete();
            return redirect('admin/categories')->with('success', trans('messages.success'));
        } else {
            return redirect()->back()->with('error', trans('messages.wrong'));
        }
    }
    public function reorder_category(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getcategory = Category::where('vendor_id', $vendor_id)->get();
        foreach ($getcategory as $category) {
            foreach ($request->order as $order) {
                $category = Category::where('id', $order['id'])->first();
                $category->reorder_id = $order['position'];
                $category->save();
            }
        }
        return response()->json(['status' => 1, 'msg' => 'Update Successfully!!'], 200);
    }
}
