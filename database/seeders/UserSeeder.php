<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::query()->get()->keyBy('code');

        $users = [
            ['name' => 'Raka Pradipta', 'email' => 'admin@pachelogistic.id', 'role' => 'super_admin', 'branch_code' => 'JKT'],
            ['name' => 'Nadia Kusuma', 'email' => 'oms@pachelogistic.id', 'role' => 'admin_oms', 'branch_code' => 'JKT'],
            ['name' => 'Dimas Santoso', 'email' => 'kasir@pachelogistic.id', 'role' => 'finance_cashier', 'branch_code' => 'JKT'],
            ['name' => 'Bagus Wijaya', 'email' => 'wms@pachelogistic.id', 'role' => 'operator_wms', 'branch_code' => 'JKT'],
            ['name' => 'Arif Hidayat', 'email' => 'tms@pachelogistic.id', 'role' => 'dispatcher_tms', 'branch_code' => 'SBY'],
        ];

        foreach ($users as $user) {
            $branch = $branches->get($user['branch_code']);

            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'branch_id' => $branch?->id,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
