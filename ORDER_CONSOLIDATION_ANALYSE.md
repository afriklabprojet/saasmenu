# 📋 Analyse de Consolidation - OrderController

**Date**: 11 novembre 2025  
**Objectif**: Consolider et enrichir OrderController en intégrant la logique métier de HomeController tout en préservant l'architecture moderne

---

## 📊 Vue d'ensemble

### OrderController existant (443 lignes)
**Localisation**: `app/Http/Controllers/web/OrderController.php`

**Architecture actuelle**:
- ✅ Architecture moderne avec méthodes privées réutilisables
- ✅ Transactions DB avec rollback
- ✅ Audit logs via AuditService
- ✅ Validation des requêtes
- ⚠️ Vendor lookup basé uniquement sur session
- ⚠️ Tax et delivery charge avec logic placeholder
- ⚠️ Pas de support gateways externes (PayTab, Mollie, Xendit, etc.)
- ⚠️ Pas de gestion coupons/promos
- ⚠️ Pas de WhatsApp/notifications
- ⚠️ Pas de buy now flow

**Méthodes existantes**:
1. `checkout()` - Affiche page checkout (ligne 33)
2. `create()` - Crée commande (ligne 86)
3. `success()` - Page succès commande (ligne 149)
4. `track()` - Suivi commande (ligne 169)
5. `cancel()` - Annulation commande (ligne 207)
6. Méthodes privées helpers (getCartItems, createOrder, createOrderDetails, clearCart, calculateSubTotal, calculateTax, calculateDeliveryCharge, generateOrderNumber, updateStock, canCancelOrder, restoreStock)

---

## 🔍 HomeController - Méthodes à intégrer

### 1️⃣ checkout() - Ligne 733-874
**Fonctionnalités à intégrer**:
- ✅ Support buy_now flag (déjà ajouté via trait)
- ✅ Vendor lookup via VendorDataTrait (déjà ajouté)
- ✅ Validation stock/min/max order pour items et variants
- ✅ Calcul taxes agrégées par item avec helper::gettax() et helper::taxRate()
- ✅ Load delivery areas, coupons, table QRs (déjà ajouté)
- ❌ Validation complète stock avant checkout (manquante)

**Code clé à intégrer**:
```php
// Validation stock et min/max order pour variants
foreach ($cartdata as $cart) {
    if ($cart->variants_id != "" && $cart->variants_id != null) {
        $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                    ->where('variants_id', $cart->variants_id)
                    ->where('user_id', Auth::user()->id)
                    ->first();
        $variant = Variants::where('id', $cart->variants_id)->first();
        
        if ($variant->stock_management == 1) {
            // Min order check
            if ($variant->min_order != null && $cartqty->totalqty < $variant->min_order) {
                return redirect()->back()->with('error', trans('messages.min_qty_message') . $variant->min_order);
            }
            // Max order check
            if ($variant->max_order != null && $variant->max_order < $cartqty->totalqty) {
                return redirect()->back()->with('error', trans('messages.max_qty_message') . $variant->max_order);
            }
            // Stock check
            if ($cart->qty > $variant->qty) {
                return redirect()->back()->with('error', trans('messages.cart_qty_msg'));
            }
        }
    }
    // Same for items without variants
}

// Tax aggregation
$itemtaxes = [];
$producttax = 0;
$tax_name = [];
$tax_price = [];

foreach ($cartdata as $cart) {
    $taxlist = helper::gettax($cart->tax);
    if (!empty($taxlist)) {
        foreach ($taxlist as $tax) {
            if (!empty($tax)) {
                $producttax = helper::taxRate($tax->tax, $cart->price, $cart->qty, $tax->type);
                // Aggregate by tax name
                if (!in_array($tax->name, $tax_name)) {
                    $tax_name[] = $tax->name;
                    // Calculate price based on type (1=fixed, 2=percentage)
                    if ($tax->type == 1) {
                        $price = $tax->tax * $cart->qty;
                    }
                    if ($tax->type == 2) {
                        $price = ($tax->tax / 100) * ($cart->price);
                    }
                    $tax_price[] = $price;
                } else {
                    // Add to existing tax
                    $tax_price[array_search($tax->name, $tax_name)] += $price;
                }
            }
        }
    }
}
$taxArr['tax'] = $tax_name;
$taxArr['rate'] = $tax_price;
```

---

