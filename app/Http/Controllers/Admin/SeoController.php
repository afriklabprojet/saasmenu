<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SeoController extends Controller
{
    /**
     * Page de gestion SEO
     */
    public function index()
    {
        $vendorId = Auth::id();

        // Récupérer tous les meta tags du vendor
        $seoMetas = SeoMeta::where('vendor_id', $vendorId)
                          ->orderBy('page_type')
                          ->paginate(20);

        return view('admin.seo.index', compact('seoMetas'));
    }

    /**
     * Formulaire de création/édition
     */
    public function createOrEdit($pageType = 'home', $pageId = null)
    {
        $vendorId = Auth::id();
        $seoMeta = SeoMeta::getMetaTags($vendorId, $pageType, $pageId);

        return view('admin.seo.form', compact('seoMeta', 'pageType', 'pageId'));
    }

    /**
     * Sauvegarder les meta tags
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'page_type' => 'required|in:home,menu,product,category,blog,contact',
            'page_id' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'twitter_card' => 'nullable|in:summary,summary_large_image',
            'schema_markup' => 'nullable|string',
            'canonical_url' => 'nullable|url',
            'index' => 'nullable|boolean',
            'follow' => 'nullable|boolean',
        ]);

        $validated['vendor_id'] = Auth::id();
        $validated['index'] = $request->has('index');
        $validated['follow'] = $request->has('follow');

        // Upload de l'image OG
        if ($request->hasFile('og_image')) {
            $path = $request->file('og_image')->store('seo/og-images', 'public');
            $validated['og_image'] = $path;
        }

        SeoMeta::updateOrCreateMeta(
            $validated['vendor_id'],
            $validated['page_type'],
            $validated,
            $validated['page_id'] ?? null
        );

        return redirect()
            ->route('admin.seo.index')
            ->with('success', 'Meta tags SEO enregistrés avec succès');
    }

    /**
     * Supprimer un meta tag
     */
    public function destroy($id)
    {
        $seoMeta = SeoMeta::where('vendor_id', Auth::id())->findOrFail($id);

        // Supprimer l'image OG si elle existe
        if ($seoMeta->og_image) {
            Storage::disk('public')->delete($seoMeta->og_image);
        }

        $seoMeta->delete();

        return back()->with('success', 'Meta tag supprimé');
    }

    /**
     * Générer sitemap.xml
     */
    public function generateSitemap()
    {
        $vendor = Auth::user();

        // Vérification de sécurité pour éviter les erreurs null
        if (!$vendor) {
            return response()->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $domain = !empty($vendor->unique_slug) ? url($vendor->unique_slug) : url('/');

        // Construire le sitemap
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Homepage
        $xml .= '<url>';
        $xml .= '<loc>' . $domain . '</loc>';
        $xml .= '<lastmod>' . now()->toAtomString() . '</lastmod>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '</url>';

        // Pages avec SEO meta
        $vendorId = $vendor->id ?? 0;
        $seoMetas = SeoMeta::where('vendor_id', $vendorId)->get();
        foreach ($seoMetas as $meta) {
            if ($meta->index && $meta->canonical_url) {
                $xml .= '<url>';
                $xml .= '<loc>' . $meta->canonical_url . '</loc>';
                $xml .= '<lastmod>' . $meta->updated_at->toAtomString() . '</lastmod>';
                $xml .= '<changefreq>weekly</changefreq>';
                $xml .= '<priority>0.8</priority>';
                $xml .= '</url>';
            }
        }

        $xml .= '</urlset>';

        // Sauvegarder dans public/
        $filename = 'sitemap-' . $vendor->id . '.xml';
        Storage::disk('public')->put($filename, $xml);

        return redirect()
            ->route('admin.seo.index')
            ->with('success', 'Sitemap généré : ' . asset('storage/' . $filename));
    }

    /**
     * Générer robots.txt
     */
    public function generateRobots()
    {
        $vendor = Auth::user();

        // Vérification de sécurité pour éviter les erreurs null
        if (!$vendor) {
            return response()->json(['error' => 'Utilisateur non connecté'], 401);
        }

        $domain = !empty($vendor->unique_slug) ? url($vendor->unique_slug) : url('/');
        $vendorId = $vendor->id ?? 0;

        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /api/\n\n";
        $robots .= "Sitemap: {$domain}/storage/sitemap-{$vendorId}.xml\n";

        $filename = 'robots-' . $vendorId . '.txt';
        Storage::disk('public')->put($filename, $robots);

        return redirect()
            ->route('admin.seo.index')
            ->with('success', 'Robots.txt généré : ' . asset('storage/' . $filename));
    }
}
