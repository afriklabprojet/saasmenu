<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Corriger les références de colonnes dans le service BI
 */
class FixBusinessIntelligenceService extends Command
{
    protected $signature = 'fix:bi-service';
    protected $description = 'Corriger les références de colonnes dans BusinessIntelligenceService';

    public function handle(): int
    {
        $filePath = app_path('Services/BusinessIntelligenceService.php');
        
        if (!File::exists($filePath)) {
            $this->error('BusinessIntelligenceService.php non trouvé');
            return Command::FAILURE;
        }

        $content = File::get($filePath);
        
        // Corrections des colonnes - plus spécifiques
        $corrections = [
            // Colonnes de la table orders
            "'order_status', 'completed'" => "'status', 5", // status 5 = delivered
            "'order_status', 'cancelled'" => "'status', 3", // status 3 = cancelled by admin
            "'order_status'" => "'status'",
            "->sum('total')" => "->sum('grand_total')",
            "->avg('total')" => "->avg('grand_total')",
            "SUM(total)" => "SUM(grand_total)",
            "->sum('order_total')" => "->sum('grand_total')",
            "order_total" => "grand_total",
            // Relations clients
            "customer_id" => "user_id", // Dans orders, customer = user_id
            // Relations produits
            "products.product_name" => "order_details.product_name", // Utiliser directement order_details
            "products.name" => "order_details.product_name", // Au cas où
            // Relations catégories
            "categories.category_name" => "categories.name",
            // Prix dans order_details
            "order_details.price" => "order_details.product_price",
            // Correction pour les sous-totaux
            "subtotal" => "sub_total"
        ];
        
        $originalContent = $content;
        
        foreach ($corrections as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        if ($content !== $originalContent) {
            File::put($filePath, $content);
            $this->info('✅ BusinessIntelligenceService.php corrigé avec succès');
            
            // Afficher les changements effectués
            $this->info('Corrections appliquées:');
            foreach ($corrections as $search => $replace) {
                if (strpos($originalContent, $search) !== false) {
                    $this->line("  • $search → $replace");
                }
            }
        } else {
            $this->info('Aucune correction nécessaire');
        }
        
        return Command::SUCCESS;
    }
}