<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// Créer la table languages si elle n'existe pas
if (!Schema::hasTable('languages')) {
    echo "Création de la table languages...\n";

    Schema::create('languages', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('code', 5);
        $table->string('layout', 10)->default('ltr');
        $table->string('image')->nullable();
        $table->enum('is_default', [1, 2])->default(2);
        $table->enum('is_available', [1, 2])->default(1);
        $table->enum('is_deleted', [1, 2])->default(2);
        $table->timestamps();
        $table->index('code');
    });

    echo "Table languages créée avec succès.\n";

    // Insérer les langues par défaut
    DB::table('languages')->insert([
        [
            'name' => 'Français',
            'code' => 'fr',
            'layout' => 'ltr',
            'is_default' => 1,
            'is_available' => 1,
            'is_deleted' => 2,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'English',
            'code' => 'en',
            'layout' => 'ltr',
            'is_default' => 2,
            'is_available' => 1,
            'is_deleted' => 2,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);

    echo "Langues par défaut insérées.\n";

} else {
    echo "Table languages existe déjà.\n";
}

// Vérifier que la table contient des données
$count = DB::table('languages')->count();
echo "Nombre de langues dans la table: {$count}\n";

// Afficher les langues
$languages = DB::table('languages')->select('name', 'code', 'is_default')->get();
foreach ($languages as $lang) {
    $default = $lang->is_default == 1 ? ' (défaut)' : '';
    echo "- {$lang->name} ({$lang->code}){$default}\n";
}
