<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Addons\RestaurantQrMenu\Models\QrMenuDesign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class QrMenuDesignController extends Controller
{
    /**
     * Liste des designs de QR codes
     */
    public function index()
    {
        $user = Auth::user();
        $vendorId = $user->type == 1 ? null : $user->id; // Admin voit tout, vendor ses designs seulement

        $designs = QrMenuDesign::query()
            ->when($vendorId, fn($q) => $q->forVendor($vendorId))
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.qr-designs.index', compact('designs'));
    }

    /**
     * Formulaire de création d'un design
     */
    public function create()
    {
        return view('admin.qr-designs.create');
    }

    /**
     * Sauvegarder un nouveau design
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'background_color' => 'required|string|max:7',
            'foreground_color' => 'required|string|max:7',
            'size' => 'required|integer|min:100|max:1000',
            'format' => 'required|in:png,jpg,svg',
        ]);

        $user = Auth::user();
        $vendorId = $user->type == 1 ? $request->vendor_id : $user->id;

        $data = [
            'vendor_id' => $vendorId,
            'name' => $request->name,
            'background_color' => $request->background_color,
            'foreground_color' => $request->foreground_color,
            'size' => $request->size,
            'format' => $request->format,
            'custom_settings' => [
                'margin' => $request->margin ?? 2,
                'error_correction' => $request->error_correction ?? 'M',
                'logo_size_ratio' => $request->logo_size_ratio ?? 0.3,
            ],
            'is_default' => $request->boolean('is_default', false),
        ];

        // Upload du logo si fourni
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('qr-designs/logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        $design = QrMenuDesign::create($data);

        // Définir comme design par défaut si demandé
        if ($request->boolean('is_default', false)) {
            $design->setAsDefault();
        }

        return redirect()->route('admin.qr-designs.index')
            ->with('success', 'Design QR créé avec succès');
    }

    /**
     * Afficher un design
     */
    public function show(QrMenuDesign $qrDesign)
    {
        $this->authorizeDesign($qrDesign);

        return view('admin.qr-designs.show', compact('qrDesign'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(QrMenuDesign $qrDesign)
    {
        $this->authorizeDesign($qrDesign);

        return view('admin.qr-designs.edit', compact('qrDesign'));
    }

    /**
     * Mettre à jour un design
     */
    public function update(Request $request, QrMenuDesign $qrDesign)
    {
        $this->authorizeDesign($qrDesign);

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'background_color' => 'required|string|max:7',
            'foreground_color' => 'required|string|max:7',
            'size' => 'required|integer|min:100|max:1000',
            'format' => 'required|in:png,jpg,svg',
        ]);

        $data = [
            'name' => $request->name,
            'background_color' => $request->background_color,
            'foreground_color' => $request->foreground_color,
            'size' => $request->size,
            'format' => $request->format,
            'custom_settings' => array_merge($qrDesign->custom_settings ?? [], [
                'margin' => $request->margin ?? 2,
                'error_correction' => $request->error_correction ?? 'M',
                'logo_size_ratio' => $request->logo_size_ratio ?? 0.3,
            ]),
        ];

        // Upload du nouveau logo si fourni
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo
            if ($qrDesign->logo_path && Storage::disk('public')->exists($qrDesign->logo_path)) {
                Storage::disk('public')->delete($qrDesign->logo_path);
            }

            $logoPath = $request->file('logo')->store('qr-designs/logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        $qrDesign->update($data);

        // Gérer le statut par défaut
        if ($request->boolean('is_default', false) && !$qrDesign->is_default) {
            $qrDesign->setAsDefault();
        }

        return redirect()->route('admin.qr-designs.show', $qrDesign)
            ->with('success', 'Design QR mis à jour avec succès');
    }

    /**
     * Supprimer un design
     */
    public function destroy(QrMenuDesign $qrDesign)
    {
        $this->authorizeDesign($qrDesign);

        // Empêcher la suppression du design par défaut
        if ($qrDesign->is_default) {
            return back()->with('error', 'Impossible de supprimer le design par défaut');
        }

        // Supprimer le logo s'il existe
        if ($qrDesign->logo_path && Storage::disk('public')->exists($qrDesign->logo_path)) {
            Storage::disk('public')->delete($qrDesign->logo_path);
        }

        $qrDesign->delete();

        return redirect()->route('admin.qr-designs.index')
            ->with('success', 'Design QR supprimé avec succès');
    }

    /**
     * Définir un design comme par défaut
     */
    public function setDefault(QrMenuDesign $qrDesign)
    {
        $this->authorizeDesign($qrDesign);

        $qrDesign->setAsDefault();

        return back()->with('success', 'Design défini comme par défaut');
    }

    /**
     * Dupliquer un design
     */
    public function duplicate(QrMenuDesign $qrDesign)
    {
        $this->authorizeDesign($qrDesign);

        $newDesign = $qrDesign->replicate();
        $newDesign->name = $qrDesign->name . ' (Copie)';
        $newDesign->is_default = false;
        $newDesign->save();

        // Dupliquer le logo si il existe
        if ($qrDesign->logo_path && Storage::disk('public')->exists($qrDesign->logo_path)) {
            $extension = pathinfo($qrDesign->logo_path, PATHINFO_EXTENSION);
            $newLogoPath = 'qr-designs/logos/' . uniqid() . '.' . $extension;

            Storage::disk('public')->copy($qrDesign->logo_path, $newLogoPath);
            $newDesign->update(['logo_path' => $newLogoPath]);
        }

        return redirect()->route('admin.qr-designs.edit', $newDesign)
            ->with('success', 'Design dupliqué avec succès');
    }

    /**
     * Prévisualiser un design
     */
    public function preview(Request $request, QrMenuDesign $qrDesign)
    {
        $this->authorizeDesign($qrDesign);

        // Générer un QR code de test avec le design
        $testUrl = route('home');

        return response()->json([
            'preview_url' => $this->generatePreviewQr($testUrl, $qrDesign),
            'design' => $qrDesign,
        ]);
    }

    /**
     * Vérifier les autorisations sur un design
     */
    private function authorizeDesign(QrMenuDesign $qrDesign): void
    {
        $user = Auth::user();

        if ($user->type != 1 && $qrDesign->vendor_id != $user->id) {
            abort(403, 'Accès non autorisé');
        }
    }

    /**
     * Générer un QR code de prévisualisation
     */
    private function generatePreviewQr(string $url, QrMenuDesign $design): string
    {
        // Ici vous pouvez implémenter la génération d'un QR code de test
        // Pour l'instant, retourner une URL placeholder
        return 'data:image/png;base64,preview_placeholder';
    }
}
