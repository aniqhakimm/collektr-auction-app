<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkout extends Model
{
    protected $fillable = [
        'auction_id',
        'user_id',
        'bid_id',
        'winning_bid_amount',
        'buyer_premium',
        'shipping_fee',
        'grand_total',
    ];

    protected $casts = [
        'winning_bid_amount' => 'decimal:2',
        'buyer_premium'      => 'decimal:2',
        'shipping_fee'       => 'decimal:2',
        'grand_total'        => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class);
    }
}
