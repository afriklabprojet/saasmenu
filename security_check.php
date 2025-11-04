<?php

/*
 * Script de vérification des corrections de sécurité
 * A exécuter via: php artisan tinker < security_check.php
 */

echo "=== VÉRIFICATION DES CORRECTIONS DE SÉCURITÉ ===\n\n";

// 1. Vérifier que les Form Requests existent
echo "1. Vérification des Form Requests créées:\n";
if (class_exists('App\Http\Requests\Admin\TaxRequest')) {
    echo "   ✅ TaxRequest existe\n";
} else {
    echo "   ❌ TaxRequest manquante\n";
}

if (class_exists('App\Http\Requests\Admin\StatusChangeRequest')) {
    echo "   ✅ StatusChangeRequest existe\n";
} else {
    echo "   ❌ StatusChangeRequest manquante\n";
}

// 2. Vérifier que le service d'audit existe
echo "\n2. Vérification du service d'audit:\n";
if (class_exists('App\Services\AuditService')) {
    echo "   ✅ AuditService existe\n";
} else {
    echo "   ❌ AuditService manquant\n";
}

// 3. Vérifier les canaux de logs
echo "\n3. Vérification des canaux de logs:\n";
$logChannels = config('logging.channels');
$requiredChannels = ['audit', 'payment', 'gdpr', 'security'];

foreach ($requiredChannels as $channel) {
    if (isset($logChannels[$channel])) {
        echo "   ✅ Canal '{$channel}' configuré\n";
    } else {
        echo "   ❌ Canal '{$channel}' manquant\n";
    }
}

// 4. Vérifier le middleware de sécurité
echo "\n4. Vérification du middleware de sécurité:\n";
$globalMiddleware = config('app.middleware') ?? app('App\Http\Kernel')->getGlobalMiddleware();
$securityMiddlewareActive = false;

foreach ($globalMiddleware as $middleware) {
    if (strpos($middleware, 'SecurityHeaders') !== false) {
        $securityMiddlewareActive = true;
        break;
    }
}

if ($securityMiddlewareActive) {
    echo "   ✅ Middleware SecurityHeaders activé\n";
} else {
    echo "   ❌ Middleware SecurityHeaders non activé\n";
}

echo "\n=== VÉRIFICATION TERMINÉE ===\n";
echo "Prochaines étapes:\n";
echo "- Tester les formulaires de création/modification de taxes\n";
echo "- Vérifier les logs d'audit dans storage/logs/audit.log\n";
echo "- Vérifier les en-têtes de sécurité dans les réponses HTTP\n";