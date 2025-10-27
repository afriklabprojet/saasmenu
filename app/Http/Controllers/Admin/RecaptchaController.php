<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;
use Illuminate\Support\Facades\Auth;

class RecaptchaController extends Controller
{
    public function updaterecaptcha(Request $request)
    {
        if(Auth::user()->type == 4)
        {
           $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $request->validate([
            'recaptcha_version' => 'required',
            'google_recaptcha_site_key' => 'required',
            'google_recaptcha_secret_key' => 'required',
            'cookie_text' => 'required',
            'cookie_button_text' => 'required',
        ]);
        $settingsdata = Settings::where('vendor_id', $vendor_id)->first();
       
        $settingsdata->recaptcha_version = $request->recaptcha_version;
        $settingsdata->google_recaptcha_site_key = $request->google_recaptcha_site_key;
        $settingsdata->google_recaptcha_secret_key = $request->google_recaptcha_secret_key;
        $settingsdata->score_threshold = $request->score_threshold;
        $settingsdata->save();
        return redirect()->back()->with('success', trans('messages.success'));
    }
}
