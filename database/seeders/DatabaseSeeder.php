<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MarksEntryXi;
use Database\Seeders\MarksEntryXiSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            //InstituteSeeder::class,
            //CourseSeeder::class,
            TheorySubjectSeeder::class,
        ]);
    }
}
