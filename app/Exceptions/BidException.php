<?php

namespace App\Exceptions;

use RuntimeException;

class BidException extends RuntimeException
{
    public static function auctionNotActive(): self
    {
        return new self('This auction is not accepting bids.');
    }

    public static function auctionExpired(): self
    {
        return new self('This auction has already ended.');
    }

    public static function amountTooLow(string $minimum): self
    {
        return new self("Bid must be at least RM {$minimum}.");
    }
}