### 2️⃣ applypromocode() - Ligne 876-919
**Fonctionnalités**:
- Validation code promo (empty, exists, dates, limit)
- Stockage en session: offer_amount, offer_code, offer_type
- Vérification montant minimum (sub_total >= promocode.price)
- Gestion timezone vendor

**À ajouter comme nouvelle méthode**:
```php
public function applyPromocode(Request $request)
{
    if ($request->promocode == "") {
        return response()->json(["status" => 0, "message" => trans('messages.enter_promocode')], 200);
    }
    
    $promocode = Coupons::where('code', $request->promocode)
                        ->where('vendor_id', $request->vendor_id)
                        ->first();
    
    if (@helper::appdata($request->vendor_id)->timezone != "") {
        date_default_timezone_set(helper::appdata($request->vendor_id)->timezone);
    }
    
    $current_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime($promocode->active_from));
    $end_date = date('Y-m-d', strtotime($promocode->active_to));
    
    if ($start_date <= $current_date && $current_date <= $end_date) {
        if ($promocode->limit > 0) {
            if ($request->sub_total < @$promocode->price) {
                return response()->json(["status" => 0, "message" => trans('messages.not_eligible')], 200);
            }
            session([
                'offer_amount' => @$promocode->price,
                'offer_code' => @$promocode->code,
                'offer_type' => 'promocode',
            ]);
            return response()->json(['status' => 1, 'message' => trans('messages.promocode_applied'), 'data' => $promocode], 200);
        } else {
            return response()->json(['status' => 0, 'message' => trans('messages.limit_over')], 200);
        }
    } else {
        return response()->json(['status' => 0, 'message' => trans('messages.promocode_expired')], 200);
    }
}
```

---

### 3️⃣ removepromocode() - Ligne 920-927
**Fonctionnalités**:
- Suppression session vars: offer_amount, offer_code, offer_type

**À ajouter comme nouvelle méthode**:
```php
public function removePromocode(Request $request)
{
    session()->forget(['offer_amount', 'offer_code', 'offer_type']);
    return response()->json(['status' => 1, 'message' => trans('messages.promocode_removed')], 200);
}
```

---

### 4️⃣ timeslot() - Ligne 928-1004
**Fonctionnalités**:
- Génération slots horaires basée sur Timing model
- Gestion pauses (break_start/break_end)
- Filtre slots passés si date = aujourd'hui
- Support interval_type (1=minutes, 2=heures)

**À ajouter comme nouvelle méthode** + helpers firsthalf/secondhalf

---

### 5️⃣ paymentmethod() - Ligne 1040-1250
**Fonctionnalités critiques**:
- **Validation finale avant paiement** (stock, min/max order)
- Session storage de toutes les données commande
- Calcul taxes détaillées
- Calcul delivery charge
- Routage vers différents gateways (Stripe, Razorpay, Flutterwave, Mercado, PayStack, PayTab, Mollie, Xendit, etc.)
- Support COD (Cash on Delivery)
- Gestion buy_now flag

**Session variables stockées**:
```php
Session::put([
    'slug' => $storeinfo->slug,
    'vendor_id' => $vendor_id,
    'payment_type' => $request->payment_type,
    'customer_email' => $request->customer_email,
    'customer_name' => $request->customer_name,
    'customer_mobile' => $request->mobile,
    'grand_total' => $request->grand_total,
    'delivery_charge' => $request->delivery_charge,
    'address' => $request->address,
    'building' => $request->building,
    'landmark' => $request->landmark,
    'postal_code' => $request->postal_code,
    'discount_amount' => session()->get('offer_amount'),
    'offer_type' => session()->get('offer_type'),
    'sub_total' => $request->sub_total,
    'tax' => $tax_total,
    'tax_name' => implode("|", array_unique($tax_name)),
    'delivery_time' => $request->delivery_time,
    'delivery_date' => $request->delivery_date,
    'delivery_area' => $request->delivery_area,
    'couponcode' => session()->get('offer_code'),
    'order_type' => $request->order_type,
    'notes' => $request->notes,
    'table' => $request->table,
    'buynow' => $buynow,
]);
```

**Routage gateways**:
- Type 1: RazorPay
- Type 2: Stripe
- Type 3: COD (Cash on delivery)
- Type 4: Flutterwave
- Type 6: Mercado Pago
- Type 7: PayStack
- Type 8: Paytm
- Type 9: MyFatoorah
- Type 10: toyyibpay
- Type 11: Phonepe
- Type 12: PayTab
- Type 13: Mollie
- Type 14: Khalti
- Type 15: Xendit

