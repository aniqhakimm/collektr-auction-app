<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Services\AuctionEndingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EndExpiredAuctions extends Command
{
    protected $signature   = 'auctions:end-expired';
    protected $description = 'Mark expired active auctions as ended and record the winner.';

    public function handle(AuctionEndingService $service): int
    {
        $count = Auction::expired()->count();

        if ($count === 0) {
            $this->info('No expired auctions found.');
            return self::SUCCESS;
        }

        $service->endExpired();

        $this->info("Ended {$count} auction(s).");

        return self::SUCCESS;
    }
}
