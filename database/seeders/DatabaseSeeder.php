<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'balance' => 5000.00,
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'balance' => 3000.00,
        ]);

        $user3 = User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'balance' => 7500.00,
        ]);

        $user4 = User::create([
            'name' => 'Alice Williams',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'balance' => 2000.00,
        ]);

        $user5 = User::create([
            'name' => 'Charlie Brown',
            'email' => 'charlie@example.com',
            'password' => Hash::make('password'),
            'balance' => 10000.00,
        ]);

        $transactions = [
            [
                'sender_id' => $user1->id,
                'receiver_id' => $user2->id,
                'amount' => 500.00,
                'commission_fee' => 7.50,
                'total_debited' => 507.50,
                'created_at' => now()->subDays(5),
            ],
            [
                'sender_id' => $user2->id,
                'receiver_id' => $user3->id,
                'amount' => 200.00,
                'commission_fee' => 3.00,
                'total_debited' => 203.00,
                'created_at' => now()->subDays(4),
            ],
            [
                'sender_id' => $user3->id,
                'receiver_id' => $user1->id,
                'amount' => 1000.00,
                'commission_fee' => 15.00,
                'total_debited' => 1015.00,
                'created_at' => now()->subDays(3),
            ],
            [
                'sender_id' => $user1->id,
                'receiver_id' => $user4->id,
                'amount' => 250.00,
                'commission_fee' => 3.75,
                'total_debited' => 253.75,
                'created_at' => now()->subDays(2),
            ],
            [
                'sender_id' => $user5->id,
                'receiver_id' => $user2->id,
                'amount' => 1500.00,
                'commission_fee' => 22.50,
                'total_debited' => 1522.50,
                'created_at' => now()->subDays(1),
            ],
            [
                'sender_id' => $user4->id,
                'receiver_id' => $user3->id,
                'amount' => 100.00,
                'commission_fee' => 1.50,
                'total_debited' => 101.50,
                'created_at' => now()->subHours(12),
            ],
            [
                'sender_id' => $user2->id,
                'receiver_id' => $user5->id,
                'amount' => 300.00,
                'commission_fee' => 4.50,
                'total_debited' => 304.50,
                'created_at' => now()->subHours(6),
            ],
        ];

        foreach ($transactions as $transactionData) {
            Transaction::create($transactionData);
        }

        $user1->balance = 5238.75;
        $user1->save();

        $user2->balance = 4492.50;
        $user2->save();

        $user3->balance = 6785.00;
        $user3->save();

        $user4->balance = 2148.50;
        $user4->save();

        $user5->balance = 8777.50;
        $user5->save();

        $this->command->info('Created 5 test users with transactions:');
        $this->command->info('1. john@example.com / password (Balance: $5238.75)');
        $this->command->info('2. jane@example.com / password (Balance: $4492.50)');
        $this->command->info('3. bob@example.com / password (Balance: $6785.00)');
        $this->command->info('4. alice@example.com / password (Balance: $2148.50)');
        $this->command->info('5. charlie@example.com / password (Balance: $8777.50)');
        $this->command->info('');
    }
}