---

### 6️⃣ ordercreate() - Ligne 1368-1443
**Fonctionnalités critiques**:
- **Gateway callbacks handling** (PayTab, Mollie, Xendit status check)
- Extraction payment_id depuis différents paramètres (paymentId, payment_id, transaction_id, transactionId, etc.)
- **Appel helper::createorder()** avec toutes les session vars
- Redirection vers mercadoorder view

**Logic gateway callbacks**:
```php
// PayTab (type 12)
if (Session::get('payment_type') == "12") {
    $checkstatus = app('App\Http\Controllers\addons\PayTabController')
                    ->checkpaymentstatus(Session::get('tran_ref'), Session::get('vendor_id'));
    if ($checkstatus == "A") {
        $paymentid = Session::get('tran_ref');
    } else {
        return redirect(Session::get('failureurl'))
                ->with('error', session()->get('paytab_response'));
    }
}

// Mollie (type 13)
if (Session::get('payment_type') == "13") {
    $checkstatus = app('App\Http\Controllers\addons\MollieController')
                    ->checkpaymentstatus(Session::get('tran_ref'), Session::get('vendor_id'));
    // Same pattern
}

// Xendit (type 15)
if (session()->get('payment_type') == "15") {
    $checkstatus = app('App\Http\Controllers\addons\XenditController')
                    ->checkpaymentstatus(session()->get('tran_ref'), Session::get('vendor_id'));
    // Check if PAID
}
```

**Appel helper::createorder()**:
```php
$orderresponse = helper::createorder(
    Session::get('vendor_id'), 
    $user_id, 
    $session_id, 
    Session::get('payment_type'), 
    $paymentid, 
    Session::get('customer_email'), 
    Session::get('customer_name'), 
    Session::get('customer_mobile'), 
    Session::get('stripeToken'), 
    Session::get('grand_total'), 
    Session::get('delivery_charge'), 
    Session::get('address'), 
    Session::get('building'), 
    Session::get('landmark'), 
    Session::get('postal_code'), 
    Session::get('discount_amount'), 
    Session::get('offer_type'), 
    Session::get('sub_total'), 
    Session::get('tax'), 
    Session::get('tax_name'), 
    Session::get('delivery_time'), 
    Session::get('delivery_date'), 
    Session::get('delivery_area'), 
    Session::get('couponcode'), 
    Session::get('order_type'), 
    Session::get('notes'), 
    Session::get('table'),
    '',
    Session::get('buynow')
);
```

---

### 7️⃣ ordersuccess() - Ligne 1252-1266
**Fonctionnalités**:
- Vendor lookup (VendorDataTrait pattern)
- **WhatsApp message** via helper::whatsappmessage()
- Load order_number
- View: front.ordersuccess avec whmessage

**À intégrer dans success()**:
```php
public function success(Request $request)
{
    $orderNumber = $request->route('order_number') ?: $request->order_number;
    $vdata = $this->getVendorId($request) ?: Session::get('restaurant_id');
    $storeinfo = $this->getStoreInfo($request);

    $order = Order::where('order_number', $orderNumber)
                 ->where('vendor_id', $vdata)
                 ->with('orderdetails.item')
                 ->first();

    if (!$order) {
        return redirect('/')->with('error', 'Commande introuvable');
    }

    // WhatsApp message generation
    $whmessage = helper::whatsappmessage($orderNumber, $vdata, $storeinfo);

    $settingdata = helper::appdata($vdata);

    return view('front.ordersuccess', compact('order', 'settingdata', 'vdata', 'storeinfo', 'order_number', 'whmessage'));
}
```

---

### 8️⃣ trackorder() - Ligne 1268-1315
**Fonctionnalités**:
- Vendor lookup
- selectRaw DATE_FORMAT (déjà sécurisé)
- Load order with tableqr relation
- Load OrderDetails
- Build summery array

**Déjà implémenté en partie, à enrichir**:
```php
// Add tableqr relation
$orderdata = Order::with('tableqr')
                  ->where('order_number', $request->ordernumber)
                  ->where('vendor_id', $vdata)
                  ->first();

// Build summery array pour la vue
$summery = [
    'id' => $status->id,
    'tax' => $status->tax,
    'tax_name' => $status->tax_name,
    'discount_amount' => $status->discount_amount,
    'order_number' => $status->order_number,
    'created_at' => $status->date,
    'delivery_charge' => $status->delivery_charge,
    // ... autres champs
];

return view('front.track-order', compact('vdata', 'storeinfo', 'orderdata', 'summery', 'orderdetails'));
```

