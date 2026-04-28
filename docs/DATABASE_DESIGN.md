# Database Design — Collektr Auction App

---

## Entity-Relationship Overview

```
users
  ├── bids (one-to-many)
  ├── checkouts (one-to-many)
  └── auctions [winning_user_id] (one-to-many)

categories
  └── auctions (one-to-many)

auctions
  ├── bids (one-to-many)
  ├── auction_images (one-to-many)
  ├── checkouts (one-to-one)
  ├── winning_bid [winning_bid_id] → bids
  └── winning_user [winning_user_id] → users

bids
  ├── auction (belongs-to)
  └── user (belongs-to)

checkouts
  ├── auction (belongs-to)
  ├── user (belongs-to)
  └── bid (belongs-to)

auction_images
  └── auction (belongs-to)
```

---

## Tables

### `users`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK, auto-increment | |
| name | varchar(255) | NOT NULL | |
| email | varchar(255) | NOT NULL, UNIQUE | |
| email_verified_at | timestamp | nullable | |
| password | varchar(255) | NOT NULL | Bcrypt hashed |
| is_admin | boolean | NOT NULL, DEFAULT false | Role flag — admins can manage auctions but cannot bid |
| remember_token | varchar(100) | nullable | |
| created_at / updated_at | timestamp | | |

---

### `categories`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK, auto-increment | |
| name | varchar(255) | NOT NULL | Display name (e.g. "Trading Cards") |
| slug | varchar(255) | NOT NULL, UNIQUE | URL-safe identifier (e.g. "trading-cards") |
| created_at / updated_at | timestamp | | |

---

### `auctions`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK, auto-increment | |
| title | varchar(255) | NOT NULL | |
| description | text | NOT NULL | |
| starting_price | decimal(10,2) | NOT NULL | Floor for the first bid |
| auction_end_at | timestamp | NOT NULL, INDEX | When the auction closes |
| status | enum('draft','active','ended') | NOT NULL, DEFAULT 'draft', INDEX | |
| image_path | varchar(255) | nullable | Path relative to `storage/app/public/` |
| category_id | bigint unsigned | nullable, FK → categories | Nullable to support uncategorised items |
| winning_bid_id | bigint unsigned | nullable, FK → bids | Set when auction ends |
| winning_user_id | bigint unsigned | nullable, FK → users | Set when auction ends |
| created_at / updated_at | timestamp | | |

**Design notes:**
- `status` is an ENUM to prevent invalid values at the DB level.
- `auction_end_at` is indexed because every page load queries for expired auctions.
- `winning_bid_id` and `winning_user_id` are denormalised onto the auction for fast winner lookups without a JOIN.
- `image_path` stores a single cover image; additional images use `auction_images`.

---

### `bids`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK, auto-increment | |
| auction_id | bigint unsigned | NOT NULL, FK → auctions (CASCADE DELETE), INDEX | |
| user_id | bigint unsigned | NOT NULL, FK → users (CASCADE DELETE), INDEX | |
| amount | decimal(10,2) | NOT NULL | Must strictly exceed current highest bid |
| created_at / updated_at | timestamp | | |

**Design notes:**
- No UNIQUE constraint on `(auction_id, user_id)` — a user can outbid their own previous bid.
- Highest bid is derived at query time via `MAX(amount)` or `ofMany` relationship; not stored as a column to avoid update anomalies.

---

### `checkouts`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK, auto-increment | |
| auction_id | bigint unsigned | NOT NULL, UNIQUE, FK → auctions | UNIQUE enforces one checkout per auction |
| user_id | bigint unsigned | NOT NULL, FK → users | The winning buyer |
| bid_id | bigint unsigned | NOT NULL, FK → bids | The winning bid |
| winning_bid_amount | decimal(10,2) | NOT NULL | **Snapshot** — frozen at checkout creation time |
| buyer_premium | decimal(10,2) | NOT NULL | **Snapshot** — 5% of bid, min RM 2.00 |
| shipping_fee | decimal(10,2) | NOT NULL | **Snapshot** — fixed RM 10.00 |
| grand_total | decimal(10,2) | NOT NULL | **Snapshot** — `winning_bid_amount + buyer_premium + shipping_fee` |
| created_at / updated_at | timestamp | | |

**Design notes:**
- The four fee columns are stored as a **snapshot** — they capture the values at the moment of checkout creation. If business rules change (e.g. the buyer premium rate is updated), existing checkouts are unaffected.
- `auction_id` has a UNIQUE index — this is the primary mechanism preventing duplicate checkouts, reinforced by `firstOrCreate()` in `CheckoutService`.
- `winning_bid_id` is stored even though `winning_bid_amount` is snapshotted, to maintain audit trail linkage.

---

### `auction_images`

| Column | Type | Constraints | Notes |
|---|---|---|---|
| id | bigint unsigned | PK, auto-increment | |
| auction_id | bigint unsigned | NOT NULL, FK → auctions (CASCADE DELETE), INDEX | |
| path | varchar(255) | NOT NULL | Relative to `storage/app/public/` |
| sort_order | integer | NOT NULL, DEFAULT 0 | Ascending display order |
| created_at / updated_at | timestamp | | |

---

### `sessions` / `password_reset_tokens` / `cache` / `jobs`

Standard Laravel infrastructure tables. Not part of the domain model.

---

## Migration Order

```
0001_01_01_000000  create_users_table
0001_01_01_000001  create_cache_table
0001_01_01_000002  create_jobs_table
2024_01_01_100000  create_auctions_table
2024_01_01_100001  create_bids_table
2024_01_01_100002  create_checkouts_table
2024_01_01_100003  add_winning_columns_to_auctions
2024_01_01_100004  add_is_admin_to_users
2024_01_01_100005  create_auction_images_table
2024_01_01_100006  create_categories_table (+ category_id FK on auctions)
```

---

## Key Constraints Summary

| Constraint | Purpose |
|---|---|
| `checkouts.auction_id` UNIQUE | One checkout per auction maximum |
| `auctions.status` ENUM | Prevents invalid status values at DB level |
| `categories.slug` UNIQUE | Prevents duplicate category slugs |
| `users.email` UNIQUE | Prevents duplicate accounts |
| `bids.auction_id` → `auctions` CASCADE | Bids deleted when auction deleted |
| `auction_images.auction_id` → `auctions` CASCADE | Images deleted when auction deleted |
| Pessimistic lock on writes | `BidService`, `CheckoutService`, `AuctionEndingService` all use `lockForUpdate()` to serialise concurrent writes |
