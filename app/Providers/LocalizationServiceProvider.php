<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Lang;
use App\Helpers\TranslationHelper;

class LocalizationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Enregistrer le helper de traduction
        $this->app->singleton('translation.helper', function () {
            return new TranslationHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Activer la locale française par défaut
        TranslationHelper::setFrenchLocale();

        // Partager les helpers avec toutes les vues
        View::share('trans', new TranslationHelper());

        // Enregistrer les messages de validation français personnalisés
        Validator::replacer('required', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Le champ :attribute est obligatoire.');
        });

        Validator::replacer('email', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Le champ :attribute doit être une adresse e-mail valide.');
        });

        Validator::replacer('unique', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'Le champ :attribute a déjà été pris.');
        });

        Validator::replacer('confirmed', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $this->getAttributeName($attribute), 'La confirmation du champ :attribute ne correspond pas.');
        });

        // Ajouter des macros Blade pour les traductions
        Blade::directive('trans_status', function ($status) {
            return "<?php echo App\Helpers\TranslationHelper::translateStatus($status); ?>";
        });

        Blade::directive('trans_user_type', function ($type) {
            return "<?php echo App\Helpers\TranslationHelper::translateUserType($type); ?>";
        });

        Blade::directive('format_date_fr', function ($date) {
            return "<?php echo App\Helpers\TranslationHelper::formatDate($date); ?>";
        });

        Blade::directive('format_price_fr', function ($amount) {
            return "<?php echo App\Helpers\TranslationHelper::formatPrice($amount); ?>";
        });

        // Personnaliser les messages d'erreur par défaut
        $this->customizeErrorMessages();
    }

    /**
     * Personnalise les messages d'erreur Laravel
     */
    private function customizeErrorMessages(): void
    {
        $messages = TranslationHelper::getValidationMessages();
        $attributes = TranslationHelper::getAttributeNames();

        // Enregistrer les messages personnalisés
        foreach ($messages as $key => $message) {
            if (is_array($message)) {
                foreach ($message as $subKey => $subMessage) {
                    Lang::addLines(["validation.$key.$subKey" => $subMessage], 'fr');
                }
            } else {
                Lang::addLines(["validation.$key" => $message], 'fr');
            }
        }

        // Enregistrer les noms d'attributs
        Lang::addLines(['validation.attributes' => $attributes], 'fr');
    }

    /**
     * Obtient le nom traduit de l'attribut
     */
    private function getAttributeName(string $attribute): string
    {
        $attributes = TranslationHelper::getAttributeNames();
        return $attributes[$attribute] ?? $attribute;
    }
}
