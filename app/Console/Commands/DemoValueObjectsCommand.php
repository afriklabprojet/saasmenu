<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ValueObjects\Money;
use App\ValueObjects\Email;
use App\ValueObjects\PhoneNumber;
use App\ValueObjects\OrderStatus;
use App\ValueObjects\Coordinates;
use App\DTOs\OrderDTO;
use App\DTOs\ProductDTO;

/**
 * Demonstration command for Value Objects and DTOs
 * Shows practical usage examples
 */
class DemoValueObjectsCommand extends Command
{
    protected $signature = 'demo:value-objects';
    protected $description = 'Demonstrate Value Objects and DTOs usage';

    public function handle(): int
    {
        $this->info('🎯 Démonstration des Value Objects et DTOs');
        $this->newLine();

        // Money Value Object Demo
        $this->demoMoney();
        $this->newLine();

        // Email Value Object Demo
        $this->demoEmail();
        $this->newLine();

        // Phone Number Value Object Demo
        $this->demoPhoneNumber();
        $this->newLine();

        // Order Status Value Object Demo
        $this->demoOrderStatus();
        $this->newLine();

        // Coordinates Value Object Demo
        $this->demoCoordinates();
        $this->newLine();

        $this->info('✅ Démonstration terminée avec succès !');
        return 0;
    }

    private function demoMoney(): void
    {
        $this->info('💰 Money Value Object');

        // Create money objects
        $price = Money::fromString('25.99', 'USD');
        $discount = Money::fromString('5.00', 'USD');
        $tax = $price->percentage(10); // 10% tax

        $this->line("Prix original: {$price->format()}");
        $this->line("Remise: {$discount->format()}");
        $this->line("Taxe (10%): {$tax->format()}");

        // Calculate final price
        $finalPrice = $price->subtract($discount)->add($tax);
        $this->line("Prix final: {$finalPrice->format()}");

        // Business logic
        if ($finalPrice->greaterThan(Money::fromString('20.00'))) {
            $this->line("✅ Commande éligible pour la livraison gratuite");
        }

        // Currency conversion example
        $priceEUR = Money::fromString('21.50', 'EUR');
        $this->line("Prix en EUR: {$priceEUR->format()}");

        // Cents for payment processing
        $this->line("Prix en centimes: {$finalPrice->toCents()} cents");
    }

    private function demoEmail(): void
    {
        $this->info('📧 Email Value Object');

        try {
            $email = Email::fromString('jean.dupont@restaurant.fr');

            $this->line("Email: {$email->getValue()}");
            $this->line("Domaine: {$email->getDomain()}");
            $this->line("Partie locale: {$email->getLocalPart()}");
            $this->line("Email masqué: {$email->obfuscate()}");

            if ($email->isBusinessEmail()) {
                $this->line("✅ Email professionnel");
            } else {
                $this->line("ℹ️ Email personnel");
            }

            // Invalid email example
            try {
                $invalidEmail = Email::fromString('invalid-email');
            } catch (\InvalidArgumentException $e) {
                $this->line("❌ Email invalide détecté: {$e->getMessage()}");
            }

        } catch (\InvalidArgumentException $e) {
            $this->error("Erreur email: {$e->getMessage()}");
        }
    }

    private function demoPhoneNumber(): void
    {
        $this->info('📱 PhoneNumber Value Object');

        try {
            // French phone number
            $phone = PhoneNumber::fromString('+33123456789');

            $this->line("Téléphone: {$phone->getValue()}");
            $this->line("Code pays: {$phone->getCountryCode()}");
            $this->line("Numéro national: {$phone->getNationalNumber()}");
            $this->line("Formaté: {$phone->format()}");
            $this->line("International: {$phone->formatInternational()}");
            $this->line("Masqué: {$phone->obfuscate()}");

            if ($phone->isMobile()) {
                $this->line("📱 Mobile");
            } else {
                $this->line("☎️ Fixe");
            }

            if ($phone->isWhatsAppCompatible()) {
                $this->line("✅ Compatible WhatsApp");
            }

            // Côte d'Ivoire phone
            $phoneCI = PhoneNumber::fromString('+22501234567');
            $this->line("Téléphone CI: {$phoneCI->format()}");

        } catch (\InvalidArgumentException $e) {
            $this->error("Erreur téléphone: {$e->getMessage()}");
        }
    }

    private function demoOrderStatus(): void
    {
        $this->info('📋 OrderStatus Value Object');

        $status = OrderStatus::pending();

        $this->line("Statut: {$status->getValue()}");
        $this->line("Libellé: {$status->getLabel()}");
        $this->line("Couleur: {$status->getColor()}");

        if ($status->isPending()) {
            $this->line("⏳ Commande en attente");
        }

        if ($status->canBeCancelled()) {
            $this->line("❌ Peut être annulée");
        }

        // Status transitions
        $this->line("Transitions possibles:");
        foreach ($status->getAllowedTransitions() as $transition) {
            $this->line("  → {$transition}");
        }

        // Try a transition
        try {
            $newStatus = $status->transitionTo(OrderStatus::CONFIRMED);
            $this->line("✅ Transition vers: {$newStatus->getLabel()}");
        } catch (\InvalidArgumentException $e) {
            $this->error("❌ Transition invalide: {$e->getMessage()}");
        }

        // Invalid transition
        try {
            $invalidTransition = $status->transitionTo(OrderStatus::COMPLETED);
        } catch (\InvalidArgumentException $e) {
            $this->line("❌ Transition invalide détectée: {$e->getMessage()}");
        }
    }

    private function demoCoordinates(): void
    {
        $this->info('🗺️ Coordinates Value Object');

        try {
            // Abidjan coordinates
            $abidjan = new Coordinates(5.3600, -4.0083);
            $paris = new Coordinates(48.8566, 2.3522);

            $this->line("Abidjan: {$abidjan->format()}");
            $this->line("Paris: {$paris->format()}");

            // Calculate distance
            $distance = $abidjan->distanceTo($paris);
            $this->line("Distance Abidjan-Paris: " . round($distance, 2) . " km");

            // Check delivery radius
            $customerLocation = new Coordinates(5.3500, -4.0100);
            $deliveryRadius = 5; // 5 km

            if ($customerLocation->isWithinDeliveryRadius($abidjan, $deliveryRadius)) {
                $this->line("✅ Client dans la zone de livraison");
            } else {
                $this->line("❌ Client hors zone de livraison");
            }

            // URLs and features
            $this->line("Google Maps: {$abidjan->getGoogleMapsUrl()}");
            $this->line("What3Words: {$abidjan->toWhat3Words()}");
            $this->line("Ville proche: {$abidjan->getNearestCity()}");

            if ($abidjan->isInCountry('CI')) {
                $this->line("🇨🇮 Coordonnées en Côte d'Ivoire");
            }

        } catch (\InvalidArgumentException $e) {
            $this->error("Erreur coordonnées: {$e->getMessage()}");
        }
    }
}
