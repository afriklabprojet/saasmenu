<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\AppSettings;
use App\Models\SocialLinks;
use App\Helpers\helper;
use App\Models\Footerfeatures;
use App\Models\City;
use App\Models\Order;
use App\Models\Pixcel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{

    public function settings_index(Request $request)
    {
        if(Auth::user()->type == 4)
        {
           $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $settingdata = Settings::where('vendor_id', $vendor_id)->first();
        $theme = Transaction::select('themes_id')->where('vendor_id', $vendor_id)->orderByDesc('id')->first();
        $getfooterfeatures = Footerfeatures::where('vendor_id', $vendor_id)->get();
        $city = City::where('is_deleted', 2)->where('is_available', 1)->get();
        $app = AppSettings::where('vendor_id',  $vendor_id)->first();
        $getsociallinks = SocialLinks::where('vendor_id', $vendor_id)->get();
        $order = Order::where('vendor_id',$vendor_id)->get();
        $pixelsettings = Pixcel::where('vendor_id',$vendor_id)->first();
        return view('admin.otherpages.settings', compact('settingdata', 'theme', 'getfooterfeatures', 'city','app','getsociallinks','order','pixelsettings'));
    }
    public function settings_update(Request $request)
    {
        if(Auth::user()->type == 4)
        {
           $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $settingsdata = Settings::where('vendor_id', $vendor_id)->first();
        $userslug = User::where('id', $vendor_id)->first();
        if ($request->hasfile('banner')) {
            $validator = Validator::make($request->all(), [
                'banner' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
            ], [
                'banner.max' => trans('messages.image_size_message'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            if (env('Environment') == 'sendbox') {
                return $this->sendError("This operation was not performed due to demo mode");
            }
            if ($settingsdata->banner != "default-banner.png" && file_exists(storage_path('app/public/admin-assets/images/banners/' . $settingsdata->banner))) {
                @unlink(storage_path('app/public/admin-assets/images/banners/' . $settingsdata->banner));
            }
            $banner = 'banner-' . uniqid() . '.' . $request->banner->getClientOriginalExtension();
            $request->file('banner')->move(storage_path('app/public/admin-assets/images/banners/'), $banner);
            $settingsdata->banner = $banner;
        }
        if ($request->hasfile('landin_page_cover_image')) {
            $validator = Validator::make($request->all(), [
                'landin_page_cover_image' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
            ], [
                'landin_page_cover_image.max' => trans('messages.image_size_message'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            if (env('Environment') == 'sendbox') {
                return $this->sendError("This operation was not performed due to demo mode");
            }
            if ($settingsdata->cover_image != "cover.png" && file_exists(storage_path('app/public/admin-assets/images/coverimage/' . $settingsdata->cover_image))) {
                @unlink(storage_path('app/public/admin-assets/images/about/coverimage/' . $settingsdata->cover_image));
            }
            $coverimage = 'cover-' . uniqid() . '.' . $request->landin_page_cover_image->getClientOriginalExtension();
            $request->landin_page_cover_image->move(storage_path('app/public/admin-assets/images/coverimage/'), $coverimage);
            $settingsdata->cover_image = $coverimage;
        }

        if ($request->hasfile('subscribe_background')) {
            $validator = Validator::make($request->all(), [
                'subscribe_background' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
            ], [
                'subscribe_background.max' => trans('messages.image_size_message'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            if (env('Environment') == 'sendbox') {
                return $this->sendError("This operation was not performed due to demo mode");
            }
            if ($settingsdata->subscribe_background != "default_subscriber.png" && file_exists(storage_path('app/public/admin-assets/images/subscribe/' . $settingsdata->subscribe_background))) {
                @unlink(storage_path('app/public/admin-assets/images/about/subscribe/' . $settingsdata->subscribe_background));
            }
            $subscribe_img = 'subscribe_bg-' . uniqid() . '.' . $request->subscribe_background->getClientOriginalExtension();
            $request->subscribe_background->move(storage_path('app/public/admin-assets/images/subscribe/'), $subscribe_img);
            $settingsdata->subscribe_background = $subscribe_img;
        }

        if ($request->hasfile('notification_sound')) {
            if (env('Environment') == 'sendbox') {
                return $this->sendError("This operation was not performed due to demo mode");
            }
            $request->validate([
                'notification_sound' => 'mimes:mp3',

            ]);
            if (file_exists(storage_path('app/public/admin-assets/notification/' . $settingsdata->notification_sound))) {
                @unlink(storage_path('app/public/admin-assets/notification/' . $settingsdata->notification_sound));
            }
            $sound = 'audio-' . uniqid() . '.' . $request->notification_sound->getClientOriginalExtension();
            $request->notification_sound->move(storage_path('app/public/admin-assets/notification/'), $sound);
            $settingsdata->notification_sound = $sound;
        }
        if ($request->hasfile('maintenance_image')) {

            $validator = Validator::make($request->all(), [
                'maintenance_image' => 'image|max:' . helper::imagesize() . '|' . helper::imageext(),
            ], [
                "maintenance_image.image" => trans('messages.enter_image_file'),
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('error', trans('messages.image_size_message') . ' ' . helper::appdata('')->image_size . ' ' . 'MB');
            }
            if (file_exists(storage_path('app/public/admin-assets/images/index/' . $settingsdata->maintenance_image))) {
                @unlink(storage_path('app/public/admin-assets/images/index/' . $settingsdata->maintenance_image));
            }
            $maintenanceimage = 'maintenance-' . uniqid() . '.' . $request->maintenance_image->getClientOriginalExtension();
            $request->maintenance_image->move(storage_path('app/public/admin-assets/images/index/'), $maintenanceimage);
            $settingsdata->maintenance_image = $maintenanceimage;

        }
        if ($request->hasfile('store_unavailable_image')) {

            $validator = Validator::make($request->all(), [
                'store_unavailable_image' => 'image|max:' . helper::imagesize() . '|' . helper::imageext(),
            ], [
                "store_unavailable_image.image" => trans('messages.enter_image_file'),
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('error', trans('messages.image_size_message') . ' ' . helper::appdata('')->image_size . ' ' . 'MB');
            }
            if (file_exists(storage_path('app/public/admin-assets/images/index/' . $settingsdata->store_unavailable_image))) {
                @unlink(storage_path('app/public/admin-assets/images/index/' . $settingsdata->store_unavailable_image));
            }
            $maintenanceimage = 'store_unavailable-' . uniqid() . '.' . $request->store_unavailable_image->getClientOriginalExtension();
            $request->store_unavailable_image->move(storage_path('app/public/admin-assets/images/index/'), $maintenanceimage);
            $settingsdata->store_unavailable_image = $maintenanceimage;

        }
        $settingsdata->currency = $request->currency;
        $settingsdata->currency_position = $request->currency_position == 1 ? 'left' : 'right';
        $settingsdata->currency_space = $request->currency_space;
        $settingsdata->decimal_separator = $request->decimal_separator;
        $settingsdata->currency_formate = $request->currency_formate;
        $settingsdata->maintenance_mode = isset($request->maintenance_mode) ? 1 : 2;
        $settingsdata->vendor_register = isset($request->vendor_register) ? 1 : 2;
        $settingsdata->timezone = $request->timezone;
        $settingsdata->firebase = '';
        $settingsdata->copyright = $request->copyright;
        $settingsdata->website_title = $request->website_title;
        $settingsdata->description = $request->description;
        $settingsdata->time_format = $request->time_format;
        $settingsdata->date_format = $request->date_format;
        $settingsdata->order_prefix = $request->order_prefix;
        $order = Order::where('vendor_id',$vendor_id)->get();
        if($order->count() == 0 && $request->order_number_start != null && $request->order_number_start != ""){
            $settingsdata->order_number_start = $request->order_number_start;
        }
        if ($request->delivery_type != null) {
            $settingsdata->delivery_type = implode(",", $request->delivery_type);
        }
        if(Auth::user()->type == 1)
        {
            $settingsdata->image_size = $request->image_size;
        }
        if (Auth::user()->type == 2) {
            $settingsdata->checkout_login_required = isset($request->checkout_login_required) ? 1 : 2;
            $settingsdata->is_checkout_login_required = isset($request->is_checkout_login_required) ? 1 : 2;
            $settingsdata->email = $request->email;
            $settingsdata->address = $request->address;
            $settingsdata->facebook_link = $request->landing_facebook_link ?? $request->facebook_link;
            $settingsdata->twitter_link = $request->landing_twitter_link ?? $request->twitter_link;
            $settingsdata->instagram_link = $request->landing_instagram_link ?? $request->instagram_link;
            $settingsdata->linkedin_link = $request->landing_linkedin_link ?? $request->linkedin_link;
            if(!empty( $request->slug))
            {
                $userslug->slug = $request->slug;
                $userslug->update();
            }

        }
        if (!empty($request->feature_icon)) {
            foreach ($request->feature_icon as $key => $icon) {
                if (!empty($icon) && !empty($request->feature_title[$key]) && !empty($request->feature_description[$key])) {
                    $feature = new Footerfeatures;
                    $feature->vendor_id = $vendor_id ;
                    $feature->icon = $icon;
                    $feature->title = $request->feature_title[$key];
                    $feature->description = $request->feature_description[$key];
                    $feature->save();
                }
            }
        }
        if (!empty($request->edit_icon_key)) {
            foreach ($request->edit_icon_key as $key => $id) {
                $feature = Footerfeatures::find($id);
                $feature->icon = $request->edi_feature_icon[$id];
                $feature->title = $request->edi_feature_title[$id];
                $feature->description = $request->edi_feature_description[$id];
                $feature->save();
            }
        }
        $settingsdata->save();

        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function settings_updatetheme(Request $request)
    {
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $settingsdata = Settings::where('vendor_id', $vendor_id )->first();
        $settingsdata->primary_color = $request->primary_color;
        $settingsdata->secondary_color = $request->secondary_color;
        $settingsdata->template = !empty($request->template) ? $request->template : 1;
        $settingsdata->template_type = !empty($request->template_type) ? $request->template_type : 1;
        if ($request->hasfile('logo')) {
            $validator = Validator::make($request->all(), [
                'logo' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
            ], [
                'logo.max' => trans('messages.image_size_message'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            if (env('Environment') == 'sendbox') {
                return $this->sendError("This operation was not performed due to demo mode");
            }
            if ($settingsdata->logo != "default-logo.png" && $settingsdata->logo != "" && file_exists(storage_path('app/public/admin-assets/images/about/logo/' . $settingsdata->logo))) {
                unlink(storage_path('app/public/admin-assets/images/about/logo/' . $settingsdata->logo));
            }
            if (Auth::user()->type == 1) {
                if ($settingsdata->logo != "default-logo.png" && $settingsdata->logo != "" && file_exists(storage_path('app/public/admin-assets/images/about/defaultimages/' . $settingsdata->logo))) {
                    unlink(storage_path('app/public/admin-assets/images/about/defaultimages/' . $settingsdata->logo));
                }
                $logo_name = 'logo-' . uniqid() . '.' . $request->logo->getClientOriginalExtension();
                $request->file('logo')->move(storage_path('app/public/admin-assets/images/about/defaultimages/'), $logo_name);
            } else {
                if ($settingsdata->logo != "default-logo.png" && $settingsdata->logo != "" && file_exists(storage_path('app/public/admin-assets/images/about/logo/' . $settingsdata->logo))) {
                    unlink(storage_path('app/public/admin-assets/images/about/logo/' . $settingsdata->logo));
                }
                $logo_name = 'logo-' . uniqid() . '.' . $request->logo->getClientOriginalExtension();
                $request->file('logo')->move(storage_path('app/public/admin-assets/images/about/logo/'), $logo_name);
            }
            $settingsdata->logo = $logo_name;
        }
        if ($request->hasfile('favicon')) {
            $validator = Validator::make($request->all(), [
                'favicon' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
            ], [
                'favicon.max' => trans('messages.image_size_message'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            if (Auth::user()->type == 1) {
                if ($settingsdata->favicon != "defaultlogo.png" && $settingsdata->favicon != "" && file_exists(storage_path('app/public/admin-assets/images/about/defaultimages/' . $settingsdata->favicon))) {
                    unlink(storage_path('app/public/admin-assets/images/about/defaultimages/' . $settingsdata->favicon));
                }
                $favicon_name = 'favicon-' . uniqid() . '.' . $request->favicon->getClientOriginalExtension();
                $request->favicon->move(storage_path('app/public/admin-assets/images/about/defaultimages/'), $favicon_name);
            } else {
                if ($settingsdata->favicon != "defaultlogo.png" && $settingsdata->favicon != "" && file_exists(storage_path('app/public/admin-assets/images/about/favicon/' . $settingsdata->favicon))) {
                    unlink(storage_path('app/public/admin-assets/images/about/favicon/' . $settingsdata->favicon));
                }
                $favicon_name = 'favicon-' . uniqid() . '.' . $request->favicon->getClientOriginalExtension();
                $request->favicon->move(storage_path('app/public/admin-assets/images/about/favicon/'), $favicon_name);
            }

            $settingsdata->favicon = $favicon_name;
        }
        $settingsdata->save();
        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function settings_updateseo(Request $request)
    {
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }

        $settingsdata = Settings::where('vendor_id', $vendor_id)->first();

        $settingsdata->meta_title = $request->meta_title;
        $settingsdata->meta_description = $request->meta_description;
        if ($request->hasfile('og_image')) {
            $validator = Validator::make($request->all(), [
                'og_image' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
            ], [
                'og_image.max' => trans('messages.image_size_message'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            if (env('Environment') == 'sendbox') {
                return $this->sendError("This operation was not performed due to demo mode");
            }
            if (Auth::user()->type == 1) {

                if ($settingsdata->og_image != "" && file_exists(storage_path('app/public/admin-assets/images/about/defaultimages/' . $settingsdata->og_image))) {
                    unlink(storage_path('app/public/admin-assets/images/about/defaultimages/' . $settingsdata->og_image));
                }
                $image = 'og_image-' . uniqid() . '.' . $request->og_image->getClientOriginalExtension();
                $request->og_image->move(storage_path('app/public/admin-assets/images/about/defaultimages/'), $image);
            } else {
                if ($settingsdata->og_image != "" && file_exists(storage_path('app/public/admin-assets/images/about/og_image/' . $settingsdata->og_image))) {
                    unlink(storage_path('app/public/admin-assets/images/about/og_image/' . $settingsdata->og_image));
                }
                $image = 'og_image-' . uniqid() . '.' . $request->og_image->getClientOriginalExtension();
                $request->og_image->move(storage_path('app/public/admin-assets/images/about/og_image/'), $image);
            }

            $settingsdata->og_image = $image;
        }
        $settingsdata->save();
        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function landingsettings(Request $request)
    {
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $settingsdata = Settings::where('vendor_id', $vendor_id)->first();
        $settingsdata->primary_color = $request->landing_primary_color;
        $settingsdata->secondary_color = $request->landing_secondary_color;
        $settingsdata->email = $request->landing_email;
        $settingsdata->contact = $request->landing_mobile;
        $settingsdata->address = $request->landing_address;
        $settingsdata->facebook_link = $request->landing_facebook_link;
        $settingsdata->twitter_link = $request->landing_twitter_link;
        $settingsdata->instagram_link = $request->landing_instagram_link;
        $settingsdata->linkedin_link = $request->landing_linkedin_link;
        $settingsdata->landing_website_title = $request->landing_website_title;
        if ($request->hasfile('logo')) {
            $validator = Validator::make($request->all(), [
                'logo' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
            ], [
                'logo.max' => trans('messages.image_size_message'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            if (env('Environment') == 'sendbox') {
                return $this->sendError("This operation was not performed due to demo mode");
            }
            if ($settingsdata->logo != "default-logo.png" && $settingsdata->logo != "" && file_exists(storage_path('app/public/admin-assets/images/about/logo/' . $settingsdata->logo))) {
                unlink(storage_path('app/public/admin-assets/images/about/logo/' . $settingsdata->logo));
            }
            $logo_name = 'logo-' . uniqid() . '.' . $request->logo->getClientOriginalExtension();
            $request->file('logo')->move(storage_path('app/public/admin-assets/images/about/logo/'), $logo_name);
            $settingsdata->logo = $logo_name;
        }
        if ($request->hasfile('favicon')) {
            $validator = Validator::make($request->all(), [
                'favicon' => 'image|max:'.helper::imagesize().'|'.helper::imageext(),
            ], [
                'favicon.max' => trans('messages.image_size_message'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            if (env('Environment') == 'sendbox') {
                return $this->sendError("This operation was not performed due to demo mode");
            }
            if ($settingsdata->favicon != "default-favicon.png" && $settingsdata->favicon != "" && file_exists(storage_path('app/public/admin-assets/images/about/favicon/' . $settingsdata->favicon))) {
                unlink(storage_path('app/public/admin-assets/images/about/favicon/' . $settingsdata->favicon));
            }
            $favicon_name = 'favicon-' . uniqid() . '.' . $request->favicon->getClientOriginalExtension();
            $request->favicon->move(storage_path('app/public/admin-assets/images/about/favicon/'), $favicon_name);
            $settingsdata->favicon = $favicon_name;
        }

        $settingsdata->save();
        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function settings_updateanalytics(Request $request)
    {
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $settingsdata = Settings::where('vendor_id', $vendor_id)->first();
        $settingsdata->tracking_id = $request->tracking_id;
        $settingsdata->view_id = $request->view_id;
        $settingsdata->save();
        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function settings_updatecustomedomain(Request $request)
    {
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $settingsdata = Settings::where('vendor_id', $vendor_id)->first();
        $settingsdata->cname_title = $request->cname_title;
        $settingsdata->cname_text = $request->cname_text;
        $settingsdata->save();
        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function delete_feature(Request $request)
    {
        Footerfeatures::where('id', $request->id)->delete();

        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function delete_viewall_page_image(Request $request)
    {
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $settingsdata = Settings::where('vendor_id', $vendor_id)->first();
        if (!empty($settingsdata)) {
            if (!empty($settingsdata->viewallpage_banner) && file_exists(storage_path('app/public/admin-assets/images/about/viewallpage_banner/' . $settingsdata->viewallpage_banner))) {
                unlink(storage_path('app/public/admin-assets/images/about/viewallpage_banner/' . $settingsdata->viewallpage_banner));
            }
            $settingsdata->viewallpage_banner = "";
            $settingsdata->update();
            return redirect('admin/settings')->with('success', trans('messages.success'));
        }
        return redirect('admin/settings');
    }
    public function app_section(Request $request)
    {

        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }
        $appsection = AppSettings::where('vendor_id', $vendor_id)->first();

        if (empty($appsection)) {
            $appsection = new AppSettings();
        }

        $appsection->vendor_id = $vendor_id;
        $appsection->android_link = $request->android_link;
        $appsection->ios_link = $request->ios_link;
        $appsection->mobile_app_on_off = isset($request->mobile_app_on_off) ? 1 : 2;

        if ($request->has('image')) {

            $validator = Validator::make($request->all(), [
                'image' => 'image|max:' . helper::imagesize() . '|' . helper::imageext(),
            ], [
                "image.image" => trans('messages.enter_image_file'),
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('error', trans('messages.image_size_message') . ' ' . helper::appdata('')->image_size . ' ' . 'MB');
            }
            if (!empty($appsection->image)) {
                if (file_exists(storage_path('app/public/admin-assets/images/index/' .  $appsection->image))) {
                    unlink(storage_path('app/public/admin-assets/images/index/' .  $appsection->image));
                }
            }
            $image = 'appsection-' . uniqid() . "." . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(storage_path('app/public/admin-assets/images/index/'), $image);
            $appsection->image = $image;
        }
        $appsection->save();
        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function social_links_update(Request $request)
    {
        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }
        if (!empty($request->social_icon)) {
            foreach ($request->social_icon as $key => $icon) {
                if (!empty($icon) && !empty($request->social_link[$key])) {
                    $sociallink = new SocialLinks;
                    $sociallink->vendor_id = $vendor_id;
                    $sociallink->icon = $icon;
                    $sociallink->link = $request->social_link[$key];
                    $sociallink->save();
                }
            }
        }
        if (!empty($request->edit_icon_key)) {
            foreach ($request->edit_icon_key as $key => $id) {
                $sociallink = SocialLinks::find($id);
                $sociallink->icon = $request->edi_sociallink_icon[$id];
                $sociallink->link = $request->edi_sociallink_link[$id];
                $sociallink->save();
            }
        }
        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function delete_sociallinks(Request $request)
    {
        SocialLinks::where('id', $request->id)->delete();
        return redirect()->back()->with('success', trans('messages.success'));
    }
}