---

### 9️⃣ cancelorder() - Ligne 1317-1366
**Fonctionnalités critiques**:
- Validation status (already_accepted, already_rejected, already_delivered)
- **CustomStatus lookup** avec order_type et type=4 (cancelled)
- **Stock restoration** pour items et variants
- **Email notification** via helper::cancel_order()
- **Push notification** via helper::push_notification()
- Config email via helper::emailconfigration()

**À intégrer dans cancel()**:
```php
public function cancel(Request $request, $orderNumber)
{
    $vdata = $this->getVendorId($request) ?: Session::get('restaurant_id');
    $storeinfo = $this->getStoreInfo($request);
    
    $orderdata = Order::where('order_number', $orderNumber)
                     ->where('vendor_id', $vdata)
                     ->first();

    if (!$orderdata) {
        return redirect()->back()->with('error', trans('messages.order_not_found'));
    }

    // Check current status
    if ($orderdata->status_type == 2) {
        return redirect()->back()->with('error', trans('messages.already_accepted'));
    } else if ($orderdata->status_type == 4) {
        return redirect()->back()->with('error', trans('messages.already_rejected'));
    } else if ($orderdata->status_type == 3) {
        return redirect()->back()->with('error', trans('messages.already_delivered'));
    }

    // Get cancelled status from CustomStatus
    $defaultsatus = CustomStatus::where('vendor_id', $storeinfo->id)
                                ->where('order_type', $orderdata->order_type)
                                ->where('type', 4)
                                ->where('is_available', 1)
                                ->where('is_deleted', 2)
                                ->first();

    if (empty($defaultsatus)) {
        return redirect()->back()->with('error', trans('messages.wrong'));
    }

    try {
        DB::beginTransaction();

        // Update order status
        $orderdata->status_type = $defaultsatus->type;
        $orderdata->status = $defaultsatus->id;
        $orderdata->update();

        // Restore stock
        $orderdetails = OrderDetails::where('order_id', $orderdata->id)->get();
        foreach ($orderdetails as $order) {
            if ($order->variants_id != null && $order->variants_id != "") {
                $item = Variants::where('id', $order->variants_id)
                               ->where('item_id', $order->item_id)
                               ->first();
            } else {
                $item = Item::where('id', $order->item_id)
                           ->where('vendor_id', $storeinfo->id)
                           ->first();
            }
            $item->qty = $item->qty + $order->qty;
            $item->update();
        }

        // Notifications
        $title = helper::gettype($orderdata->status, $orderdata->status_type, $orderdata->order_type, $storeinfo->id)->name;
        $message_text = 'Order ' . $orderdata->order_number . ' has been cancelled by ' . $orderdata->user_name;
        
        // Email config and send
        $emaildata = helper::emailconfigration($storeinfo->id);
        Config::set('mail', $emaildata);
        helper::cancel_order($storeinfo->email, $storeinfo->name, $title, $message_text, $orderdata);
        
        // Push notification
        $vendorData = User::select('id', 'name', 'slug', 'email', 'mobile', 'token')
                         ->where('id', $orderdata->vendor_id)
                         ->first();
        $body = "#" . $orderNumber . " has been cancelled";
        helper::push_notification($vendorData->token, $title, $body, "order", $orderdata->id);

        // Audit log
        AuditService::logAdminAction(
            'CANCEL_ORDER',
            'Order',
            [
                'reason' => 'Customer cancellation',
                'title' => $title,
                'notification_sent' => true
            ],
            $orderdata->id
        );

        DB::commit();

        return redirect()->back()->with('success', trans('messages.success'));

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', trans('messages.wrong'));
    }
}
```

---

## 🎯 Stratégie de Consolidation

### Option choisie: **ENRICH** (Enrichir l'existant)

**Rationale**:
- ✅ OrderController existant a une architecture moderne et propre
- ✅ Transactions, validations, audit logs déjà en place
- ✅ Méthodes privées réutilisables bien structurées
- ⚠️ Manque logique métier critique (gateways, coupons, notifications, stock validation complète)
- ⚠️ Tax et delivery charge sont des placeholders

