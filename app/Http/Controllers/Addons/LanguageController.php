<?php
namespace App\Http\Controllers\Addons;
use App\Http\Controllers\Controller;
use App\Models\Languages;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
class LanguageController extends Controller
{
    public function index(Request $request)
    {
        $getlanguages = Languages::get();
        if ($request->code == "") {
            foreach ($getlanguages as $firstlang) {
                $currantLang = Languages::where('code', $firstlang->code)->first();
                break;
            }
        } else {
            $currantLang = Languages::where('code', $request->code)->first();
        }
        if ($request->has('lang')) {
            if ($request->lang != "" && $request->lang != null) {
                $settingdata = Settings::where('vendor_id', 1)->first();
                $settingdata->default_language = $request->lang;
                $settingdata->update();
            }
        }
        if (empty($currantLang)) {
            $dir = base_path() . '/resources/lang/' . 'en';
        } else {
            $dir = base_path() . '/resources/lang/' . $currantLang->code;
        }
        if (!is_dir($dir)) {
            $dir = base_path() . '/resources/lang/en';
        }

        // Safely load language files with fallback to English
        $labelsFile = $dir . '/labels.json';
        $messagesFile = $dir . '/messages.json';
        $landingFile = $dir . '/landing.json';

        // Fallback to English files if current language files don't exist
        $englishDir = base_path() . '/resources/lang/en';
        if (!file_exists($labelsFile)) {
            $labelsFile = $englishDir . '/labels.json';
        }
        if (!file_exists($messagesFile)) {
            $messagesFile = $englishDir . '/messages.json';
        }
        if (!file_exists($landingFile)) {
            $landingFile = $englishDir . '/landing.json';
        }

        // Decode JSON files with null fallback
        $arrLabel = file_exists($labelsFile) ? json_decode(file_get_contents($labelsFile), true) : [];
        $arrMessage = file_exists($messagesFile) ? json_decode(file_get_contents($messagesFile), true) : [];
        $arrLanding = file_exists($landingFile) ? json_decode(file_get_contents($landingFile), true) : [];

        // Ensure arrays are not null
        $arrLabel = $arrLabel ?: [];
        $arrMessage = $arrMessage ?: [];
        $arrLanding = $arrLanding ?: [];
        return view('admin.language.index', compact('getlanguages', 'currantLang', 'arrLabel', 'arrMessage', 'arrLanding'));
    }
    public function add()
    {
        return view('admin.language.add');
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'layout' => 'required',
            'name' => 'required_with:code',
            'image.*' => 'mimes:jpeg,png,jpg',
        ], [
            "code.required" => trans('messages.language_required'),
            "layout.required" => trans('messages.layout_required'),
            "name.required_with" => trans('messages.wrong'),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {
            $path = base_path('resources/lang/' . $request->code);
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            File::copyDirectory(base_path() . '/resources/lang/en', base_path() . '/resources/lang/' . $request->code);
            $language = new Languages();
            $language->code = $request->code;
            $language->name = $request->name;
            $language->layout = $request->layout;
            if ($request->has('image')) {
                $flagimage = 'flag-' . uniqid() . "." . $request->file('image')->getClientOriginalExtension();
                $request->file('image')->move(storage_path('app/public/admin-assets/images/language/'), $flagimage);
                $language->image = $flagimage;
            }
            $language->is_available = 1;
            $language->save();
            return redirect('admin/language-settings')->with('success', trans('messages.success'));
        }
    }
}
