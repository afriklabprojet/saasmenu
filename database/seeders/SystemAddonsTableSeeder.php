<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemAddonsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addons = [
            [
                'name' => 'Blogs',
                'unique_identifier' => 'blog',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'blog.jpg',
                'type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Coupons',
                'unique_identifier' => 'coupon',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'coupons.jpg',
                'type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Language Translation',
                'unique_identifier' => 'language',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'language.jpg',
                'type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Subscription',
                'unique_identifier' => 'subscription',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'subscription.jpg',
                'type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cookie Consent',
                'unique_identifier' => 'cookie_consent',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'cookie_consent.jpg',
                'type' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sound Notification',
                'unique_identifier' => 'notification',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'notification.jpg',
                'type' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Personalised Slug',
                'unique_identifier' => 'unique_slug',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'unique_slug.jpg',
                'type' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Whatsapp Message',
                'unique_identifier' => 'whatsapp_message',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'whatsapp_message.jpg',
                'type' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Customer Login',
                'unique_identifier' => 'customer_login',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'customer_login.jpg',
                'type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Product Import',
                'unique_identifier' => 'product_import',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'product_import.jpg',
                'type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Top Deals',
                'unique_identifier' => 'top_deals',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'top_deals.jpg',
                'type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Firebase Notification',
                'unique_identifier' => 'firebase_notification',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'firebase_notification.jpg',
                'type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Table QR',
                'unique_identifier' => 'tableqr',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'tableqr.jpg',
                'type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'POS System',
                'unique_identifier' => 'pos',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'pos.jpg',
                'type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Google reCAPTCHA',
                'unique_identifier' => 'google_recaptcha',
                'version' => '3.7',
                'activated' => 1,
                'image' => 'google_recaptcha.jpg',
                'type' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('systemaddons')->insert($addons);
    }
}
