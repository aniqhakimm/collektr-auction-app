<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use \Illuminate\Database\Console\Seeds\WithoutModelEvents;

    public function run(): void
    {
        // ── Categories ────────────────────────────────────────────────────────
        $categoryData = [
            'Trading Cards'        => 'trading-cards',
            'Collectible Figures'  => 'collectible-figures',
            'Vintage Electronics'  => 'vintage-electronics',
            'Watches & Jewellery'  => 'watches-jewellery',
            'Art & Prints'         => 'art-prints',
            'Sports Memorabilia'   => 'sports-memorabilia',
            'Books & Comics'       => 'books-comics',
            'Coins & Stamps'       => 'coins-stamps',
        ];

        $categories = [];
        foreach ($categoryData as $name => $slug) {
            $categories[$slug] = Category::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $cards   = $categories['trading-cards'];
        $figures = $categories['collectible-figures'];

        // ── Users ─────────────────────────────────────────────────────────────
        $admin = User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@collektr.test',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $malayUsers = [
            ['name' => 'Ahmad Faris',    'email' => 'faris@collektr.test'],
            ['name' => 'Nurul Aisyah',   'email' => 'aisyah@collektr.test'],
            ['name' => 'Haziq Danial',   'email' => 'haziq@collektr.test'],
            ['name' => 'Siti Nabilah',   'email' => 'nabilah@collektr.test'],
            ['name' => 'Amirul Hakim',   'email' => 'amirul@collektr.test'],
        ];

        $bidders = collect($malayUsers)->map(fn ($u) => User::create([
            'name'     => $u['name'],
            'email'    => $u['email'],
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]));

        // ── Active auctions ───────────────────────────────────────────────────
        $activeAuctions = [
            // Pokemon TCG
            [
                'title'          => 'Pokémon TCG — Charizard ex SAR (Scarlet & Violet)',
                'description'    => "Mint condition Special Art Rare Charizard ex from the Scarlet & Violet base set. PSA-ready pull, unplayed and stored in a sleeve since opening. One of the most sought-after cards in the modern era.",
                'starting_price' => 180.00,
                'auction_end_at' => now()->addDays(3),
                'category_id'    => $cards->id,
            ],
            [
                'title'          => 'Pokémon TCG — Pikachu Illustrator Reprint Promo',
                'description'    => "Officially licensed reprint of the legendary Pikachu Illustrator promo distributed at the 2023 World Championships. Near mint, comes in a top loader.",
                'starting_price' => 250.00,
                'auction_end_at' => now()->addDays(5),
                'category_id'    => $cards->id,
            ],
            [
                'title'          => 'Pokémon TCG — Base Set Booster Pack (Unlimited, Sealed)',
                'description'    => "Factory sealed unlimited Base Set booster pack. Weighs correctly for a rare. This is a piece of Pokémon history — a must-have for any serious collector.",
                'starting_price' => 350.00,
                'auction_end_at' => now()->addDays(2),
                'category_id'    => $cards->id,
            ],
            [
                'title'          => 'Pokémon TCG — Moonbreon / Umbreon VMAX Alt Art',
                'description'    => "The fan-favourite Umbreon VMAX Alternative Art from Evolving Skies. Near mint, straight from a fresh pack. One of the most aesthetically stunning cards ever printed.",
                'starting_price' => 220.00,
                'auction_end_at' => now()->addDays(4),
                'category_id'    => $cards->id,
            ],
            // Hololive TCG
            [
                'title'          => 'hololive Official Trading Card Game — Holo*27 Starter Deck Set',
                'description'    => "Complete starter deck set featuring all four decks from the Holo*27 series. Includes Hoshimachi Suisei, Tokino Sora, Azki, and Roboco. Sealed and unplayed.",
                'starting_price' => 95.00,
                'auction_end_at' => now()->addDays(6),
                'category_id'    => $cards->id,
            ],
            [
                'title'          => 'hololive TCG — Gawr Gura Secret Rare Foil',
                'description'    => "The ultra-rare Gawr Gura Secret Rare from the 2nd Anniversary set. Stunning holographic treatment. Graded PSA 9 by a trusted third-party grader.",
                'starting_price' => 130.00,
                'auction_end_at' => now()->addDays(1),
                'category_id'    => $cards->id,
            ],
            // Hot Wheels
            [
                'title'          => 'Hot Wheels RLC — 2024 Snake Funny Car (Red Line Club Exclusive)',
                'description'    => "Exclusive Red Line Club die-cast in mint condition with original mailer box. Limited to RLC members only. Real Riders wheels, spectraflame paint. The crown jewel of any Hot Wheels collection.",
                'starting_price' => 75.00,
                'auction_end_at' => now()->addDays(4),
                'category_id'    => $figures->id,
            ],
            [
                'title'          => 'Hot Wheels Super Treasure Hunt — \'69 COPO Camaro (Factory Sealed Card)',
                'description'    => "Factory sealed Super Treasure Hunt \'69 COPO Camaro pulled from a fresh case. Spectraflame orange with Real Riders. Blister intact, no creases on card.",
                'starting_price' => 55.00,
                'auction_end_at' => now()->addDays(3),
                'category_id'    => $figures->id,
            ],
            // Collectible Figures
            [
                'title'          => 'Nendoroid — Hatsune Miku: Symphony 2023 Ver. (GSC)',
                'description'    => "Good Smile Company Nendoroid Hatsune Miku Symphony 2023 Ver. Brand new in box, unopened. Limited event exclusive figure with alternate face plates and special accessories.",
                'starting_price' => 120.00,
                'auction_end_at' => now()->addDays(7),
                'category_id'    => $figures->id,
            ],
            [
                'title'          => 'figma — Fate/Stay Night Saber Alter 2.0 (Max Factory)',
                'description'    => "Max Factory figma Saber Alter 2.0 with updated joints and all original accessories. Complete with Invisible Air effect parts and Excalibur Morgan. Near mint in box.",
                'starting_price' => 160.00,
                'auction_end_at' => now()->addDays(5),
                'category_id'    => $figures->id,
            ],
            [
                'title'          => 'One Piece — Monkey D. Luffy Gear 5 POP Mega WCF Figure',
                'description'    => "Bandai Spirits Gear 5 Luffy World Collectable Figure Mega size. First edition run. Mint in sealed box with original shrink wrap partially intact.",
                'starting_price' => 85.00,
                'auction_end_at' => now()->addDays(2),
                'category_id'    => $figures->id,
            ],
        ];

        foreach ($activeAuctions as $data) {
            $auction = Auction::create(array_merge($data, ['status' => 'active']));

            $amount = (float) $auction->starting_price;
            foreach ($bidders->random(rand(2, 4)) as $bidder) {
                $amount += rand(10, 60);
                Bid::create([
                    'auction_id' => $auction->id,
                    'user_id'    => $bidder->id,
                    'amount'     => $amount,
                ]);
            }
        }

        // ── Active — no bids ──────────────────────────────────────────────────
        Auction::create([
            'title'          => 'Pokémon TCG — Miraidon ex SAR (Scarlet & Violet, No Bids)',
            'description'    => "Mint Miraidon ex Special Art Rare from Scarlet & Violet base set. Fresh pull, stored in sleeve. Starting bid is a steal — grab it before someone else does.",
            'starting_price' => 90.00,
            'auction_end_at' => now()->addDay(),
            'status'         => 'active',
            'category_id'    => $cards->id,
        ]);

        // ── Ended — with winner ───────────────────────────────────────────────
        $ended = Auction::create([
            'title'          => 'Hot Wheels — Original 1968 Red Line Custom Camaro (Vintage)',
            'description'    => "Original 1968 Hot Wheels Red Line Custom Camaro in antifreeze. Light play wear, all four red lines intact. A genuine piece of die-cast history.",
            'starting_price' => 200.00,
            'auction_end_at' => now()->subMinute(),
            'status'         => 'active',
            'category_id'    => $figures->id,
        ]);
        $winBid = Bid::create([
            'auction_id' => $ended->id,
            'user_id'    => $bidders->first()->id,
            'amount'     => 480.00,
        ]);
        $ended->update([
            'status'          => 'ended',
            'winning_bid_id'  => $winBid->id,
            'winning_user_id' => $bidders->first()->id,
        ]);

        // ── Ended — no bids ───────────────────────────────────────────────────
        Auction::create([
            'title'          => 'hololive TCG — Korone & Okayu Friendship Pack (Ended, No Bids)',
            'description'    => "Special collaboration pack featuring Inugami Korone and Nekomata Okayu. Sealed. Unfortunately went unsold.",
            'starting_price' => 45.00,
            'auction_end_at' => now()->subHour(),
            'status'         => 'ended',
            'category_id'    => $cards->id,
        ]);

        $this->command->info('Seeded successfully.');
        $this->command->info('Admin:   admin@collektr.test / password');
        $this->command->info('Bidder:  ' . $bidders->first()->email . ' / password');
        $this->command->info('Winner of "1968 Red Line Custom Camaro": ' . $bidders->first()->email);
    }
}
