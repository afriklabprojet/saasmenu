<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Table;
use App\Models\Restaurant;
use App\Models\Order;
use App\Models\TableNotification;
use App\Services\QRCodeService;
use Carbon\Carbon;

class TableQRAdminController extends Controller
{
    protected $qrService;

    public function __construct(QRCodeService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Liste des tables du restaurant
     */
    public function index(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        $tables = Table::where('restaurant_id', $restaurant->id)
            ->with(['currentOrders', 'notifications' => function($query) {
                $query->where('status', 'unread')->latest();
            }])
            ->when($request->search, function($query, $search) {
                $query->where('table_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('table_number')
            ->paginate(20);

        return view('admin.tables.index', compact('tables'));
    }

    /**
     * Créer une nouvelle table
     */
    public function store(Request $request)
    {
        $request->validate([
            'table_number' => 'required|string|max:20',
            'name' => 'nullable|string|max:100',
            'capacity' => 'required|integer|min:1|max:20',
            'location' => 'nullable|string|max:100',
        ]);

        $restaurant = auth()->user()->restaurant;

        // Vérifier que le numéro de table n'existe pas déjà
        $exists = Table::where('restaurant_id', $restaurant->id)
            ->where('table_number', $request->table_number)
            ->exists();

        if ($exists) {
            return back()->withErrors(['table_number' => 'Ce numéro de table existe déjà.']);
        }

        $table = Table::create([
            'restaurant_id' => $restaurant->id,
            'table_number' => $request->table_number,
            'name' => $request->name,
            'capacity' => $request->capacity,
            'location' => $request->location,
            'table_code' => strtoupper(Str::random(8)),
            'status' => 'active',
        ]);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table créée avec succès.');
    }

    /**
     * Afficher les détails d'une table
     */
    public function show($id)
    {
        $restaurant = auth()->user()->restaurant;

        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->with(['orders' => function($query) {
                $query->latest()->take(10);
            }, 'notifications' => function($query) {
                $query->latest()->take(20);
            }])
            ->firstOrFail();

        $todayStats = [
            'orders_count' => Order::where('table_id', $id)
                ->whereDate('created_at', today())
                ->count(),
            'total_revenue' => Order::where('table_id', $id)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('total_amount'),
            'avg_order_time' => Order::where('table_id', $id)
                ->whereDate('created_at', today())
                ->whereNotNull('completed_at')
                ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, completed_at)')),
        ];

        return view('admin.tables.show', compact('table', 'todayStats'));
    }

    /**
     * Mettre à jour une table
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'table_number' => 'required|string|max:20',
            'name' => 'nullable|string|max:100',
            'capacity' => 'required|integer|min:1|max:20',
            'location' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive,maintenance',
        ]);

        $restaurant = auth()->user()->restaurant;
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->firstOrFail();

        // Vérifier que le numéro de table n'existe pas déjà (sauf pour cette table)
        $exists = Table::where('restaurant_id', $restaurant->id)
            ->where('table_number', $request->table_number)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['table_number' => 'Ce numéro de table existe déjà.']);
        }

        $table->update($request->only([
            'table_number', 'name', 'capacity', 'location', 'status'
        ]));

        return redirect()->route('admin.tables.show', $id)
            ->with('success', 'Table mise à jour avec succès.');
    }

    /**
     * Supprimer une table
     */
    public function destroy($id)
    {
        $restaurant = auth()->user()->restaurant;
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->firstOrFail();

        // Vérifier qu'il n'y a pas de commandes en cours
        $activeOrders = Order::where('table_id', $id)
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->exists();

        if ($activeOrders) {
            return back()->withErrors(['error' => 'Impossible de supprimer une table avec des commandes en cours.']);
        }

        $table->delete();

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table supprimée avec succès.');
    }

    /**
     * Générer le QR code d'une table
     */
    public function generateQR($id)
    {
        $restaurant = auth()->user()->restaurant;
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->firstOrFail();

        $qrUrl = route('table.menu', [
            'restaurant_slug' => $restaurant->slug,
            'table_code' => $table->table_code
        ]);

        $qrCode = $this->qrService->generate($qrUrl, [
            'size' => 300,
            'margin' => 2,
        ]);

        return view('admin.tables.qr', compact('table', 'qrCode', 'qrUrl'));
    }

    /**
     * Télécharger le QR code d'une table
     */
    public function downloadQR($id)
    {
        $restaurant = auth()->user()->restaurant;
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->firstOrFail();

        $qrUrl = route('table.menu', [
            'restaurant_slug' => $restaurant->slug,
            'table_code' => $table->table_code
        ]);

        $qrImage = $this->qrService->generateImage($qrUrl, [
            'size' => 400,
            'margin' => 4,
            'format' => 'png'
        ]);

        $filename = "table-{$table->table_number}-qr.png";

        return response($qrImage)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Génération en masse des QR codes
     */
    public function bulkGenerateQR(Request $request)
    {
        $request->validate([
            'table_ids' => 'required|array',
            'table_ids.*' => 'integer',
            'format' => 'nullable|in:png,pdf,zip'
        ]);

        $restaurant = auth()->user()->restaurant;
        $format = $request->get('format', 'zip');

        $tables = Table::where('restaurant_id', $restaurant->id)
            ->whereIn('id', $request->table_ids)
            ->get();

        if ($tables->isEmpty()) {
            return back()->withErrors(['error' => 'Aucune table sélectionnée.']);
        }

        switch ($format) {
            case 'pdf':
                return $this->generateQRPDF($tables, $restaurant);
            case 'zip':
                return $this->generateQRZip($tables, $restaurant);
            default:
                return back()->withErrors(['error' => 'Format non supporté.']);
        }
    }

    /**
     * Récupérer les commandes d'une table
     */
    public function getTableOrders($id)
    {
        $restaurant = auth()->user()->restaurant;

        $orders = Order::where('table_id', $id)
            ->whereHas('table', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })
            ->with(['items.menuItem'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Commandes actives d'une table
     */
    public function getActiveOrders($id)
    {
        $restaurant = auth()->user()->restaurant;

        $orders = Order::where('table_id', $id)
            ->whereHas('table', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->with(['items.menuItem'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Statut des tables en temps réel
     */
    public function getLiveStatus()
    {
        $restaurant = auth()->user()->restaurant;

        $tables = Table::where('restaurant_id', $restaurant->id)
            ->with(['currentOrders', 'notifications' => function($query) {
                $query->where('status', 'unread');
            }])
            ->get()
            ->map(function($table) {
                return [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'status' => $table->status,
                    'active_orders' => $table->currentOrders->count(),
                    'unread_notifications' => $table->notifications->count(),
                    'last_activity' => $table->last_accessed,
                ];
            });

        return response()->json($tables);
    }

    /**
     * Mettre à jour le statut d'une table
     */
    public function updateTableStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,maintenance,occupied,free'
        ]);

        $restaurant = auth()->user()->restaurant;
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->firstOrFail();

        $table->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Statut de la table mis à jour'
        ]);
    }

    /**
     * Notifications des tables
     */
    public function getNotifications()
    {
        $restaurant = auth()->user()->restaurant;

        $notifications = TableNotification::where('restaurant_id', $restaurant->id)
            ->with(['table'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.tables.notifications', compact('notifications'));
    }

    /**
     * Résoudre une notification
     */
    public function resolveNotification($id)
    {
        $restaurant = auth()->user()->restaurant;

        $notification = TableNotification::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification résolue'
        ]);
    }

    /**
     * Notifications en temps réel
     */
    public function getLiveNotifications()
    {
        $restaurant = auth()->user()->restaurant;

        $notifications = TableNotification::where('restaurant_id', $restaurant->id)
            ->where('status', 'unread')
            ->with(['table'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'table_number' => $notification->table->table_number,
                    'priority' => $notification->priority,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Marquer notification comme vue
     */
    public function markNotificationSeen($id)
    {
        $restaurant = auth()->user()->restaurant;

        TableNotification::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->update(['status' => 'seen']);

        return response()->json(['success' => true]);
    }

    /**
     * Commandes en temps réel d'une table
     */
    public function getLiveOrders($table_id)
    {
        $restaurant = auth()->user()->restaurant;

        $orders = Order::where('table_id', $table_id)
            ->whereHas('table', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })
            ->whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])
            ->with(['items.menuItem'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Mettre à jour le statut d'une commande
     */
    public function updateOrderStatus(Request $request, $order_id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,served,completed,cancelled'
        ]);

        $restaurant = auth()->user()->restaurant;

        $order = Order::where('id', $order_id)
            ->whereHas('table', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })
            ->firstOrFail();

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Statut de la commande mis à jour'
        ]);
    }

    /**
     * Analytiques des tables
     */
    public function getAnalytics(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        $period = $request->get('period', 'today');
        $dateFilter = match($period) {
            'today' => [today(), today()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [today(), today()]
        };

        $analytics = [
            'total_orders' => Order::whereHas('table', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })
            ->whereBetween('created_at', $dateFilter)
            ->count(),

            'total_revenue' => Order::whereHas('table', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })
            ->whereBetween('created_at', $dateFilter)
            ->where('status', 'completed')
            ->sum('total_amount'),

            'avg_order_value' => Order::whereHas('table', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })
            ->whereBetween('created_at', $dateFilter)
            ->where('status', 'completed')
            ->avg('total_amount'),

            'table_utilization' => $this->getTableUtilization($restaurant->id, $dateFilter),
        ];

        return response()->json($analytics);
    }

    /**
     * Revenus par table
     */
    public function getRevenueByTable(Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        $period = $request->get('period', 'today');

        $dateFilter = match($period) {
            'today' => [today(), today()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [today(), today()]
        };

        $revenueByTable = Table::where('restaurant_id', $restaurant->id)
            ->with(['orders' => function($query) use ($dateFilter) {
                $query->whereBetween('created_at', $dateFilter)
                    ->where('status', 'completed');
            }])
            ->get()
            ->map(function($table) {
                return [
                    'table_number' => $table->table_number,
                    'orders_count' => $table->orders->count(),
                    'total_revenue' => $table->orders->sum('total_amount'),
                    'avg_order_value' => $table->orders->avg('total_amount') ?? 0,
                ];
            })
            ->sortByDesc('total_revenue')
            ->values();

        return response()->json($revenueByTable);
    }

    /**
     * Calculer le taux d'utilisation des tables
     */
    private function getTableUtilization($restaurant_id, $dateFilter)
    {
        $totalTables = Table::where('restaurant_id', $restaurant_id)->count();
        $tablesWithOrders = Table::where('restaurant_id', $restaurant_id)
            ->whereHas('orders', function($query) use ($dateFilter) {
                $query->whereBetween('created_at', $dateFilter);
            })
            ->count();

        return $totalTables > 0 ? ($tablesWithOrders / $totalTables) * 100 : 0;
    }

    /**
     * Générer PDF avec tous les QR codes
     */
    public function downloadAllQRPDF()
    {
        $restaurant = auth()->user()->restaurant;

        $tables = Table::where('restaurant_id', $restaurant->id)
            ->where('status', 'active')
            ->get();

        if ($tables->isEmpty()) {
            return back()->with('error', 'Aucune table active à exporter.');
        }

        $result = $this->qrService->generatePDF($tables);

        if ($result['success']) {
            return response()->download(
                storage_path('app/' . $result['file_path']),
                $result['filename']
            )->deleteFileAfterSend(true);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Générer ZIP avec tous les QR codes
     */
    public function downloadAllQRZip()
    {
        $restaurant = auth()->user()->restaurant;

        $tables = Table::where('restaurant_id', $restaurant->id)
            ->where('status', 'active')
            ->get();

        if ($tables->isEmpty()) {
            return back()->with('error', 'Aucune table active à exporter.');
        }

        $result = $this->qrService->generateZip($tables);

        if ($result['success']) {
            return response()->download(
                storage_path('app/' . $result['file_path']),
                'qr-tables-' . now()->format('Y-m-d') . '.zip'
            )->deleteFileAfterSend(true);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Personnaliser le QR code d'une table
     */
    public function customizeQR(Request $request, $id)
    {
        $request->validate([
            'qr_color_fg' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
            'qr_color_bg' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
            'qr_use_logo' => 'nullable|boolean',
            'qr_size' => 'nullable|integer|min:200|max:800',
        ]);

        $restaurant = auth()->user()->restaurant;
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->firstOrFail();

        $table->update([
            'qr_color_fg' => $request->qr_color_fg ?? '#000000',
            'qr_color_bg' => $request->qr_color_bg ?? '#FFFFFF',
            'qr_use_logo' => $request->qr_use_logo ?? true,
            'qr_size' => $request->qr_size ?? 300,
        ]);

        return back()->with('success', 'Personnalisation du QR code sauvegardée.');
    }

    /**
     * Télécharger le QR code personnalisé d'une table
     */
    public function downloadCustomQR($id)
    {
        $restaurant = auth()->user()->restaurant;
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->firstOrFail();

        $qrUrl = route('table.menu', [
            'restaurant_slug' => $restaurant->slug,
            'table_code' => $table->table_code
        ]);

        $customOptions = [
            'size' => $table->qr_size ?? 300,
            'format' => 'png',
            'foreground_color' => $table->qr_color_fg ?? '#000000',
            'background_color' => $table->qr_color_bg ?? '#FFFFFF',
        ];

        // Ajouter le logo si activé
        if ($table->qr_use_logo && $restaurant->logo_path && Storage::exists($restaurant->logo_path)) {
            $customOptions['logo_path'] = Storage::path($restaurant->logo_path);
        }

        $qrImage = $this->qrService->generateCustom($qrUrl, $customOptions);

        // Convertir base64 en fichier
        $imageData = explode(',', $qrImage)[1];
        $decodedImage = base64_decode($imageData);

        return response($decodedImage)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="table-' . $table->table_number . '-qr.png"');
    }

    /**
     * Statistiques de scan pour une table
     */
    public function scanStats($id, Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->firstOrFail();

        $period = $request->get('period', 'week');
        $stats = $this->qrService->getScanStats($id, $period);

        if ($request->wantsJson()) {
            return response()->json($stats);
        }

        return view('admin.tables.scan-stats', compact('table', 'stats', 'period'));
    }

    /**
     * Statistiques globales de scan du restaurant
     */
    public function restaurantScanStats(Request $request)
    {
        $restaurant = auth()->user()->restaurant;
        $period = $request->get('period', 'week');

        $stats = $this->qrService->getRestaurantScanStats($restaurant->id, $period);

        if ($request->wantsJson()) {
            return response()->json($stats);
        }

        return view('admin.tables.restaurant-scan-stats', compact('stats', 'period'));
    }

    /**
     * Prévisualiser le QR code personnalisé
     */
    public function previewCustomQR(Request $request, $id)
    {
        $restaurant = auth()->user()->restaurant;
        $table = Table::where('restaurant_id', $restaurant->id)
            ->where('id', $id)
            ->firstOrFail();

        $qrUrl = route('table.menu', [
            'restaurant_slug' => $restaurant->slug,
            'table_code' => $table->table_code
        ]);

        $customOptions = [
            'size' => $request->get('size', 300),
            'format' => 'png',
            'foreground_color' => $request->get('fg_color', '#000000'),
            'background_color' => $request->get('bg_color', '#FFFFFF'),
        ];

        if ($request->get('use_logo') && $restaurant->logo_path && Storage::exists($restaurant->logo_path)) {
            $customOptions['logo_path'] = Storage::path($restaurant->logo_path);
        }

        $qrImage = $this->qrService->generateCustom($qrUrl, $customOptions);

        return response()->json([
            'qr_code' => $qrImage,
            'table_number' => $table->table_number,
            'url' => $qrUrl
        ]);
    }
}

