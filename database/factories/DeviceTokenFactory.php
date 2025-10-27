<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceTokenFactory extends Factory
{
    protected $model = \App\Models\DeviceToken::class;

    public function definition(): array
    {
        $platform = $this->faker->randomElement(['android', 'ios', 'web']);
        $isActive = $this->faker->boolean(85); // 85% chance of being active

        return [
            'user_id' => User::factory(),
            'restaurant_id' => Restaurant::factory(),
            'token' => $this->generateFcmToken(),
            'platform' => $platform,
            'device_id' => $this->faker->uuid(),
            'device_name' => $this->generateDeviceName($platform),
            'app_version' => $this->generateAppVersion(),
            'is_active' => $isActive,
            'last_used_at' => $isActive ? $this->faker->dateTimeBetween('-30 days', 'now') : $this->faker->dateTimeBetween('-90 days', '-31 days'),
        ];
    }

    public function android(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'android',
            'device_name' => $this->generateDeviceName('android'),
        ]);
    }

    public function ios(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'ios',
            'device_name' => $this->generateDeviceName('ios'),
        ]);
    }

    public function web(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform' => 'web',
            'device_name' => $this->generateDeviceName('web'),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'last_used_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'last_used_at' => $this->faker->dateTimeBetween('-90 days', '-31 days'),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'last_used_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    private function generateFcmToken(): string
    {
        // Generate a realistic FCM token format
        $characters = 'AzByC0x1Dw2Ev3Fu4Gt5Hs6Jr7Kq8Lp9Mo_Nn-Mm';
        $token = '';

        for ($i = 0; $i < 152; $i++) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $token;
    }

    private function generateDeviceName(string $platform): string
    {
        switch ($platform) {
            case 'android':
                $brands = ['Samsung', 'Google', 'Huawei', 'Xiaomi', 'OnePlus', 'LG'];
                $models = ['Galaxy S21', 'Pixel 6', 'P40 Pro', 'Mi 11', 'Nord 2', 'G8'];
                return $this->faker->randomElement($brands) . ' ' . $this->faker->randomElement($models);

            case 'ios':
                $models = ['iPhone 13', 'iPhone 13 Pro', 'iPhone 12', 'iPhone 12 Pro', 'iPhone SE', 'iPad Pro', 'iPad Air'];
                return $this->faker->randomElement($models);

            case 'web':
                $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
                $os = ['Windows 10', 'macOS', 'Ubuntu', 'Windows 11'];
                return $this->faker->randomElement($browsers) . ' on ' . $this->faker->randomElement($os);

            default:
                return 'Unknown Device';
        }
    }

    private function generateAppVersion(): string
    {
        $major = $this->faker->numberBetween(1, 3);
        $minor = $this->faker->numberBetween(0, 9);
        $patch = $this->faker->numberBetween(0, 20);

        return "{$major}.{$minor}.{$patch}";
    }
}
