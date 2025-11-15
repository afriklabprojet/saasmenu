<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\UpdateOrderStatusRequest;
use App\Http\Requests\Orders\StoreCustomerInfoRequest;
use App\Http\Requests\Orders\StoreVendorNoteRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\CustomStatus;
use App\Models\Item;
use App\Models\Variants;
use App\Helpers\helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * OrdersApiController - RESTful API pour la gestion des commandes
 * 
 * Remplace les routes CRUDdy par des endpoints RESTful conformes
 */
class OrdersApiController extends Controller
{
    /**
     * Mettre à jour le statut d'une commande
     * 
     * PATCH /admin/api/orders/{order}/status
     * 
     * @param UpdateOrderStatusRequest $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        try {
            $vendorId = $this->getVendorId();
            
            // Vérifier que la commande appartient au vendor
            if ($order->vendor_id != $vendorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this order'
                ], 403);
            }

            $statusType = $request->status_type;
            $statusId = $request->status_id;

            // Valider le statut
            $customStatus = CustomStatus::where('vendor_id', $order->vendor_id)
                ->where('order_type', $order->order_type)
                ->where('type', $statusType)
                ->where('id', $statusId)
                ->where('is_available', 1)
                ->where('is_deleted', 2)
                ->first();

            if (!$customStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status provided'
                ], 422);
            }

            // Préparer le message de notification
            $notificationData = $this->prepareNotificationData($order, $statusType);

            // Envoyer email de notification
            $this->sendStatusEmail($order, $notificationData);

            // Envoyer notification WhatsApp
            $this->sendWhatsAppNotification($order, $statusType, $vendorId);

            // Mettre à jour le statut de paiement pour Cash on Delivery
            if ($order->payment_type == 6 && $statusType == 3) {
                $order->payment_status = 2;
            }

            // Mettre à jour le statut de la commande
            $order->status = $customStatus->id;
            $order->status_type = $customStatus->type;
            $order->save();

            // Si annulation, remettre les produits en stock
            if ($statusType == 4) {
                $this->restoreStock($order);
            }

            Log::info('Order status updated successfully', [
                'order_id' => $order->id,
                'old_status' => $order->status,
                'new_status' => $customStatus->id,
                'vendor_id' => $vendorId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => new OrderResource($order->fresh())
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update order status', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mettre à jour les informations client d'une commande
     * 
     * PATCH /admin/api/orders/{order}/customer-info
     * 
     * @param StoreCustomerInfoRequest $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCustomerInfo(StoreCustomerInfoRequest $request, Order $order)
    {
        try {
            $vendorId = $this->getVendorId();

            // Vérifier que la commande appartient au vendor
            if ($order->vendor_id != $vendorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this order'
                ], 403);
            }

            $editType = $request->edit_type;

            // Mettre à jour selon le type d'édition
            if ($editType === 'customer_info') {
                $order->customer_name = $request->customer_name;
                $order->mobile = $request->customer_mobile;
                $order->customer_email = $request->customer_email;
            }

            if ($editType === 'delivery_info') {
                $order->address = $request->customer_address;
                $order->building = $request->customer_building;
                $order->landmark = $request->customer_landmark;
                $order->pincode = $request->customer_pincode;
            }

            $order->save();

            Log::info('Order customer info updated', [
                'order_id' => $order->id,
                'edit_type' => $editType,
                'vendor_id' => $vendorId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer information updated successfully',
                'data' => new OrderResource($order->fresh())
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update customer info', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer information',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Ajouter/Mettre à jour la note vendor d'une commande
     * 
     * PATCH /admin/api/orders/{order}/vendor-note
     * 
     * @param StoreVendorNoteRequest $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVendorNote(StoreVendorNoteRequest $request, Order $order)
    {
        try {
            $vendorId = $this->getVendorId();

            // Vérifier que la commande appartient au vendor
            if ($order->vendor_id != $vendorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this order'
                ], 403);
            }

            $order->vendor_note = $request->vendor_note;
            $order->save();

            Log::info('Order vendor note updated', [
                'order_id' => $order->id,
                'vendor_id' => $vendorId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vendor note updated successfully',
                'data' => new OrderResource($order->fresh())
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update vendor note', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update vendor note',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtenir l'ID du vendor courant
     * 
     * @return int
     */
    protected function getVendorId(): int
    {
        if (Auth::user()->type == 4) {
            return Auth::user()->vendor_id;
        }
        
        return Auth::user()->id;
    }

    /**
     * Préparer les données de notification selon le type de statut
     * 
     * @param Order $order
     * @param int $statusType
     * @return array
     */
    protected function prepareNotificationData(Order $order, int $statusType): array
    {
        $title = helper::gettype($order->status, $statusType, $order->order_type, $order->vendor_id)->name ?? '';

        switch ($statusType) {
            case 2: // Accepted
                return [
                    'title' => $title,
                    'message' => "Your Order {$order->order_number} has been accepted by Admin"
                ];
            
            case 3: // Delivered
                return [
                    'title' => $title,
                    'message' => "Your Order {$order->order_number} has been successfully delivered."
                ];
            
            case 4: // Cancelled
                return [
                    'title' => $title,
                    'message' => "Order {$order->order_number} has been cancelled by Admin."
                ];
            
            default:
                return [
                    'title' => $title,
                    'message' => "Order {$order->order_number} status has been updated."
                ];
        }
    }

    /**
     * Envoyer email de notification de statut
     * 
     * @param Order $order
     * @param array $data
     * @return void
     */
    protected function sendStatusEmail(Order $order, array $data): void
    {
        try {
            $emailData = helper::emailconfigration($order->vendor_id);
            Config::set('mail', $emailData);
            
            helper::order_status_email(
                $order->customer_email,
                $order->customer_name,
                $data['title'],
                $data['message'],
                $order->vendor_id
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send status email', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer notification WhatsApp
     * 
     * @param Order $order
     * @param int $statusType
     * @param int $vendorId
     * @return void
     */
    protected function sendWhatsAppNotification(Order $order, int $statusType, int $vendorId): void
    {
        try {
            // TODO: Implémenter l'envoi WhatsApp via WhatsAppBusinessService
            // Cette logique sera ajoutée avec le service WhatsApp
            
            Log::info('WhatsApp notification queued', [
                'order_id' => $order->id,
                'status_type' => $statusType
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to send WhatsApp notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Restaurer le stock après annulation de commande
     * 
     * @param Order $order
     * @return void
     */
    protected function restoreStock(Order $order): void
    {
        $orderDetails = OrderDetails::where('order_id', $order->id)->get();

        foreach ($orderDetails as $detail) {
            if ($detail->variants_id != null && $detail->variants_id != "") {
                $item = Variants::where('id', $detail->variants_id)
                    ->where('item_id', $detail->item_id)
                    ->first();
            } else {
                $item = Item::where('id', $detail->item_id)
                    ->where('vendor_id', $order->vendor_id)
                    ->first();
            }

            if ($item) {
                $item->qty = $item->qty + $detail->qty;
                $item->save();

                Log::info('Stock restored', [
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'quantity' => $detail->qty
                ]);
            }
        }
    }
}
