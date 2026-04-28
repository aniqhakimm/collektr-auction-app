<?php

namespace App\Exceptions;

use RuntimeException;

class CheckoutException extends RuntimeException
{
    public static function notWinner(): self
    {
        return new self('Only the winning bidder can checkout.');
    }

    public static function auctionNotEnded(): self
    {
        return new self('Checkout is only available after the auction ends.');
    }

    public static function alreadyExists(): self
    {
        return new self('A checkout already exists for this auction.');
    }
}