**Actions**:
1. ✅ **VendorDataTrait** déjà intégré
2. ✅ **checkout()** enrichi avec coupons, deliveryAreas, tableQrs, taxArr
3. ⚠️ **checkout()** - Ajouter validation stock/min/max complète
4. ❌ **Ajouter méthodes nouvelles**: applyPromocode(), removePromocode(), timeslot()
5. ❌ **Ajouter paymentmethod()** avec toutes ses validations et routing gateways
6. ❌ **create()** - Remplacer par logic qui utilise helper::createorder() ou intégrer la logic complète
7. ✅ **success()** - Enrichir avec WhatsApp message
8. ✅ **track()** - Enrichir avec tableqr relation et summery array
9. ❌ **cancel()** - Enrichir avec CustomStatus, email, push notifications
10. ❌ **calculateTax()** - Implémenter logic réelle (agrégation par tax name)
11. ❌ **calculateDeliveryCharge()** - Implémenter logic réelle (DeliveryArea lookup)

---

## 📝 Plan d'implémentation

### Phase 1: Validation stock dans checkout() ✅ PRIORITÉ HAUTE
```php
private function validateCartStock($cartdata, $vdata)
{
    foreach ($cartdata as $cart) {
        if ($cart->variants_id != "" && $cart->variants_id != null) {
            // Aggregate cart qty for this variant
            if (Auth::check()) {
                $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                            ->where('variants_id', $cart->variants_id)
                            ->where('user_id', Auth::id())
                            ->first();
            } else {
                $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                            ->where('variants_id', $cart->variants_id)
                            ->where('session_id', Session::getId())
                            ->first();
            }
            
            $variant = Variants::where('id', $cart->variants_id)->first();
            $item_name = Item::select('item_name')->where('id', $cart->item_id)->first();
            
            if ($variant->stock_management == 1) {
                // Min order validation
                if ($variant->min_order != null && $variant->min_order != "" && $variant->min_order != 0) {
                    if ($cartqty->totalqty < $variant->min_order) {
                        throw new \Exception(trans('messages.min_qty_message') . $variant->min_order . " " . ($item_name->item_name));
                    }
                }
                
                // Max order validation
                if ($variant->max_order != null && $variant->max_order != "" && $variant->max_order != 0) {
                    if ($variant->max_order < $cartqty->totalqty) {
                        throw new \Exception(trans('messages.max_qty_message') . $variant->max_order . ' ' . ($item_name->item_name));
                    }
                }
                
                // Stock validation
                if ($cart->qty > $variant->qty) {
                    throw new \Exception(trans('messages.cart_qty_msg') . ' ' . trans('labels.out_of_stock_msg') . ' ' . $item_name->item_name . '(' . $variant->name . ')');
                }
            }
        } else {
            // Same logic for items without variants
            $item = Item::where('id', $cart->item_id)->first();
            
            if (Auth::check()) {
                $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                            ->where('item_id', $cart->item_id)
                            ->where('user_id', Auth::id())
                            ->first();
            } else {
                $cartqty = Cart::selectRaw('SUM(qty) as totalqty')
                            ->where('item_id', $cart->item_id)
                            ->where('session_id', Session::getId())
                            ->first();
            }
            
            if ($item->stock_management == 1) {
                if ($item->min_order != null && $item->min_order != "" && $item->min_order != 0) {
                    if ($cartqty->totalqty < $item->min_order) {
                        throw new \Exception(trans('messages.min_qty_message') . $item->min_order . ' ' . ($item->item_name));
                    }
                }
                
                if ($item->max_order != null && $item->max_order != "" && $item->max_order != 0) {
                    if ($item->max_order < $cartqty->totalqty) {
                        throw new \Exception(trans('messages.max_qty_message') . $item->max_order . ' ' . ($item->item_name));
                    }
                }
                
                if ($cart->qty > $item->qty) {
                    throw new \Exception(trans('messages.cart_qty_msg') . ' ' . trans('labels.out_of_stock_msg') . ' ' . $item->item_name);
                }
            }
        }
    }
}
```

### Phase 2: Méthodes coupons ✅ PRIORITÉ HAUTE
- Ajouter `applyPromocode(Request $request)`
- Ajouter `removePromocode(Request $request)`

### Phase 3: Méthode timeslot() ✅ PRIORITÉ MOYENNE
- Ajouter `timeslot(Request $request)`
- Ajouter helpers `firsthalf()` et `secondhalf()`
- Import model `Timing`

