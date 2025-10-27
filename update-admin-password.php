<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Hash;

// Mise à jour du mot de passe admin
$user = App\Models\User::find(1);
$user->password = Hash::make('admin123');
$user->save();

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║                                                                       ║\n";
echo "║         ✅ MOT DE PASSE ADMIN MIS À JOUR! ✅                         ║\n";
echo "║                                                                       ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "🔑 CREDENTIALS ADMIN\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";
echo "Email:    admin@restaurant.com\n";
echo "Password: admin123\n";
echo "\n";
echo "🌐 URL ADMIN\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";
echo "http://localhost:8000/admin\n";
echo "\n";
echo "✨ Vous pouvez maintenant vous connecter!\n";
echo "\n";
