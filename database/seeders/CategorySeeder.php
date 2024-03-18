<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expenseCategories = [
            'Food', 'Transportation', 'Housing', 'Education', 'Shopping',
            'Kids', 'Entertainment', 'Health and beauty', 'Pet', 'Internet', 'Mobile',
        ];

        foreach ($expenseCategories as $expenseCategory) {
            DB::table('categories')->insert([
                'title' => $expenseCategory,
                'type' => 'expenses',
                'is_default' => true,
            ]);
        }


        $incomeCategories = [
            'Salary', 'Debt repayment', 'Gifts', 'Rental income', 'Premium/bonuses',
        ];

        foreach ($incomeCategories as $incomeCategory) {
            DB::table('categories')->insert([
                'title' => $incomeCategory,
                'type' => 'income',
                'is_default' => true,
            ]);
        }
    }
}
