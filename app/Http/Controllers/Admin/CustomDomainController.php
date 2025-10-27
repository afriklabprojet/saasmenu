<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomDomainController extends Controller
{
    /**
     * Afficher la page de configuration du domaine personnalisé
     */
    public function index()
    {
        $vendor = Auth::user();

        return view('admin.custom-domain.index', compact('vendor'));
    }

    /**
     * Enregistrer ou mettre à jour le domaine personnalisé
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'custom_domain' => [
                'required',
                'string',
                'regex:/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,6}$/',
                'unique:users,custom_domain,' . Auth::id()
            ]
        ], [
            'custom_domain.required' => 'Le domaine personnalisé est requis',
            'custom_domain.regex' => 'Format de domaine invalide (ex: monrestaurant.com)',
            'custom_domain.unique' => 'Ce domaine est déjà utilisé par un autre restaurant'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        /** @var \App\Models\User $vendor */
        $vendor = Auth::user();
        $domain = strtolower(trim($request->custom_domain));

        // Vérifier que ce n'est pas le domaine principal de l'application
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST);
        if ($domain === $appDomain) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas utiliser le domaine principal de l\'application');
        }

        $vendor->custom_domain = $domain;
        $vendor->domain_verified = false; // Nécessite vérification
        $vendor->domain_verified_at = null;
        $vendor->save();

        return redirect()->back()
            ->with('success', 'Domaine personnalisé enregistré ! Veuillez configurer vos DNS pour pointer vers notre serveur.');
    }

    /**
     * Vérifier si le domaine est correctement configuré
     */
    public function verify(Request $request)
    {
        /** @var \App\Models\User $vendor */
        $vendor = Auth::user();

        if (!$vendor->custom_domain) {
            return redirect()->back()
                ->with('error', 'Aucun domaine personnalisé configuré');
        }

        // Vérifier que le domaine pointe bien vers notre serveur
        $domain = $vendor->custom_domain;
        $appIp = gethostbyname(parse_url(config('app.url'), PHP_URL_HOST));
        $domainIp = gethostbyname($domain);

        if ($domainIp === $appIp || $this->checkCNAME($domain)) {
            $vendor->domain_verified = true;
            $vendor->domain_verified_at = now();
            $vendor->save();

            return redirect()->back()
                ->with('success', 'Domaine vérifié avec succès ! Votre restaurant est maintenant accessible via ' . $domain);
        }

        return redirect()->back()
            ->with('error', 'Le domaine ne pointe pas vers notre serveur. Veuillez vérifier votre configuration DNS.');
    }

    /**
     * Supprimer le domaine personnalisé
     */
    public function destroy()
    {
        /** @var \App\Models\User $vendor */
        $vendor = Auth::user();
        $vendor->custom_domain = null;
        $vendor->domain_verified = false;
        $vendor->domain_verified_at = null;
        $vendor->save();

        return redirect()->back()
            ->with('success', 'Domaine personnalisé supprimé');
    }

    /**
     * Vérifier si le CNAME est configuré
     */
    private function checkCNAME($domain)
    {
        $dns = dns_get_record($domain, DNS_CNAME);
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST);

        foreach ($dns as $record) {
            if (isset($record['target']) && str_contains($record['target'], $appDomain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir les instructions de configuration DNS
     */
    public function dnsInstructions()
    {
        $vendor = Auth::user();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $appIp = gethostbyname($appDomain);

        $instructions = [
            'domain' => $vendor->custom_domain,
            'app_domain' => $appDomain,
            'app_ip' => $appIp,
            'cname_record' => [
                'type' => 'CNAME',
                'name' => '@',
                'value' => $appDomain
            ],
            'a_record' => [
                'type' => 'A',
                'name' => '@',
                'value' => $appIp
            ]
        ];

        return response()->json($instructions);
    }
}
