# Digital Wallet System

A high-performance digital wallet application built with Laravel 12 and Vue.js 2, featuring real-time transaction updates via Pusher.

## Features

- **Atomic Money Transfers**: Database transactions with row-level locking to prevent race conditions
- **High Concurrency Support**: Designed to handle hundreds of transfers per second
- **Scalable Balance Storage**: Balance stored in users table
- **Real-time Updates**: Pusher integration for instant transaction notifications
- **Comprehensive Validation**: Full request validation and error handling

## Requirements

- PHP 8.2+
- Composer 2.0+
- Node.js 18.20+
- MySQL
- Pusher account (for real-time features)

## Installation

### Step 1: Clone the Repository

```
git clone 
cd DigitalWallet
```

### Step 2: Install PHP Dependencies

```
composer install
```

**Note:** Ensure you have Composer 2.0+ installed. Check version with:
```
composer --version
```

### Step 3: Install Node.js Dependencies

```
npm install
```

### Step 4: Environment Configuration

Copy `.env.example` to `.env` and configure:

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with database and Pusher credentials:

```env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=digital_wallet
# DB_USERNAME=root
# DB_PASSWORD=

# Pusher Configuration (get from https://pusher.com)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

### Step 5: Run Database Migrations

```bash
php artisan migrate
```

### Step 6: Seed Database with Dummy Data

```bash
php artisan db:seed
```

This will create 5 test users with sample transactions:
- john@example.com / password (Balance: $5000.00)
- jane@example.com / password (Balance: $3000.00)
- bob@example.com / password (Balance: $7500.00)
- alice@example.com / password (Balance: $2000.00)
- charlie@example.com / password (Balance: $10000.00)

### Step 7: Start Development Servers

You need to run two commands in separate terminals:

**Terminal 1 - Laravel Server:**
```
php artisan serve
```
This will start the Laravel application on `http://localhost:8000`

**Terminal 2 - Vite Dev Server:**
```
npm run dev
```
This will start the Vite development server for hot module replacement.

### Step 8: Access the Application

Open your browser and navigate to:
```
http://localhost:8000
```

You can now log in with any of the seeded user accounts above.

## Production Build

For production, build the assets:

```
npm run build
```

Then serve the application:
```
php artisan serve
```

## API Endpoints

All endpoints require authentication via Laravel Sanctum.

## Database Structure

### Users Table
- `id` - Primary key
- `name` - User name
- `email` - User email
- `balance` - Current balance (decimal 15,2)
- `password` - Hashed password
- `timestamps`

### Transactions Table
- `id` - Primary key
- `sender_id` - Foreign key to users
- `receiver_id` - Foreign key to users
- `amount` - Transfer amount (decimal 15,2)
- `commission_fee` - Commission charged (decimal 15,2)
- `total_debited` - Total debited from sender (decimal 15,2)
- `timestamps`
- Indexes on `sender_id`, `receiver_id`, and `created_at` for performance

## Key Implementation Details

### High Concurrency Handling
- Uses `DB::transaction()` for atomic operations
- Implements `lockForUpdate()` on user rows to prevent race conditions
- All balance updates are atomic and rolled back on failure

### Scalability
- Balance stored in users table (not calculated from transactions)
- Indexed queries for fast transaction lookups
- Pagination for transaction history

### Real-time Updates
- Laravel Broadcasting with Pusher
- Private channels per user (`user.{userId}`)
- Vue.js listens for `transaction.completed` events
- Automatic UI updates without page refresh

### Note
- As per the Transaction API requirements, a receiver ID is mandatory, and I have implemented it accordingly. However, from a UI perspective, I have included the email for usability. Ideally, this should be either a dropdown selection or an email input, which will then be used to send data to the backend.
