<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Note;
use App\Models\User;
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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Yana Tester',
            'email' => 'yana@dev.com',
            'password' => Hash::make('password123'), // Password gampang buat ngetes login
        ]);
        
        User::factory(2)->create(); // Tambah 2 user acak lagi pakai Faker

        // 2. Bikin 3 Kategori
        Category::factory(3)->create();

        // 3. Bikin 30 Post/Notes
        Note::factory(30)->create();
    }
}
