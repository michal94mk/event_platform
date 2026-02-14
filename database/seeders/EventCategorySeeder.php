<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Konferencje', 'description' => 'Konferencje i branżowe spotkania'],
            ['name' => 'Warsztaty', 'description' => 'Warsztaty i szkolenia'],
            ['name' => 'Koncerty', 'description' => 'Koncerty i wydarzenia muzyczne'],
            ['name' => 'Sport', 'description' => 'Wydarzenia sportowe i fitness'],
            ['name' => 'Networking', 'description' => 'Spotkania networkingowe'],
            ['name' => 'Kultura', 'description' => 'Wydarzenia kulturalne i artystyczne'],
            ['name' => 'Technologia', 'description' => 'Meetupy i hackathony tech'],
            ['name' => 'Biznes', 'description' => 'Targi, premiery, eventy biznesowe'],
        ];

        foreach ($categories as $cat) {
            EventCategory::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                ]
            );
        }
    }
}
