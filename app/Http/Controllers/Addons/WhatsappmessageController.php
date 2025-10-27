<?php



namespace App\Http\Controllers\Addons;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\Settings;

use Illuminate\Support\Facades\Auth;

class WhatsappmessageController extends Controller

{
    public function whatsapp_update(Request $request)
    {
        if(Auth::user()->type == 4)
        {
            $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $request->validate([
            'item_message' => 'required',
            'whatsapp_message' => 'required',
            'contact' => 'required',
        ], [
            'item_message.required' => trans('messages.item_message_required'),
            'whatsapp_message.required' => trans('messages.whatsapp_message_required'),
            'contact.required' => trans('messages.contact_required'),
        ]);
        $settingsdata = Settings::where('vendor_id',$vendor_id)->first();
        $settingsdata->item_message = $request->item_message;
        $settingsdata->whatsapp_message = $request->whatsapp_message;
        $settingsdata->contact = $request->contact;
        $settingsdata->whatsapp_on_off =  isset($request->whatsapp_on_off) ? 1 : 2;
        $settingsdata->whatsapp_chat_on_off =  isset($request->whatsapp_chat_on_off) ? 1 : 2;
        $settingsdata->whatsapp_chat_position = $request->whatsapp_chat_position;
        $settingsdata->save();
        return redirect()->back()->with('success', trans('messages.success'));
    }
}
