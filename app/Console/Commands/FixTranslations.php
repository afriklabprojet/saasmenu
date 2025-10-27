<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:fix-french';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige automatiquement les traductions anglaises dans les fichiers français';

    /**
     * Mappings de traductions à corriger
     */
    private $translations = [
        // Textes de base
        'Add new category' => 'Ajouter nouvelle catégorie',
        'Add new Item' => 'Ajouter nouvel article',
        'Add to Cart' => 'Ajouter au panier',
        'View my Order' => 'Voir ma commande',
        'Assign Plan Manually' => 'Assigner plan manuellement',
        'Today Store Is Closed !!' => 'Le magasin est fermé aujourd\'hui !!',
        'Slot Time Interval' => 'Intervalle de temps de créneau',
        'Provide Only delivery' => 'Livraison uniquement',
        'VAT percentage( calculated into item price )' => 'Pourcentage TVA (calculé dans le prix de l\'article)',
        'Read Before Sending Custom Domain Request' => 'Lire avant d\'envoyer la demande de domaine personnalisé',

        // Paramètres et configuration
        'Payment Settings' => 'Paramètres de paiement',
        'General Settings' => 'Paramètres généraux',
        'Basic Information' => 'Informations de base',
        'Store Settings' => 'Paramètres du magasin',
        'Delivery Area' => 'Zone de livraison',
        'Working Hours' => 'Heures de travail',
        'Social Links' => 'Liens sociaux',

        // Interface utilisateur
        'Login' => 'Connexion',
        'Register' => 'S\'inscrire',
        'Logout' => 'Déconnexion',
        'Profile' => 'Profil',
        'Edit Profile' => 'Modifier le profil',
        'Change Password' => 'Changer le mot de passe',
        'Dashboard' => 'Tableau de Bord',

        // Gestion des commandes
        'New Order' => 'Nouvelle commande',
        'Order Details' => 'Détails de la commande',
        'Order Status' => 'Statut de la commande',
        'Order History' => 'Historique des commandes',
        'Pending Orders' => 'Commandes en attente',
        'Completed Orders' => 'Commandes terminées',
        'Cancelled Orders' => 'Commandes annulées',
        'Processing Orders' => 'Commandes en cours',

        // Produits et catégories
        'Add Product' => 'Ajouter un produit',
        'Edit Product' => 'Modifier le produit',
        'Product Details' => 'Détails du produit',
        'Category Management' => 'Gestion des catégories',
        'Product Management' => 'Gestion des produits',
        'Stock Management' => 'Gestion des stocks',

        // Utilisateurs et rôles
        'User Management' => 'Gestion des utilisateurs',
        'Customer Management' => 'Gestion des clients',
        'Staff Management' => 'Gestion du personnel',
        'Role Management' => 'Gestion des rôles',
        'Permissions' => 'Autorisations',

        // Rapports et analyses
        'Sales Report' => 'Rapport des ventes',
        'Revenue Report' => 'Rapport des revenus',
        'Customer Report' => 'Rapport clients',
        'Product Report' => 'Rapport produits',
        'Analytics' => 'Analyses',
        'Statistics' => 'Statistiques',

        // Navigation et actions
        'Save Changes' => 'Sauvegarder les modifications',
        'Save' => 'Sauvegarder',
        'Update' => 'Mettre à jour',
        'Delete' => 'Supprimer',
        'Edit' => 'Modifier',
        'View' => 'Voir',
        'Add' => 'Ajouter',
        'Cancel' => 'Annuler',
        'Back' => 'Retour',
        'Next' => 'Suivant',
        'Previous' => 'Précédent',
        'Submit' => 'Soumettre',
        'Search' => 'Rechercher',
        'Filter' => 'Filtrer',
        'Export' => 'Exporter',
        'Import' => 'Importer',
        'Print' => 'Imprimer',

        // Messages système
        'Success' => 'Succès',
        'Error' => 'Erreur',
        'Warning' => 'Attention',
        'Information' => 'Information',
        'Confirmation' => 'Confirmation',
        'Please wait' => 'Veuillez patienter',
        'Loading' => 'Chargement',
        'No data found' => 'Aucune donnée trouvée',
        'No results' => 'Aucun résultat',

        // Formulaires
        'Required field' => 'Champ obligatoire',
        'Optional' => 'Optionnel',
        'Select option' => 'Sélectionner une option',
        'Choose file' => 'Choisir un fichier',
        'Upload' => 'Télécharger',
        'Browse' => 'Parcourir',

        // Temps et dates
        'Today' => 'Aujourd\'hui',
        'Yesterday' => 'Hier',
        'Tomorrow' => 'Demain',
        'This Week' => 'Cette semaine',
        'Last Week' => 'Semaine dernière',
        'This Month' => 'Ce mois',
        'Last Month' => 'Mois dernier',
        'This Year' => 'Cette année',
        'Last Year' => 'Année dernière',

        // Status
        'Active' => 'Actif',
        'Inactive' => 'Inactif',
        'Pending' => 'En attente',
        'Approved' => 'Approuvé',
        'Rejected' => 'Rejeté',
        'Completed' => 'Terminé',
        'Processing' => 'En cours',
        'Cancelled' => 'Annulé',
        'Delivered' => 'Livré',
        'Confirmed' => 'Confirmé',
        'Draft' => 'Brouillon',
        'Published' => 'Publié'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔧 Correction des traductions françaises");
        $this->info("=======================================");

        $labelsPath = resource_path('lang/fr/labels.php');

        if (!File::exists($labelsPath)) {
            $this->error("Fichier labels.php non trouvé: $labelsPath");
            return 1;
        }

        $content = File::get($labelsPath);
        $originalContent = $content;
        $replacements = 0;

        foreach ($this->translations as $english => $french) {
            // Recherche des patterns exacts
            $patterns = [
                "\"$english\"",
                "'$english'",
                "=>\"$english\"",
                "=>'$english'"
            ];

            foreach ($patterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $frenchPattern = str_replace($english, $french, $pattern);
                    $content = str_replace($pattern, $frenchPattern, $content);
                    $replacements++;
                    $this->line("  ✅ '$english' → '$french'");
                }
            }
        }

        // Corrections spécifiques pour les clés de traduction
        $specificReplacements = [
            '"users"=>"Users"' => '"users"=>"Utilisateurs"',
            '"customers"=>"Customers"' => '"customers"=>"Clients"',
            '"staff"=>"Staff"' => '"staff"=>"Personnel"',
            '"vendors"=>"Vendors"' => '"vendors"=>"Vendeurs"',
            '"products"=>"Products"' => '"products"=>"Produits"',
            '"categories"=>"Categories"' => '"categories"=>"Catégories"',
            '"orders"=>"Orders"' => '"orders"=>"Commandes"',
            '"reports"=>"Reports"' => '"reports"=>"Rapports"',
            '"analytics"=>"Analytics"' => '"analytics"=>"Analyses"',
            '"notifications"=>"Notifications"' => '"notifications"=>"Notifications"',
            '"settings"=>"Settings"' => '"settings"=>"Paramètres"',
            '"profile"=>"Profile"' => '"profile"=>"Profil"',
            '"login"=>"Login"' => '"login"=>"Connexion"',
            '"register"=>"Register"' => '"register"=>"S\'inscrire"',
            '"logout"=>"Logout"' => '"logout"=>"Déconnexion"'
        ];

        foreach ($specificReplacements as $search => $replace) {
            if (strpos($content, $search) !== false) {
                $content = str_replace($search, $replace, $content);
                $replacements++;
                $this->line("  🔄 Correction spécifique: $search → $replace");
            }
        }

        if ($content !== $originalContent) {
            File::put($labelsPath, $content);
            $this->info("\n✅ Fichier mis à jour avec $replacements corrections");
        } else {
            $this->info("\nℹ️  Aucune correction nécessaire");
        }

        // Vérification des traductions restantes en anglais
        $this->checkRemainingEnglish($content);

        return 0;
    }

    /**
     * Vérifie les traductions anglaises restantes
     */
    private function checkRemainingEnglish(string $content): void
    {
        $this->info("\n🔍 Vérification des traductions anglaises restantes:");

        // Pattern pour détecter du texte anglais simple
        preg_match_all('/\"[^\"]*\"=>\"([A-Z][a-zA-Z ]+)\"/', $content, $matches);

        $englishRemaining = [];
        foreach ($matches[1] as $match) {
            // Ignorer les mots français connus
            $frenchWords = ['Email', 'Mobile', 'WhatsApp', 'Google', 'Facebook', 'Instagram', 'Twitter', 'LinkedIn'];
            if (!in_array($match, $frenchWords) && preg_match('/^[A-Z][a-zA-Z ]+$/', $match)) {
                $englishRemaining[] = $match;
            }
        }

        if (empty($englishRemaining)) {
            $this->info("  ✅ Aucune traduction anglaise détectée");
        } else {
            $this->warn("  ⚠️  Traductions anglaises potentielles détectées:");
            foreach (array_unique($englishRemaining) as $english) {
                $this->line("    - $english");
            }
        }
    }
}
