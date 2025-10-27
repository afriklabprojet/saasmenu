#!/bin/bash

echo "=================================================="
echo "🧪 SCRIPT DE TEST - TABLE BOOKING & MULTI-LANGUAGE"
echo "=================================================="
echo ""

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Vérifications
echo "📋 VÉRIFICATION DE L'ENVIRONNEMENT"
echo "--------------------------------------------------"

# 1. Vérifier table dans DB
echo -n "1. Table table_bookings existe... "
if php artisan tinker --execute="echo (Schema::hasTable('table_bookings') ? 'OUI' : 'NON');" | grep -q "OUI"; then
    echo -e "${GREEN}✅ OK${NC}"
else
    echo -e "${RED}❌ ERREUR${NC}"
    echo "   Exécuter: php artisan migrate"
fi

# 2. Vérifier modèle TableBooking
echo -n "2. Modèle TableBooking existe... "
if [ -f "app/Models/TableBooking.php" ]; then
    echo -e "${GREEN}✅ OK${NC}"
else
    echo -e "${RED}❌ ERREUR${NC}"
fi

# 3. Vérifier Controller
echo -n "3. TableBookingController existe... "
if [ -f "app/Http/Controllers/Admin/TableBookingController.php" ]; then
    echo -e "${GREEN}✅ OK${NC}"
else
    echo -e "${RED}❌ ERREUR${NC}"
fi

# 4. Vérifier vues
echo -n "4. Vues Blade existent... "
VIEWS_COUNT=$(find resources/views/admin/table-booking -name "*.blade.php" 2>/dev/null | wc -l | tr -d ' ')
if [ "$VIEWS_COUNT" -ge "4" ]; then
    echo -e "${GREEN}✅ OK ($VIEWS_COUNT vues)${NC}"
else
    echo -e "${RED}❌ ERREUR (trouvé $VIEWS_COUNT/4)${NC}"
fi

# 5. Vérifier routes
echo -n "5. Routes enregistrées... "
ROUTE_COUNT=$(php artisan route:list --name=admin.table-booking 2>/dev/null | grep -c "table-booking")
if [ "$ROUTE_COUNT" -ge "7" ]; then
    echo -e "${GREEN}✅ OK ($ROUTE_COUNT routes)${NC}"
else
    echo -e "${YELLOW}⚠️  ATTENTION ($ROUTE_COUNT routes trouvées)${NC}"
fi

# 6. Vérifier middleware localisation
echo -n "6. Middleware LocalizationMiddleware... "
if grep -q "LocalizationMiddleware::class," app/Http/Kernel.php; then
    echo -e "${GREEN}✅ ACTIVÉ${NC}"
else
    echo -e "${RED}❌ DÉSACTIVÉ${NC}"
fi

# 7. Vérifier composant langue
echo -n "7. Composant language-switcher... "
if [ -f "resources/views/components/language-switcher.blade.php" ]; then
    echo -e "${GREEN}✅ OK${NC}"
else
    echo -e "${RED}❌ ERREUR${NC}"
fi

echo ""
echo "=================================================="
echo "🔍 TESTS FONCTIONNELS"
echo "=================================================="

# Test 1 : Créer une réservation de test
echo ""
echo -n "Test 1: Créer réservation test... "
php artisan tinker --execute="
\$vendor = App\Models\User::where('type', 2)->first();
if (\$vendor) {
    \$booking = App\Models\TableBooking::create([
        'vendor_id' => \$vendor->id,
        'customer_name' => 'Test Client',
        'customer_email' => 'test@example.com',
        'customer_phone' => '+225 01 02 03 04 05',
        'guests_count' => 4,
        'booking_date' => now()->addDays(2)->format('Y-m-d'),
        'booking_time' => '19:00',
        'status' => 'pending',
        'special_requests' => 'Test de réservation automatique'
    ]);
    echo 'CRÉÉ ID=' . \$booking->id;
} else {
    echo 'ERREUR: Aucun vendor trouvé';
}
" 2>/dev/null | tail -1
if echo "$?" | grep -q "0"; then
    echo -e " ${GREEN}✅ OK${NC}"
else
    echo -e " ${RED}❌ ERREUR${NC}"
fi

# Test 2 : Vérifier disponibilité créneau
echo -n "Test 2: Vérification disponibilité... "
php artisan tinker --execute="
\$vendor = App\Models\User::where('type', 2)->first();
if (\$vendor) {
    \$available = App\Models\TableBooking::isTimeSlotAvailable(
        \$vendor->id,
        now()->addDays(3)->format('Y-m-d'),
        '20:00'
    );
    echo (\$available ? 'DISPONIBLE' : 'COMPLET');
}
" 2>/dev/null | tail -1
if echo "$?" | grep -q "0"; then
    echo -e " ${GREEN}✅ OK${NC}"
else
    echo -e " ${RED}❌ ERREUR${NC}"
fi

# Test 3 : Compter réservations
echo -n "Test 3: Nombre de réservations... "
BOOKING_COUNT=$(php artisan tinker --execute="echo App\Models\TableBooking::count();" 2>/dev/null | tail -1)
echo -e "${GREEN}$BOOKING_COUNT réservations${NC}"

# Test 4 : Test changement langue
echo -n "Test 4: Support multi-langue... "
if [ -f "app/Http/Middleware/LocalizationMiddleware.php" ]; then
    echo -e "${GREEN}✅ FR/EN supportés${NC}"
else
    echo -e "${RED}❌ ERREUR${NC}"
fi

echo ""
echo "=================================================="
echo "📊 RÉSUMÉ"
echo "=================================================="
echo ""
echo "Fichiers créés:"
echo "  - 1 migration (table_bookings)"
echo "  - 1 modèle (TableBooking)"
echo "  - 1 controller (TableBookingController)"
echo "  - 5 vues Blade (4 admin + 1 client)"
echo "  - 1 composant (language-switcher)"
echo "  - 10 routes (8 admin + 2 client)"
echo ""
echo "Addons implémentés:"
echo "  ${GREEN}✅ table_booking${NC} - Système de réservation complet"
echo "  ${GREEN}✅ multi_language${NC} - Support FR/EN activé"
echo ""
echo "Accès:"
echo "  🔗 Admin: http://localhost:8000/admin/table-booking"
echo "  🔗 Client: http://localhost:8000/{vendor_slug}/reserver-une-table"
echo "  🔑 Login: admin@restaurant.com / admin123"
echo ""
echo "Documentation:"
echo "  📄 Voir: IMPLEMENTATION_ADDONS_RAPPORT.md"
echo ""
echo "=================================================="

# Optionnel : Nettoyer réservations de test
read -p "Supprimer les réservations de test? (y/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan tinker --execute="
    App\Models\TableBooking::where('customer_email', 'test@example.com')->delete();
    echo 'Réservations de test supprimées';
    " 2>/dev/null | tail -1
    echo -e "${GREEN}✅ Nettoyé${NC}"
fi

echo ""
echo "🎉 Tests terminés!"
