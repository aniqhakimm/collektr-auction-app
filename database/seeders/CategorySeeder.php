<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Trading Cards',
            'Collectible Figures',
            'Vintage Electronics',
            'Watches & Jewellery',
            'Art & Prints',
            'Sports Memorabilia',
            'Books & Comics',
            'Coins & Stamps',
        ];

        foreach ($names as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
