<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\helper;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\CustomStatus;
use App\Models\Item;
use App\Models\Variants;
use App\Models\User;
use App\Services\WhatsAppTemplateService;
use App\Services\WhatsAppBusinessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getorders = Order::where('vendor_id', $vendor_id);
        if ($request->has('status') && $request->status != "") {
            if ($request->status == "processing") {
                $getorders = $getorders->whereIn('status_type', array(1,2));
            }
            if ($request->status == "cancelled") {
                $getorders = $getorders->where('status_type', 4);
            }

            if ($request->status == "delivered") {
                $getorders = $getorders->where('status_type', 3);
            }
        }
        $totalorders = Order::where('vendor_id',$vendor_id)->count();
        $totalprocessing = Order::whereIn('status_type', array(1,2))->where('vendor_id',$vendor_id)->count();
        $totalrevenue = Order::where('vendor_id',$vendor_id)->where('status_type', 3)->where('payment_status',2)->sum('grand_total');
        $totalcompleted = Order::where('status_type', 3)->where('vendor_id',$vendor_id)->count();
        $totalcancelled = Order::where('status_type', 4)->where('vendor_id',$vendor_id)->count();
        if (!empty($request->startdate) && !empty($request->enddate)) {
            $totalorders = Order::where('vendor_id',$vendor_id)->whereBetween('created_at', [$request->startdate, $request->enddate])->count();
            $getorders = $getorders->whereBetween('created_at', [$request->startdate, $request->enddate]);
            $totalprocessing = Order::whereIn('status_type', array(1,2))->where('vendor_id',$vendor_id)->whereBetween('created_at', [$request->startdate, $request->enddate])->count();
            $totalrevenue = Order::where('status_type', 3)->where('vendor_id',$vendor_id)->where('payment_status',2)->whereBetween('created_at', [$request->startdate, $request->enddate])->sum('grand_total');
            $totalcompleted = Order::where('status_type', 3)->where('vendor_id',$vendor_id)->whereBetween('created_at', [$request->startdate, $request->enddate])->count();
            $totalcancelled = Order::where('status_type',4)->where('vendor_id',$vendor_id)->whereBetween('created_at', [$request->startdate, $request->enddate])->count();
        }
        $getorders = $getorders->orderByDesc('id')->get();
        return view('admin.orders.index', compact('getorders', 'totalorders', 'totalprocessing', 'totalcompleted', 'totalcancelled', 'totalrevenue'));
    }
    public function update(Request $request)
    {

        try {
            if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
            $orderdata = Order::where('id', $request->id)->where('vendor_id',$vendor_id)->first();
            $orderdetail = OrderDetails::where('order_id', $orderdata->id)->get();
            if (empty($orderdata) || !in_array($request->type, [2, 3, 4])) {
                abort(404);
            }
            $title = "";
            $message_text = "";
            if ($request->type == "2") {
                $title = @helper::gettype($request->status, $request->type, $orderdata->order_type, $orderdata->vendor_id)->name;
                $message_text = 'Your Order ' . $orderdata->order_number . ' has been accepted by Admin';
            }
            if ($request->type == "3") {
                $title = @helper::gettype($request->status, $request->type, $orderdata->order_type, $orderdata->vendor_id)->name;
                $message_text = 'Your Order ' . $orderdata->order_number . ' has been successfully delivered.';
            }
            if ($request->type == "4") {
                $title = @helper::gettype($request->status, $request->type, $orderdata->order_type, $orderdata->vendor_id)->name;
                $message_text = 'Order ' . $orderdata->order_number . ' has been cancelled by Admin.';
            }

            $defaultsatus = CustomStatus::where('vendor_id', $orderdata->vendor_id )->where('order_type', $orderdata->order_type)->where('type',$request->type)->where('id',$request->status)->where('is_available', 1)->where('is_deleted', 2)->first();

                if (empty($defaultsatus) && $defaultsatus == null) {
                    return redirect()->back()->with('error', trans('messages.wrong'));
                }
                else {
                    $emaildata = helper::emailconfigration($orderdata->vendor_id);
                    Config::set('mail', $emaildata);
                    helper::order_status_email($orderdata->customer_email, $orderdata->customer_name, $title, $message_text, $orderdata->vendor_id);

                    // Envoyer notification WhatsApp selon le type de changement
                    $this->sendWhatsAppNotification($orderdata, $request->type, $vendor_id);

                    if ($orderdata->payment_type == 6 && $request->type == 3) {
                        $orderdata->payment_status = 2;
                    }

                    $orderdata->status = $defaultsatus->id;
                    $orderdata->status_type = $defaultsatus->type;
                    $orderdata->save();

                    if ($request->type == "4") {
                        foreach ($orderdetail as $order) {
                            if ($order->variants_id != null && $order->variants_id != "") {
                                $item = Variants::where('id', $order->variants_id)->where('item_id', $order->item_id)->first();
                            } else {
                                $item = Item::where('id', $order->item_id)->where('vendor_id', $orderdata->vendor_id)->first();
                            }
                            $item->qty = $item->qty + $order->qty;
                            $item->update();
                        }
                    }
                    return redirect()->back()->with('success', trans('messages.success'));
                }


        } catch (\Throwable $th) {
            return redirect()->back()->with('error', trans('messages.wrong'));
        }


    }
    public function invoice(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getorderdata = Order::with('tableqr')->where('order_number', $request->order_number)->where('vendor_id',$vendor_id)->first();
        if (empty($getorderdata)) {
            abort(404);
        }
        $ordersdetails = OrderDetails::where('order_id', $getorderdata->id)->get();
        return view('admin.orders.invoice', compact('getorderdata', 'ordersdetails'));
    }
    public function print(Request $request)
    {
        if(Auth::user()->type == 4)
        {
             $vendor_id = Auth::user()->vendor_id;
        }else{
            $vendor_id = Auth::user()->id;
        }
        $getorderdata = Order::where('order_number', $request->order_number)->where('vendor_id',$vendor_id)->first();

        if (empty($getorderdata)) {
            abort(404);
        }
        $ordersdetails = OrderDetails::where('order_id', $getorderdata->id)->get();
        return view('admin.orders.print', compact('getorderdata', 'ordersdetails'));
    }
    public function customerinfo(Request $request)
    {
        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }

        $customerinfo = Order::where('order_number', $request->order_id)->where('vendor_id', $vendor_id)->first();

        if ($request->edit_type == "customer_info") {
            $customerinfo->customer_name = $request->customer_name;
            $customerinfo->mobile = $request->customer_mobile;
            $customerinfo->customer_email = $request->customer_email;
        }
        if ($request->edit_type == "delivery_info") {
            $customerinfo->address = $request->customer_address;
            $customerinfo->building = $request->customer_building;
            $customerinfo->landmark = $request->customer_landmark;
            $customerinfo->pincode = $request->customer_pincode;
        }
        $customerinfo->update();
        return redirect()->back()->with('success', trans('messages.success'));
    }

    public function vendor_note(Request $request)
    {

        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }

        $updatenote = Order::where('order_number', $request->order_id)->where('vendor_id', $vendor_id)->first();

        $updatenote->vendor_note = $request->vendor_note;
        $updatenote->update();
        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function payment_status(Request $request)
    {
        if ($request->ramin_amount > 0) {
            return redirect()->back()->with('error', trans('messages.amount_validation_msg'));
        }

        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }
        $order = Order::where('order_number', $request->booking_number)->where('vendor_id', $vendor_id)->first();
        $order->payment_status = 2;
        $order->update();
        return redirect()->back()->with('success', trans('messages.success'));
    }
    public function generatepdf(Request $request)
    {
        if (Auth::user()->type == 4) {
            $vendor_id = Auth::user()->vendor_id;
        } else {
            $vendor_id = Auth::user()->id;
        }
        $getorderdata = Order::where('order_number', $request->order_number)->where('vendor_id', $vendor_id)->first();
        $ordersdetails = OrderDetails::where('order_id', $getorderdata->id)->get();
        $pdf = PDF::loadView('admin.orders.invoicepdf', ['getorderdata' => $getorderdata, 'ordersdetails' => $ordersdetails]);
        return $pdf->download('orderinvoice.pdf');
    }

    /**
     * Envoie une notification WhatsApp au client selon le changement de statut
     * NOUVEAU : Envoi automatique via WhatsApp Business API
     *
     * @param Order $order Commande concernée
     * @param int $status_type Type de statut (2=Acceptée, 3=Livrée, 4=Annulée)
     * @param int $vendor_id ID du restaurant
     * @return void
     */
    private function sendWhatsAppNotification($order, $status_type, $vendor_id)
    {
        try {
            // Récupérer les données du restaurant
            $vendordata = User::find($vendor_id);

            if (!$vendordata) {
                Log::warning('Restaurant not found for WhatsApp notification', [
                    'vendor_id' => $vendor_id,
                    'order_number' => $order->order_number
                ]);
                return;
            }

            // Vérifier si le numéro client est valide
            if (empty($order->mobile)) {
                Log::warning('No mobile number for WhatsApp notification', [
                    'order_number' => $order->order_number
                ]);
                return;
            }

            $message = null;
            $template_name = null;

            // Générer le message selon le type de changement de statut
            switch ($status_type) {
                case 2: // Commande acceptée/en traitement
                    if (config('whatsapp-templates.auto_notifications.order_accepted', true)) {
                        $message = WhatsAppTemplateService::generateConfirmationMessage(
                            $order->order_number,
                            $vendor_id,
                            $vendordata
                        );
                        $template_name = 'order_confirmed';
                    }
                    break;

                case 3: // Commande livrée/complétée
                    if (config('whatsapp-templates.auto_notifications.order_delivered', true)) {
                        $message = WhatsAppTemplateService::generateReadyMessage(
                            $order->order_number,
                            $vendor_id,
                            $vendordata
                        );
                        $template_name = 'order_ready';
                    }
                    break;

                case 4: // Commande annulée
                    if (config('whatsapp-templates.auto_notifications.order_cancelled', true)) {
                        $message = $this->generateCancellationMessage($order, $vendordata);
                        $template_name = 'order_cancelled';
                    }
                    break;
            }

            // Si un message a été généré, l'envoyer automatiquement
            if ($message) {
                // Décoder le message URL-encodé pour l'envoi
                $decodedMessage = urldecode($message);

                // Initialiser le service WhatsApp Business
                $whatsappService = new WhatsAppBusinessService();

                // Context pour le logging
                $context = [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'template' => $template_name,
                    'status_type' => $status_type,
                    'vendor_id' => $vendor_id,
                    'vendor_name' => $vendordata->name
                ];

                // Envoyer le message WhatsApp
                $result = $whatsappService->sendTextMessage(
                    $order->mobile,
                    $decodedMessage,
                    $context
                );

                // Logger le résultat
                if ($result['success']) {
                    Log::info('WhatsApp notification sent successfully', [
                        'order_number' => $order->order_number,
                        'template' => $template_name,
                        'customer' => $order->customer_name,
                        'mobile' => $order->mobile,
                        'message_id' => $result['context']['message_id'] ?? null
                    ]);
                } else {
                    Log::warning('WhatsApp notification failed to send', [
                        'order_number' => $order->order_number,
                        'template' => $template_name,
                        'error' => $result['status'],
                        'mobile' => $order->mobile
                    ]);
                }

                return $result;
            }

        } catch (\Exception $e) {
            Log::error('WhatsApp notification exception', [
                'error' => $e->getMessage(),
                'order_number' => $order->order_number,
                'status_type' => $status_type,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Génère un message d'annulation personnalisé
     *
     * @param Order $order
     * @param User $vendordata
     * @return string
     */
    private function generateCancellationMessage($order, $vendordata)
    {
        $message = "❌ *Commande Annulée* ❌\n\n";
        $message .= "Bonjour *{$order->customer_name}*,\n\n";
        $message .= "Nous sommes désolés mais votre commande *#{$order->order_number}* a été annulée.\n\n";
        $message .= "💰 *Montant* : " . helper::currency_formate($order->grand_total, $vendordata->id) . "\n\n";

        if ($order->payment_status == 2) {
            $message .= "💳 Votre paiement sera remboursé sous 3-5 jours ouvrés.\n\n";
        }

        $message .= "📞 *Besoin d'aide ?*\n";
        $message .= "Contactez-nous : {$vendordata->mobile}\n\n";
        $message .= "Nous espérons vous revoir bientôt ! 🙏\n\n";
        $message .= "_Envoyé par {$vendordata->name}_";

        return str_replace("\n", "%0a", $message);
    }
}
