<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\helper;
class FrontMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(!file_exists(storage_path() . "/installed")) {
            return redirect('install');
            exit;
        }

        // Vérifier si le paramètre vendor est présent dans la route
        $vendor = $request->route('vendor') ?? $request->vendor;

        // Si pas de vendor, essayer de détecter par le domaine ou utiliser un défaut
        if (!$vendor) {
            // Pour le développement local, utiliser un vendor par défaut (type: 2)
            $user = User::where('type', 2)->where('is_available', 1)->first(); // Premier vendor disponible
        } else {
            $user = User::where('slug', $vendor)->first();
        }

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return response(view('errors.404'), 404);
        }

        Helper::language($user->id);
        if (@helper::appdata(@$user->id)->maintenance_mode == 1) {
            return response(view('errors.maintenance'));
        }
        $checkplan = helper::checkplan($user->id, '3');
        $v = json_decode(json_encode($checkplan));
        if (@$v->original->status == 2) {
            return response(view('errors.accountdeleted'));
        }
        if ($user->is_available == 2) {
            return response(view('errors.accountdeleted'));
        }
        return $next($request);
    }
}
