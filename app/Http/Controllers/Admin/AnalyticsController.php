<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Contrôleur pour les analytics et rapports du restaurant
 */
class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Obtenir l'ID du vendor
     */
    private function getVendorId()
    {
        if (Auth::user()->type == 4) {
            return Auth::user()->vendor_id;
        }
        return Auth::user()->id;
    }

    /**
     * Dashboard analytics complet
     */
    public function dashboard(Request $request)
    {
        $vendor_id = $this->getVendorId();
        $period = $request->get('period', 'today');

        $analytics = $this->analyticsService->getCompleteDashboard($vendor_id, $period);

        if ($request->ajax()) {
            return response()->json($analytics);
        }

        return view('admin.analytics.dashboard', compact('analytics', 'period'));
    }

    /**
     * API: Statistiques du chiffre d'affaires
     */
    public function revenue(Request $request)
    {
        $vendor_id = $this->getVendorId();
        $period = $request->get('period', 'today');

        $stats = $this->analyticsService->getRevenueStats($vendor_id, $period);

        return response()->json($stats);
    }

    /**
     * API: Plats les plus vendus
     */
    public function topSelling(Request $request)
    {
        $vendor_id = $this->getVendorId();
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 'month');

        $topItems = $this->analyticsService->getTopSellingItems($vendor_id, $limit, $period);

        return response()->json($topItems);
    }

    /**
     * API: Heures de pointe
     */
    public function peakHours(Request $request)
    {
        $vendor_id = $this->getVendorId();
        $period = $request->get('period', 'week');

        $peakHours = $this->analyticsService->getPeakHours($vendor_id, $period);

        return response()->json($peakHours);
    }

    /**
     * API: Analytics clients
     */
    public function customers(Request $request)
    {
        $vendor_id = $this->getVendorId();
        $period = $request->get('period', 'month');

        $customerAnalytics = $this->analyticsService->getCustomerAnalytics($vendor_id, $period);

        return response()->json($customerAnalytics);
    }

    /**
     * API: Performance des catégories
     */
    public function categories(Request $request)
    {
        $vendor_id = $this->getVendorId();
        $period = $request->get('period', 'month');

        $categoryStats = $this->analyticsService->getCategoryPerformance($vendor_id, $period);

        return response()->json($categoryStats);
    }

    /**
     * API: Comparaison entre périodes
     */
    public function compare(Request $request)
    {
        $vendor_id = $this->getVendorId();

        // Périodes par défaut : ce mois vs mois précédent
        $currentStart = $request->get('current_start', Carbon::now()->startOfMonth()->toDateString());
        $currentEnd = $request->get('current_end', Carbon::now()->endOfMonth()->toDateString());
        $previousStart = $request->get('previous_start', Carbon::now()->subMonth()->startOfMonth()->toDateString());
        $previousEnd = $request->get('previous_end', Carbon::now()->subMonth()->endOfMonth()->toDateString());

        $comparison = $this->analyticsService->comparePeriods(
            $vendor_id,
            $currentStart,
            $currentEnd,
            $previousStart,
            $previousEnd
        );

        return response()->json($comparison);
    }

    /**
     * Export des données analytics en CSV
     */
    public function export(Request $request)
    {
        $vendor_id = $this->getVendorId();
        $type = $request->get('type', 'revenue'); // revenue, items, customers
        $period = $request->get('period', 'month');

        $data = [];
        $filename = "analytics_{$type}_" . date('Y-m-d') . '.csv';

        switch ($type) {
            case 'revenue':
                $stats = $this->analyticsService->getRevenueStats($vendor_id, $period);
                $data = [
                    ['Métrique', 'Valeur'],
                    ['CA Actuel', $stats['current']['revenue']],
                    ['Commandes Actuelles', $stats['current']['orders']],
                    ['Panier Moyen', $stats['current']['avg_order']],
                    ['Variation CA (%)', $stats['change']['revenue']],
                    ['Variation Commandes (%)', $stats['change']['orders']],
                ];
                break;

            case 'items':
                $items = $this->analyticsService->getTopSellingItems($vendor_id, 50, $period);
                $data[] = ['Produit', 'Quantité Vendue', 'Chiffre d\'Affaires', 'Nombre de Commandes', 'Prix Moyen'];
                foreach ($items as $item) {
                    $data[] = [
                        $item['item_name'],
                        $item['total_quantity'],
                        $item['total_revenue'],
                        $item['order_count'],
                        $item['avg_price'],
                    ];
                }
                break;

            case 'customers':
                $customerAnalytics = $this->analyticsService->getCustomerAnalytics($vendor_id, $period);
                $data[] = ['Client', 'Mobile', 'Commandes', 'Total Dépensé', 'Panier Moyen'];
                foreach ($customerAnalytics['top_customers'] as $customer) {
                    $data[] = [
                        $customer['name'],
                        $customer['mobile'],
                        $customer['order_count'],
                        $customer['total_spent'],
                        $customer['avg_order'],
                    ];
                }
                break;
        }

        // Générer le CSV
        $handle = fopen('php://output', 'w');
        ob_start();

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        $csv = ob_get_clean();

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
