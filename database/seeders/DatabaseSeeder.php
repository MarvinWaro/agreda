<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\OperatingHour;
use App\Models\Setting;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $court = $this->seedCourt();
        $this->seedSports($court);
        $this->seedOperatingHours($court);
        $this->seedUsers();
        $this->seedSettings();
    }

    private function seedCourt(): Court
    {
        return Court::firstOrCreate(
            ['name' => 'AGREDA Sports Court'],
            [
                'description' => 'A single multi-sport court for Basketball, Volleyball, Futsal and Pickleball. Lines and equipment are adjusted per booking.',
                'location' => 'Brgy. Example, Agreda',
                'is_active' => true,
            ],
        );
    }

    private function seedSports(Court $court): void
    {
        $sports = [
            ['name' => 'Basketball', 'slug' => 'basketball', 'icon' => 'basketball', 'rate_offpeak' => 500, 'rate_peak' => 800],
            ['name' => 'Volleyball', 'slug' => 'volleyball', 'icon' => 'volleyball', 'rate_offpeak' => 500, 'rate_peak' => 800],
            ['name' => 'Futsal', 'slug' => 'futsal', 'icon' => 'futsal', 'rate_offpeak' => 600, 'rate_peak' => 900],
            ['name' => 'Pickleball', 'slug' => 'pickleball', 'icon' => 'pickleball', 'rate_offpeak' => 400, 'rate_peak' => 600],
        ];

        $ids = [];

        foreach ($sports as $sport) {
            $ids[] = Sport::updateOrCreate(['slug' => $sport['slug']], $sport)->id;
        }

        $court->sports()->syncWithoutDetaching($ids);
    }

    private function seedOperatingHours(Court $court): void
    {
        // Open every day of the week, 8:00 AM – 10:00 PM.
        foreach (range(0, 6) as $dayOfWeek) {
            OperatingHour::updateOrCreate(
                ['court_id' => $court->id, 'day_of_week' => $dayOfWeek],
                ['open_time' => '08:00:00', 'close_time' => '22:00:00'],
            );
        }
    }

    private function seedUsers(): void
    {
        if (! User::query()->where('email', 'owner@agreda.test')->exists()) {
            User::factory()->owner()->create([
                'name' => 'AGREDA Owner',
                'email' => 'owner@agreda.test',
            ]);
        }

        if (! User::query()->where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'contact_phone', 'value' => '0917 000 0000', 'group' => 'contact'],
            ['key' => 'contact_email', 'value' => 'hello@agreda.test', 'group' => 'contact'],
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/agreda', 'group' => 'contact'],
            ['key' => 'map_embed_url', 'value' => '', 'group' => 'contact'],
            ['key' => 'opening_hours', 'value' => 'Open daily 8:00 AM – 10:00 PM', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
