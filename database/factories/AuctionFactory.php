<?php

namespace Database\Factories;

use App\Models\Auction;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuctionFactory extends Factory
{
    protected $model = Auction::class;

    public function definition(): array
    {
        return [
            'title'          => $this->faker->sentence(3),
            'description'    => $this->faker->paragraph(),
            'starting_price' => 10.00,
            'auction_end_at' => now()->addHour(),
            'status'         => 'active',
            'image_path'     => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function ended(): static
    {
        return $this->state([
            'status'         => 'ended',
            'auction_end_at' => now()->subHour(),
        ]);
    }

    public function expiredActive(): static
    {
        // Active status but end time has passed — ready for the ending command.
        return $this->state([
            'status'         => 'active',
            'auction_end_at' => now()->subMinutes(5),
        ]);
    }
}
