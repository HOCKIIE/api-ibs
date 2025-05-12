<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Blog;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // สร้าง 10 category
        $categories = Category::factory()->count(10)->create();

        // สร้าง 20 blog และ attach category แบบสุ่ม 1-3 อัน
        Blog::factory()
            ->count(20)
            ->create()
            ->each(function ($blog) use ($categories) {
                $blog->categories()->attach(
                    $categories->random(rand(1, 3))->pluck('id')->toArray()
                );
            });
    }
}
