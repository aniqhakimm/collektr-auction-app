<?php

namespace App\Models;

use Database\Factories\AuctionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Auction extends Model
{
    /** @use HasFactory<AuctionFactory> */
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'starting_price',
        'auction_end_at',
        'status',
        'category_id',
        'image_path',
        'winning_bid_id',
        'winning_user_id',
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'auction_end_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function winningBid(): BelongsTo
    {
        return $this->belongsTo(Bid::class, 'winning_bid_id');
    }

    public function winningUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winning_user_id');
    }

    public function checkout(): HasOne
    {
        return $this->hasOne(Checkout::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(AuctionImage::class)->orderBy('sort_order');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeEnded(Builder $query): void
    {
        $query->where('status', 'ended');
    }

    // Auctions whose time has passed but the ending job hasn't run yet.
    public function scopeExpired(Builder $query): void
    {
        $query->where('status', 'active')
              ->where('auction_end_at', '<=', now());
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    // Returns the single highest bid as a relationship so it can be eager-loaded.
    // Usage: Auction::with('highestBid')->get()
    public function highestBid(): HasOne
    {
        return $this->hasOne(Bid::class)->ofMany('amount', 'max');
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }
}
