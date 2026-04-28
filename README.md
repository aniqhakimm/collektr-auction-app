# 🏷️ Collektr - Simple Auction App

A simple auction app where users can:

* browse collectibles
* place bids
* and (if they win) see a checkout summary with fees included

---

# 🚀 What This App Does

1. Admin creates auction items
2. Users place bids
3. Auction ends automatically
4. Winner sees total price (with fees)

---

# 🧱 Tech Used (Simple Explanation)

* **Laravel** → backend system (handles logic)
* **Livewire** → makes bidding update automatically
* **Tailwind CSS** → styling
* **MySQL** → database

---

# ⚙️ Setup (Step-by-Step)

## ✅ Before you start

Make sure you have:

* PHP 8.2 or newer installed 
* Composer installed
* MySQL 8.0+ installed 
* Node.js 18+ installed 

---

## 1. Download the project

```bash
git clone <repository-url>
cd collektr-auction-app
```

---

## 2. Install required packages

```bash
composer install
npm install
```

---

## 3. Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

Then open `.env` file and update your database info:

```env
DB_DATABASE=collektr_auction
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

---

## 4. Setup database

```bash
php artisan migrate
php artisan db:seed
```

This will create:

* 1 admin account
* 5 test users
* sample auctions

---

## 5. Setup storage

```bash
php artisan storage:link
```

---

## 6. Build frontend

```bash
npm run build
```

(For development use:)

```bash
npm run dev
```

---

## 7. Start the app

```bash
php artisan serve
```

Open in browser:
👉 http://localhost:8000 or 
👉 http://collektr-auction-app.test/

---

# 🔐 Login Accounts

### Admin

* Email: `admin@collektr.test`
* Password: `password`

### Users

* Emails like:

  * `faris@collektr.test`
  * `aisyah@collektr.test`
* Password: `password`

---

# 🛒 How Auction Works

1. Go to `/auctions` page
2. Pick an item
3. Place a bid
4. Wait until auction ends
5. If you win → click **Checkout**

---

# 💰 Fee Calculation

Example:

```
Winning bid: RM 20.00
Buyer premium: RM 2.00 (minimum applies)
Shipping: RM 10.00

Total: RM 32.00
```

Rules:

* 5% fee OR minimum RM 2
* Shipping always RM 10

---

# ⏱️ Ending Auctions

Run this command to end expired auctions:

```bash
php artisan auctions:end-expired
```

Or run automatically:

```bash
php artisan schedule:work
```

---

# 🧪 Running Tests

Run all tests:

```bash
php artisan test
```

You should see all tests passing ✅

---

# 📄 Pages in the App

### Public

* `/auctions` → list auctions
* `/auctions/{id}` → view auction + bid

### Logged-in users

* `/checkout/{auction}` → see total if you win
* `/profile` → update profile and see bidding history

### Admin only

* `/admin/auctions` → manage auctions

---

# ⚠️ Important Notes

* You must login to bid
* Only winner can see checkout
* Bids must be higher than current bid
* Auction ends automatically

---

# ⭐ Extra Features (Bonus)

* Bid history
* Countdown timer
* Prevent duplicate checkout
* Snapshot checkout (price won’t change later)

---

# 📌 Summary

This project focuses on:

* correct auction rules
* safe bidding (no conflicts)
* clear checkout calculation
* simple and usable interface

---

# ✅ Ready to Use

If:

* app runs
* you can bid
* checkout works
* tests pass

👉 Then everything is working correctly!

---
