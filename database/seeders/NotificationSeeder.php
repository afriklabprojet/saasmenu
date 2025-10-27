<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use App\Models\DeviceToken;
use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        $this->command->info('🔔 Seeding Notifications...');

        $restaurants = Restaurant::all();
        $totalNotifications = 0;

        foreach ($restaurants as $restaurant) {
            // Get restaurant users and device tokens
            $restaurantUsers = User::whereHas('restaurantUsers', function($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })->get();

            $deviceTokens = DeviceToken::where('restaurant_id', $restaurant->id)
                ->where('is_active', true)
                ->get();

            if ($deviceTokens->isEmpty()) {
                continue;
            }

            // Create various types of notifications

            // 1. Order notifications (most common)
            $orderNotifications = rand(20, 40);
            for ($i = 0; $i < $orderNotifications; $i++) {
                $this->createOrderNotification($restaurant, $deviceTokens->random());
                $totalNotifications++;
            }

            // 2. Loyalty program notifications
            $loyaltyNotifications = rand(10, 20);
            for ($i = 0; $i < $loyaltyNotifications; $i++) {
                $this->createLoyaltyNotification($restaurant, $deviceTokens->random());
                $totalNotifications++;
            }

            // 3. System notifications for restaurant staff
            $systemNotifications = rand(5, 15);
            foreach ($restaurantUsers as $user) {
                $userTokens = $deviceTokens->where('user_id', $user->id);
                if ($userTokens->isNotEmpty()) {
                    for ($i = 0; $i < min($systemNotifications, 5); $i++) {
                        $this->createSystemNotification($restaurant, $userTokens->random(), $user);
                        $totalNotifications++;
                    }
                }
            }

            // 4. Promotional notifications
            $promoNotifications = rand(3, 8);
            for ($i = 0; $i < $promoNotifications; $i++) {
                $this->createPromotionalNotification($restaurant, $deviceTokens->random());
                $totalNotifications++;
            }

            // 5. POS system notifications
            $posNotifications = rand(5, 12);
            for ($i = 0; $i < $posNotifications; $i++) {
                $staffTokens = $deviceTokens->whereIn('user_id', $restaurantUsers->pluck('id'));
                if ($staffTokens->isNotEmpty()) {
                    $this->createPosNotification($restaurant, $staffTokens->random());
                    $totalNotifications++;
                }
            }

            // 6. Recent notifications (within last 24 hours)
            $recentNotifications = rand(3, 8);
            for ($i = 0; $i < $recentNotifications; $i++) {
                $this->createRecentNotification($restaurant, $deviceTokens->random());
                $totalNotifications++;
            }

            $restaurantNotificationCount = Notification::where('restaurant_id', $restaurant->id)->count();
            $this->command->info("   ✓ Created {$restaurantNotificationCount} notifications for {$restaurant->name}");
        }

        // Create delivery status summary
        $sentCount = Notification::where('status', 'sent')->count();
        $deliveredCount = Notification::where('status', 'delivered')->count();
        $failedCount = Notification::where('status', 'failed')->count();
        $readCount = Notification::where('read_at', '!=', null)->count();

        $this->command->info("✅ Created {$totalNotifications} notifications total");
        $this->command->info("   📤 Sent: {$sentCount} | Delivered: {$deliveredCount} | Failed: {$failedCount}");
        $this->command->info("   👀 Read: {$readCount} | Unread: " . ($totalNotifications - $readCount));
    }

    private function createOrderNotification(Restaurant $restaurant, DeviceToken $deviceToken): void
    {
        $orderStatuses = ['confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];
        $status = fake()->randomElement($orderStatuses);

        Notification::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $deviceToken->user_id,
            'device_token_id' => $deviceToken->id,
            'title' => $this->getOrderTitle($status),
            'body' => $this->getOrderBody($status),
            'type' => 'order',
            'data' => [
                'order_id' => 'ORD-' . fake()->randomNumber(6),
                'status' => $status,
                'amount' => fake()->randomFloat(2, 10, 150),
                'table_number' => fake()->numberBetween(1, 30),
            ],
            'status' => fake()->randomElement(['sent', 'delivered', 'delivered', 'delivered']), // Most delivered
            'sent_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'delivered_at' => fake()->optional(0.8)->dateTimeBetween('-7 days', 'now'),
            'read_at' => fake()->optional(0.6)->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    private function createLoyaltyNotification(Restaurant $restaurant, DeviceToken $deviceToken): void
    {
        $loyaltyTypes = ['points_earned', 'points_redeemed', 'tier_upgraded', 'special_offer'];
        $type = fake()->randomElement($loyaltyTypes);

        Notification::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $deviceToken->user_id,
            'device_token_id' => $deviceToken->id,
            'title' => $this->getLoyaltyTitle($type),
            'body' => $this->getLoyaltyBody($type),
            'type' => 'loyalty',
            'data' => [
                'loyalty_type' => $type,
                'points' => fake()->numberBetween(10, 500),
                'member_tier' => fake()->randomElement(['Bronze', 'Silver', 'Gold', 'Platinum']),
            ],
            'status' => fake()->randomElement(['sent', 'delivered', 'delivered']),
            'sent_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'delivered_at' => fake()->optional(0.9)->dateTimeBetween('-30 days', 'now'),
            'read_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    private function createSystemNotification(Restaurant $restaurant, DeviceToken $deviceToken, User $user): void
    {
        $systemTypes = ['pos_session_started', 'pos_session_ended', 'low_inventory', 'system_maintenance'];
        $type = fake()->randomElement($systemTypes);

        Notification::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'device_token_id' => $deviceToken->id,
            'title' => $this->getSystemTitle($type),
            'body' => $this->getSystemBody($type),
            'type' => 'system',
            'data' => [
                'system_type' => $type,
                'terminal_id' => fake()->optional()->randomNumber(3),
                'priority' => fake()->randomElement(['low', 'medium', 'high']),
            ],
            'status' => 'delivered',
            'sent_at' => fake()->dateTimeBetween('-3 days', 'now'),
            'delivered_at' => fake()->dateTimeBetween('-3 days', 'now'),
            'read_at' => fake()->optional(0.8)->dateTimeBetween('-3 days', 'now'),
        ]);
    }

    private function createPromotionalNotification(Restaurant $restaurant, DeviceToken $deviceToken): void
    {
        Notification::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $deviceToken->user_id,
            'device_token_id' => $deviceToken->id,
            'title' => fake()->randomElement([
                'Special Offer!',
                'Weekend Special',
                'Happy Hour',
                'New Menu Item',
                'Limited Time Offer'
            ]),
            'body' => fake()->randomElement([
                'Get 20% off on all orders today!',
                'Try our new signature dish with 15% discount',
                'Happy hour drinks - Buy 1 Get 1 Free',
                'Free dessert with every main course',
                'Weekend brunch special - Book now!'
            ]),
            'type' => 'promotion',
            'data' => [
                'promotion_code' => fake()->bothify('PROMO##??'),
                'discount_percent' => fake()->numberBetween(10, 50),
                'valid_until' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            ],
            'status' => fake()->randomElement(['sent', 'delivered', 'delivered']),
            'sent_at' => fake()->dateTimeBetween('-14 days', 'now'),
            'delivered_at' => fake()->optional(0.9)->dateTimeBetween('-14 days', 'now'),
            'read_at' => fake()->optional(0.5)->dateTimeBetween('-14 days', 'now'),
        ]);
    }

    private function createPosNotification(Restaurant $restaurant, DeviceToken $deviceToken): void
    {
        Notification::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $deviceToken->user_id,
            'device_token_id' => $deviceToken->id,
            'title' => fake()->randomElement([
                'Payment Processed',
                'Terminal Alert',
                'Cash Register Update',
                'Shift Summary',
                'POS Sync Complete'
            ]),
            'body' => fake()->randomElement([
                'Payment of $45.50 processed successfully',
                'Terminal 2 requires attention',
                'Cash register balanced for today',
                'Your shift total: $1,250.75',
                'All terminals synchronized with server'
            ]),
            'type' => 'pos',
            'data' => [
                'terminal_id' => fake()->numberBetween(1, 5),
                'transaction_id' => 'TXN-' . fake()->randomNumber(8),
                'amount' => fake()->randomFloat(2, 5, 200),
            ],
            'status' => 'delivered',
            'sent_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'delivered_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'read_at' => fake()->optional(0.9)->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    private function createRecentNotification(Restaurant $restaurant, DeviceToken $deviceToken): void
    {
        Notification::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $deviceToken->user_id,
            'device_token_id' => $deviceToken->id,
            'title' => 'New Order Received',
            'body' => 'Order #' . fake()->randomNumber(6) . ' for table ' . fake()->numberBetween(1, 30),
            'type' => 'order',
            'data' => [
                'order_id' => 'ORD-' . fake()->randomNumber(6),
                'status' => 'new',
                'amount' => fake()->randomFloat(2, 15, 85),
                'table_number' => fake()->numberBetween(1, 30),
                'urgent' => fake()->boolean(20), // 20% urgent
            ],
            'status' => 'delivered',
            'sent_at' => fake()->dateTimeBetween('-2 hours', 'now'),
            'delivered_at' => fake()->dateTimeBetween('-2 hours', 'now'),
            'read_at' => null, // Unread recent notifications
        ]);
    }

    private function getOrderTitle(string $status): string
    {
        return match($status) {
            'confirmed' => 'Order Confirmed',
            'preparing' => 'Order Being Prepared',
            'ready' => 'Order Ready!',
            'delivered' => 'Order Delivered',
            'cancelled' => 'Order Cancelled',
            default => 'Order Update',
        };
    }

    private function getOrderBody(string $status): string
    {
        return match($status) {
            'confirmed' => 'Your order has been confirmed and is being processed.',
            'preparing' => 'Chef is preparing your delicious meal.',
            'ready' => 'Your order is ready for pickup/delivery.',
            'delivered' => 'Your order has been delivered. Enjoy your meal!',
            'cancelled' => 'Your order has been cancelled. Refund will be processed.',
            default => 'Your order status has been updated.',
        };
    }

    private function getLoyaltyTitle(string $type): string
    {
        return match($type) {
            'points_earned' => 'Points Earned!',
            'points_redeemed' => 'Points Redeemed',
            'tier_upgraded' => 'Tier Upgrade!',
            'special_offer' => 'Exclusive Offer',
            default => 'Loyalty Update',
        };
    }

    private function getLoyaltyBody(string $type): string
    {
        return match($type) {
            'points_earned' => 'You earned ' . fake()->numberBetween(10, 100) . ' loyalty points!',
            'points_redeemed' => 'You redeemed ' . fake()->numberBetween(50, 500) . ' points successfully.',
            'tier_upgraded' => 'Congratulations! You\'ve been upgraded to ' . fake()->randomElement(['Silver', 'Gold', 'Platinum']) . ' tier.',
            'special_offer' => 'Special offer just for you! Limited time only.',
            default => 'Your loyalty status has been updated.',
        };
    }

    private function getSystemTitle(string $type): string
    {
        return match($type) {
            'pos_session_started' => 'POS Session Started',
            'pos_session_ended' => 'POS Session Ended',
            'low_inventory' => 'Low Inventory Alert',
            'system_maintenance' => 'System Maintenance',
            default => 'System Notification',
        };
    }

    private function getSystemBody(string $type): string
    {
        return match($type) {
            'pos_session_started' => 'POS terminal session has been started.',
            'pos_session_ended' => 'POS terminal session has been closed.',
            'low_inventory' => 'Several items are running low on inventory.',
            'system_maintenance' => 'Scheduled system maintenance completed.',
            default => 'System status update.',
        };
    }
}
