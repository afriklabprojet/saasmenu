<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Addons\RestaurantQrMenu\Models\RestaurantQrMenu;
use App\Addons\RestaurantQrMenu\Models\QrMenuDesign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrMenuController extends Controller
{
    /**
     * Liste des QR menus
     */
    public function index()
    {
        $user = Auth::user();
        $vendorId = $user->type == 1 ? null : $user->id; // Admin voit tout, vendor ses QR seulement

        $qrMenus = RestaurantQrMenu::query()
            ->when($vendorId, fn($q) => $q->forVendor($vendorId))
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.qr-menu.index', compact('qrMenus'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $user = Auth::user();
        $vendorId = $user->type == 1 ? null : $user->id;

        $designs = QrMenuDesign::query()
            ->when($vendorId, fn($q) => $q->forVendor($vendorId))
            ->get();

        return view('admin.qr-menu.create', compact('designs'));
    }

    /**
     * Sauvegarder un nouveau QR menu
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'menu_url' => 'required|url',
            'table_numbers' => 'nullable|array',
            'design_id' => 'nullable|exists:qr_menu_designs,id',
        ]);

        $user = Auth::user();
        $vendorId = $user->type == 1 ? $request->vendor_id : $user->id;

        // Générer le slug unique
        $slug = Str::slug($request->name . '-' . $vendorId . '-' . time());

        // Créer le QR menu
        $qrMenu = RestaurantQrMenu::create([
            'vendor_id' => $vendorId,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'menu_url' => $request->menu_url,
            'table_numbers' => $request->table_numbers,
            'qr_code_path' => '', // Sera mis à jour après génération
            'settings' => [
                'design_id' => $request->design_id,
                'auto_redirect' => $request->boolean('auto_redirect', true),
                'analytics_enabled' => $request->boolean('analytics_enabled', true),
            ],
        ]);

        // Générer le QR code
        $this->generateQrCode($qrMenu, $request->design_id);

        return redirect()->route('admin.qr-menu.index')
            ->with('success', 'QR Menu créé avec succès');
    }

    /**
     * Afficher un QR menu
     */
    public function show(RestaurantQrMenu $qrMenu)
    {
        $this->authorizeQrMenu($qrMenu);

        $stats = $qrMenu->getScanStats();
        $recentScans = $qrMenu->scans()
            ->recent(7)
            ->orderBy('scanned_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.qr-menu.show', compact('qrMenu', 'stats', 'recentScans'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(RestaurantQrMenu $qrMenu)
    {
        $this->authorizeQrMenu($qrMenu);

        $user = Auth::user();
        $vendorId = $user->type == 1 ? null : $user->id;

        $designs = QrMenuDesign::query()
            ->when($vendorId, fn($q) => $q->forVendor($vendorId))
            ->get();

        return view('admin.qr-menu.edit', compact('qrMenu', 'designs'));
    }

    /**
     * Mettre à jour un QR menu
     */
    public function update(Request $request, RestaurantQrMenu $qrMenu)
    {
        $this->authorizeQrMenu($qrMenu);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'menu_url' => 'required|url',
            'table_numbers' => 'nullable|array',
            'design_id' => 'nullable|exists:qr_menu_designs,id',
        ]);

        $oldDesignId = $qrMenu->settings['design_id'] ?? null;
        $newDesignId = $request->design_id;

        $qrMenu->update([
            'name' => $request->name,
            'description' => $request->description,
            'menu_url' => $request->menu_url,
            'table_numbers' => $request->table_numbers,
            'settings' => array_merge($qrMenu->settings ?? [], [
                'design_id' => $newDesignId,
                'auto_redirect' => $request->boolean('auto_redirect', true),
                'analytics_enabled' => $request->boolean('analytics_enabled', true),
            ]),
        ]);

        // Régénérer le QR code si le design a changé
        if ($oldDesignId != $newDesignId) {
            $this->generateQrCode($qrMenu, $newDesignId);
        }

        return redirect()->route('admin.qr-menu.show', $qrMenu)
            ->with('success', 'QR Menu mis à jour avec succès');
    }

    /**
     * Supprimer un QR menu
     */
    public function destroy(RestaurantQrMenu $qrMenu)
    {
        $this->authorizeQrMenu($qrMenu);

        // Supprimer le fichier QR code
        if ($qrMenu->qr_code_path && Storage::disk('public')->exists($qrMenu->qr_code_path)) {
            Storage::disk('public')->delete($qrMenu->qr_code_path);
        }

        $qrMenu->delete();

        return redirect()->route('admin.qr-menu.index')
            ->with('success', 'QR Menu supprimé avec succès');
    }

    /**
     * Télécharger le QR code
     */
    public function download(RestaurantQrMenu $qrMenu)
    {
        $this->authorizeQrMenu($qrMenu);

        if (!$qrMenu->qr_code_path || !Storage::disk('public')->exists($qrMenu->qr_code_path)) {
            return back()->with('error', 'QR Code non trouvé');
        }

        $filename = Str::slug($qrMenu->name) . '-qr-code.' . pathinfo($qrMenu->qr_code_path, PATHINFO_EXTENSION);
        $filePath = storage_path('app/public/' . $qrMenu->qr_code_path);

        return response()->download($filePath, $filename);
    }

    /**
     * Régénérer le QR code
     */
    public function regenerate(RestaurantQrMenu $qrMenu)
    {
        $this->authorizeQrMenu($qrMenu);

        $designId = $qrMenu->settings['design_id'] ?? null;
        $this->generateQrCode($qrMenu, $designId);

        return back()->with('success', 'QR Code régénéré avec succès');
    }

    /**
     * Générer le QR code
     */
    private function generateQrCode(RestaurantQrMenu $qrMenu, ?int $designId = null): void
    {
        // Obtenir les paramètres de design
        $design = $designId ? QrMenuDesign::find($designId) : null;

        $size = $design->size ?? 300;
        $format = $design->format ?? 'png';
        $backgroundColor = $design->background_color ?? '#ffffff';
        $foregroundColor = $design->foreground_color ?? '#000000';

        // URL de scan qui redirige vers le menu avec analytics
        $scanUrl = route('qr-menu.scan', $qrMenu->slug);

        // Générer le QR code
        $qrCode = QrCode::format($format)
            ->size($size)
            ->backgroundColor($backgroundColor)
            ->color($foregroundColor)
            ->margin(2);

        // Ajouter le logo si disponible
        if ($design && $design->logo_path && Storage::disk('public')->exists($design->logo_path)) {
            $logoPath = storage_path('app/public/' . $design->logo_path);
            $qrCode->merge($logoPath, 0.3, true);
        }

        // Chemin de sauvegarde
        $path = 'qr-codes/' . $qrMenu->slug . '.' . $format;

        // Supprimer l'ancien fichier s'il existe
        if ($qrMenu->qr_code_path && Storage::disk('public')->exists($qrMenu->qr_code_path)) {
            Storage::disk('public')->delete($qrMenu->qr_code_path);
        }

        // Sauvegarder le nouveau QR code
        Storage::disk('public')->put($path, $qrCode->generate($scanUrl));

        // Mettre à jour le chemin dans la base
        $qrMenu->update(['qr_code_path' => $path]);
    }

    /**
     * Vérifier les autorisations sur un QR menu
     */
    private function authorizeQrMenu(RestaurantQrMenu $qrMenu): void
    {
        $user = Auth::user();

        if ($user->type != 1 && $qrMenu->vendor_id != $user->id) {
            abort(403, 'Accès non autorisé');
        }
    }
}
