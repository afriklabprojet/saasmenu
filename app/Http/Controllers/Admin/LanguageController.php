<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Page de gestion des langues
     */
    public function index()
    {
        $languages = Language::orderBy('sort_order')->get();
        return view('admin.languages.index', compact('languages'));
    }

    /**
     * Changer la langue active
     */
    public function changeLanguage(Request $request, $code)
    {
        $language = Language::getByCode($code);

        if (!$language) {
            return back()->withErrors(['error' => 'Langue non trouvée']);
        }

        // Sauvegarder en session
        Session::put('locale', $code);
        Session::put('language_id', $language->id);
        Session::put('rtl', $language->rtl);

        // Appliquer la langue
        App::setLocale($code);

        return back()->with('success', 'Langue changée : ' . $language->name);
    }

    /**
     * Activer/Désactiver une langue
     */
    public function toggleStatus($id)
    {
        $language = Language::findOrFail($id);
        
        // Empêcher de désactiver la langue par défaut
        if ($language->is_default && $language->is_active) {
            return back()->withErrors(['error' => 'Impossible de désactiver la langue par défaut']);
        }

        $language->update(['is_active' => !$language->is_active]);

        return back()->with('success', 'Statut mis à jour');
    }

    /**
     * Définir comme langue par défaut
     */
    public function setDefault($id)
    {
        $language = Language::findOrFail($id);
        
        // Activer la langue si elle ne l'est pas
        if (!$language->is_active) {
            $language->update(['is_active' => true]);
        }

        $language->setAsDefault();

        return back()->with('success', $language->name . ' définie comme langue par défaut');
    }

    /**
     * Mettre à jour l'ordre
     */
    public function updateOrder(Request $request)
    {
        $order = $request->input('order', []);

        foreach ($order as $index => $id) {
            Language::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
