<?php

namespace Database\Seeders;

use App\Models\CarouselSlide;
use App\Models\Court;
use App\Models\Event;
use App\Models\Faq;
use App\Models\OperatingHour;
use App\Models\Page;
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
        $this->seedContent();
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
            ['key' => 'address', 'value' => 'AGREDA Phase III, Brgy. Example', 'group' => 'contact'],
            ['key' => 'opening_hours', 'value' => 'Open daily 8:00 AM – 10:00 PM', 'group' => 'general'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    private function seedContent(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'title' => 'About AGREDA PHASE III',
                'body' => "AGREDA PHASE III is a community multi-sport court built for the neighbourhood. One full court adapts to four sports — Basketball, Volleyball, Futsal and Pickleball — with lines and equipment adjusted for every booking.\n\nWe keep things simple: reserve a slot online, the owner confirms availability, and you pay in person at the court.",
            ],
            [
                'slug' => 'facilities',
                'title' => 'One court, four sports',
                'body' => 'Lines and equipment are adjusted per booking, so the court is ready for whichever sport you reserve.',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }

        $faqs = [
            ['question' => 'How do I confirm my booking?', 'answer' => 'Submit a request with your preferred sport, date and time. The owner reviews it and confirms availability, then you pay in person at the court.', 'category' => 'Booking', 'sort_order' => 1],
            ['question' => 'Can I change sports on the same booking?', 'answer' => 'Each booking is for one sport and time slot. To switch sports, submit a separate request — the court is the same, only the lines and equipment change.', 'category' => 'Booking', 'sort_order' => 2],
            ['question' => "What's the cancellation policy?", 'answer' => 'Let us know as early as possible by phone or Facebook so we can free the slot for others.', 'category' => 'Booking', 'sort_order' => 3],
            ['question' => 'How do I pay?', 'answer' => 'Payment is made in person at the court. There is no online payment — the price shown is for reference.', 'category' => 'Payment', 'sort_order' => 4],
            ['question' => 'House rules & equipment', 'answer' => 'Wear non-marking court shoes, clean up after your game, and handle the provided equipment with care.', 'category' => 'Rules', 'sort_order' => 5],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq,
            );
        }

        $slides = [
            ['title' => 'Grand opening', 'caption' => 'Book the court at AGREDA PHASE III', 'sort_order' => 1, 'is_visible' => true],
            ['title' => 'Pickleball nights', 'caption' => 'Lines added on request', 'sort_order' => 2, 'is_visible' => true],
            ['title' => 'Community court', 'caption' => 'Four sports, one venue', 'sort_order' => 3, 'is_visible' => true],
        ];

        foreach ($slides as $slide) {
            CarouselSlide::updateOrCreate(
                ['title' => $slide['title']],
                [...$slide, 'image_path' => null, 'link_url' => null],
            );
        }

        $events = [
            ['title' => 'Weekend 3x3 Basketball', 'description' => 'Friendly neighbourhood tournament.', 'event_date' => '2026-07-11', 'is_featured' => true],
            ['title' => 'Volleyball Open Play', 'description' => 'Bring a team or join one.', 'event_date' => '2026-07-18', 'is_featured' => true],
            ['title' => 'Pickleball Social', 'description' => 'Beginners welcome — paddles available.', 'event_date' => '2026-07-25', 'is_featured' => true],
        ];

        foreach ($events as $event) {
            Event::updateOrCreate(
                ['title' => $event['title']],
                [...$event, 'image_path' => null],
            );
        }
    }
}
