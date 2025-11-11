<?php

namespace App\Http\Controllers\web\Traits;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Settings;
use App\Helpers\helper;

/**
 * VendorDataTrait
 *
 * Trait réutilisable pour récupérer les données du vendor
 * en fonction du host (domaine principal ou custom domain)
 *
 * Utilisé par tous les contrôleurs web nécessitant l'identification du restaurant/vendor
 */
trait VendorDataTrait
{
    /**
     * Récupère les données du vendor en fonction du host
     *
     * @param Request $request
     * @return array ['storeinfo' => Settings|User, 'vendor_id' => int]
     */
    protected function getVendorData(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];

        // Si c'est le domaine principal (ou localhost pour dev)
        if ($host == env('WEBSITE_HOST') ||
            strpos($host, 'localhost') !== false ||
            strpos($host, '127.0.0.1') !== false) {

            // Utiliser helper pour récupérer storeinfo via vendor parameter
            $storeinfo = helper::storeinfo($request->vendor);
            $vdata = $storeinfo->id;
        }
        // Si c'est un custom domain
        else {
            // Chercher le restaurant par custom domain
            $storeinfo = Settings::where('custom_domain', $host)->first();

            if (!$storeinfo) {
                // Fallback: utiliser le premier vendor de type 2 (restaurant)
                $storeinfo = User::where('type', 2)->first();
                $vdata = $storeinfo->id;
            } else {
                $vdata = $storeinfo->vendor_id;
            }
        }

        return [
            'storeinfo' => $storeinfo,
            'vendor_id' => $vdata
        ];
    }

    /**
     * Récupère uniquement le vendor_id
     *
     * @param Request $request
     * @return int
     */
    protected function getVendorId(Request $request)
    {
        $data = $this->getVendorData($request);
        return $data['vendor_id'];
    }

    /**
     * Récupère uniquement les infos du store
     *
     * @param Request $request
     * @return Settings|User
     */
    protected function getStoreInfo(Request $request)
    {
        $data = $this->getVendorData($request);
        return $data['storeinfo'];
    }
}