### Phase 4: Méthode paymentmethod() ✅ PRIORITÉ CRITIQUE
- Ajouter `paymentmethod(Request $request)`
- Validation stock/min/max complète (même logic que checkout)
- Calcul taxes détaillées
- Calcul delivery charge
- Session storage de toutes les données
- Routing vers gateways (Stripe, Razorpay, etc.)
- Support COD

### Phase 5: Enrichir create() ou remplacer par ordercreate() ✅ PRIORITÉ CRITIQUE
- Option A: Garder create() et enrichir avec gateway callbacks + helper::createorder
- Option B: Renommer create() en _createSimple() et créer nouvelle create() basée sur ordercreate()
- **Recommandation: Option A** pour préserver compatibilité

### Phase 6: Enrichir success() ✅ PRIORITÉ HAUTE
- Ajouter WhatsApp message via helper::whatsappmessage()
- Passer whmessage à la vue

### Phase 7: Enrichir track() ✅ PRIORITÉ MOYENNE
- Ajouter relation tableqr
- Builder summery array
- Passer à la vue

### Phase 8: Enrichir cancel() ✅ PRIORITÉ HAUTE
- CustomStatus lookup
- Email notification (helper::cancel_order)
- Push notification (helper::push_notification)
- Config email dynamique

### Phase 9: Implémenter calculateTax() ✅ PRIORITÉ HAUTE
- Récupérer toutes les taxes via helper::gettax()
- Calculer par item avec helper::taxRate()
- Agréger par tax name
- Retourner total

### Phase 10: Implémenter calculateDeliveryCharge() ✅ PRIORITÉ MOYENNE
- Lookup DeliveryArea by id
- Retourner charge ou 0

---

## 🔒 Sécurité & Qualité

### Fixes déjà appliqués
- ✅ selectRaw au lieu de DB::raw pour DATE_FORMAT
- ✅ VendorDataTrait pour lookup vendor sécurisé
- ✅ Transactions DB avec rollback
- ✅ Audit logs

### À préserver
- ✅ Validation des requêtes
- ✅ Try-catch avec DB rollback
- ✅ AuditService logs
- ✅ Pas de DB::raw concatenation

---

## 📦 Imports nécessaires

**Ajouts requis**:
```php
use App\Models\Variants;
use App\Models\Timing;
use App\Models\Tax;
use Illuminate\Support\Facades\Mail;
use DateTime;
use DateInterval;
```

---

## ✅ Checklist finale

### Méthodes à ajouter
- [ ] applyPromocode()
- [ ] removePromocode()
- [ ] timeslot()
- [ ] firsthalf() helper
- [ ] secondhalf() helper
- [ ] paymentmethod()

### Méthodes à enrichir
- [ ] checkout() - Ajouter validateCartStock()
- [ ] create() - Ajouter gateway callbacks + helper::createorder logic
- [ ] success() - Ajouter WhatsApp message
- [ ] track() - Ajouter tableqr + summery
- [ ] cancel() - Ajouter CustomStatus + notifications

### Méthodes privées à implémenter
- [ ] calculateTax() - Logic réelle avec agrégation
- [ ] calculateDeliveryCharge() - Logic réelle avec DeliveryArea
- [ ] validateCartStock() - Nouvelle méthode

---

## 🎬 Commit Strategy

1. **Commit 1**: Ajouter validateCartStock() et enrichir checkout()
2. **Commit 2**: Ajouter méthodes coupons (applyPromocode, removePromocode)
3. **Commit 3**: Ajouter timeslot() + helpers
4. **Commit 4**: Ajouter paymentmethod() avec routing gateways
5. **Commit 5**: Enrichir create() avec gateway callbacks
6. **Commit 6**: Enrichir success(), track(), cancel()
7. **Commit 7**: Implémenter calculateTax() et calculateDeliveryCharge()
8. **Commit 8**: Tests et validation

---

## 📊 Métriques

- **Lignes OrderController avant**: 443
- **Lignes estimées après**: ~1200 (avec toutes les méthodes)
- **Méthodes avant**: 11
- **Méthodes après**: ~20
- **Nouvelles dépendances**: Variants, Timing, Tax models
- **Gateways supportés**: 15 (Stripe, Razorpay, COD, Flutterwave, Mercado, PayStack, Paytm, MyFatoorah, toyyibpay, Phonepe, PayTab, Mollie, Khalti, Xendit)

---

**Date de création**: 11 novembre 2025  
**Auteur**: GitHub Copilot  
**Status**: ✅ Analyse complète - Prêt pour implémentation
